<?php

namespace App\Http\Middleware;

use App\Models\SiteSetting;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailIsVerifiedWhenRequired
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (
            ! $user
            || SiteSetting::getValue('require_email_verification', '0') !== '1'
            || $user->hasAnyRole(['Super Admin', 'Admin'])
            || ! filled($user->email)
            || $user->hasVerifiedEmail()
        ) {
            return $next($request);
        }

        return redirect()->route('verification.notice');
    }
}
