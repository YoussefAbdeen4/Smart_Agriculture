<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Traits\ApiTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthenticatedSessionController extends Controller
{
    use ApiTrait;

    public function store(LoginRequest $request): JsonResponse
    {
        /* get user */
        $user = User::where('email', $request->email)->first();
        if(!$user){
            return $this->errorResponse(
                ['email' => 'The provided credentials are incorrect.'],
                'The provided credentials are incorrect.',
                401
            );
        }
        /* check password */
        if (Hash::check($request->password, $user->password)) {
            /* create token */
            $user->token = 'Bearer '.$user->createToken('Single-Web-Page-Application')->plainTextToken;
            /* check verification */
            if (is_null($user->email_verified_at)) {
                /* return user data with error massage */
                return $this->dataResponse(compact('user'), 'Email is not verified', 401);
            } else {
                /* return user data */
                return $this->dataResponse(compact('user'), '');
            }
        } else {
            /* return error massage */
            return $this->errorResponse(
                ['password' => 'The provided credentials are incorrect.'],
                'The provided credentials are incorrect.',
                401
            );
        }
    }

    public function destroy(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return $this->successResponse('Logut done successfuly');
    }
}
