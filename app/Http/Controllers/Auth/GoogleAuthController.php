<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiTrait;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Laravel\Socialite\Facades\Socialite;

class GoogleAuthController extends Controller
{
    use ApiTrait;

    public function redirect()
    {
        return Socialite::driver('google')->stateless()->redirect();
    }

    public function callback()
    {
        $googleUser = Socialite::driver('google')->stateless()->user();

        $user = User::where('google_id', $googleUser->id)->first();
        if (! $user) {
            $user = User::create(
                [
                    'first_name' => explode(' ', $googleUser->name)[0] ?? 'User',
                    'last_name' => explode(' ', $googleUser->name)[1] ?? '',
                    'email' => $googleUser->email,
                    'google_id' => $googleUser->id,
                    'password' => Hash::make(Str::random(8)),
                    'registration_completed' => false,
                ]
            );
        }

        if ($user['registration_completed']) {
            Auth::login($user);
        }

        $user->token = 'Bearer '.$user->createToken('Single-Web-Page-Application')->plainTextToken;

        return $this->dataResponse(compact('user'));
    }

    public function completeRegistration(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'handle' => ['required', 'string', 'unique:'.User::class,  'max:255'],
            'role' => ['required', 'in:farmer,engineer'],
            'phone' => ['required', 'string', 'max:20'],
        ]);

        $user->update([
            'handle' => $validated['handle'],
            'role' => $validated['role'],
            'phone' => $validated['phone'],
            'registration_completed' => true,
            'email_verified_at' => now(),
        ]);

        return $this->dataResponse($user, 'Profile completed successfully');
    }
}
