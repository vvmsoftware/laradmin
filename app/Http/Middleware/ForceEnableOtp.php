<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class ForceEnableOtp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();
        if ($user->hasEnabledTwoFactorAuthentication() === false) {
            return redirect()->route('two-factor.enable');
        }
        // Verify if the current session has been authenticated with 2fa
        $google2fa = app(Authenticator::class);
        if ($google2fa->isAuthenticated() === false) {
            return redirect()->route('two-factor.login');
        }

        return $next($request);
    }
}
