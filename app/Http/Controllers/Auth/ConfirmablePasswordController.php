<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class ConfirmablePasswordController extends Controller
{
    use ApiTrait;

    public function store(Request $request): JsonResponse
    {

        $request->validate([
            'password' => ['required'],
        ]);

        if (! Hash::check($request->password, $request->user()->password)) {
            return $this->errorResponse(
                ['password' => __('auth.password')],
                'Invalid password confirmation.',
                422
            );
        }

        return $this->successResponse('Password confirmed successfully.');
    }
}
