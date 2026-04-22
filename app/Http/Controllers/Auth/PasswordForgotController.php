<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiTrait;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;


class PasswordForgotController extends Controller
{
    use ApiTrait;

    public function store(Request $request): JsonResponse
    {
        $request->validate(['email' => ['required', 'email']]);
        $user = User::where('email', $request->email)->first();

        if(!$user){
            return $this->errorResponse(['email'=>'email not found'],'email not found');
        }
        return $this->dataResponse(compact('user'),'email found');
    }
}
