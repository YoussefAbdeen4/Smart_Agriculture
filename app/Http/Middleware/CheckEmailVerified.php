<?php

namespace App\Http\Middleware;

use App\Http\Traits\ApiTrait;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckEmailVerified
{
    use ApiTrait;
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $authUser = Auth::guard('sanctum')->user();
        if(is_null($authUser) or is_null($authUser->email_verified_at) ){
            return $this->errorResponse(['email'=>'user is not verified'],'Unauthroized',401);
        }

        return $next($request);
    }
}
