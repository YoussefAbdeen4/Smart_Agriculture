<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Traits\ApiTrait;
use Illuminate\Support\Facades\Mail;
use App\Mail\SendMail;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class EmailVerificationNotificationController extends Controller
{
    use ApiTrait;

    /**
     * Send a new email verification notification.
     */
    public function store(Request $request): JsonResponse|RedirectResponse
    {
        if ($request->user()->hasVerifiedEmail()) {
            return $this->successResponse('Email is already verified.');
        }
         $token = $request->header('Authorization');
        $user = $request->user();
        $code = rand(100000,999999);
        /* create expired date */
        $expired_date=date('Y-m-d H-i-s',strtotime('+2 minutes'));
        /* update code & expired date */
        $user->code=$code;
        $user->code_expired_at=$expired_date;
        $user->save();
        /* add token */
        $user->token=$token;
        /* send email */
        $data=[
            'name'=>$user->first_name.' '.$user->last_name,
            'code'=>$user->code,
        ];
        //dd($user->email);
        Mail::to($user->email)->send(new SendMail($data));
        /* return user data */
        return $this->dataResponse(compact('user'),'Verification code sent to your email.');
    }
}
