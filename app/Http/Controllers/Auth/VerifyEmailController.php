<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiTrait;
use Illuminate\Auth\Events\Verified;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rules;
use Illuminate\Validation\ValidationException;
class VerifyEmailController extends Controller
{
    use ApiTrait;

    /**
     * Mark the authenticated user's email address as verified.
     */
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->successResponse('Email already verified.');
        }
        $request->validate([
            'code' => ['required', 'digits:6', 'exists:users'],
        ]);
        /* token */
        $token = $request->header('Authorization');
        /* get user */
        $user = $request->user();
        /* check code & expired date */
        $now = date('Y-m-d H:i:s');;
        if ($user->code == $request->code and $user->code_expired_at > $now) {
            /* verifie user */
            $user->email_verified_at=$now;
            $user->save();
            $user->token = $token;
            /* return data */
            return $this->dataResponse(compact('user'), 'Email has been verified successfully.');
        } else {
            $user->token = $token;
            /* return data with error massage */
            return $this->dataResponse(compact('user'), 'code expired', 401);
        }
    }
}
