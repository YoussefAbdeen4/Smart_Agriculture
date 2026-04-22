<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiTrait;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailVerificationPromptController extends Controller
{
    use ApiTrait;

    /**
     * Show the email verification prompt page.
     */
    public function __invoke(Request $request): JsonResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->successResponse('Email is already verified.');
        }

        return $this->errorResponse((object) [], 'Email verification required.', 403);
    }
}
