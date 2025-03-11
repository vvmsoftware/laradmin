<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\TwoFactorEnable;
use Illuminate\Http\Request;
use Inertia\Inertia;
use PragmaRX\Google2FA\Exceptions\IncompatibleWithGoogleAuthenticatorException;
use PragmaRX\Google2FA\Exceptions\InvalidCharactersException;
use PragmaRX\Google2FA\Exceptions\SecretKeyTooShortException;
use PragmaRX\Google2FALaravel\Google2FA;
use PragmaRX\Google2FALaravel\Support\Authenticator;

class TwoFactorController extends Controller
{
    public function enable(Request $request)
    {
        $user = $request->user();
        $two_factor_enabled = $user->hasEnabledTwoFactorAuthentication();

        if (!$two_factor_enabled) {
            $user->generateSecretKey();
        }

        return Inertia::render('settings/TwoFactor',[
            'status' => $two_factor_enabled,
            'twoFactorCode' => $user->twoFactorSecret(),
        ]);
    }

    public function notEnabled()
    {
        return Inertia::render('NoTwoFactor');
    }

    public function showQRCode(Request $request)
    {
        $user = $request->user();
        if($user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('dashboard');
        }
        $qrCode = $user->twoFactorQrCodeSvg();
        return response($qrCode)->header('Content-Type', 'image/svg+xml');
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function verify(TwoFactorEnable $request)
    {
        $user = $request->user();
        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey($user->twoFactorSecret(), $request->otp);
        if($valid) {
            if (!$user->hasEnabledTwoFactorAuthentication()) {
                $user->confirmTwoFactorAuthentication();
            }
            $google2fa->login();
            return redirect()->route('dashboard');
        }
        return redirect()->back()->withErrors(['otp' => 'The code you entered is invalid'])->withInput();
    }

    public function login(Request $request)
    {
        $user = $request->user();
        if(!$user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.enable')->with('error', 'Two factor authentication is not enabled.');
        }
        if (app(Authenticator::class)->isAuthenticated()) {
            return redirect()->route('dashboard');
        }
        return Inertia::render('auth/TwoFactorLogin');
    }

    /**
     * @throws IncompatibleWithGoogleAuthenticatorException
     * @throws SecretKeyTooShortException
     * @throws InvalidCharactersException
     */
    public function disable(TwoFactorEnable $request)
    {
        $user = $request->user();
        if(!$user->hasEnabledTwoFactorAuthentication()) {
            return redirect()->route('two-factor.enable')->with('error', 'Two factor authentication is not enabled.');
        }
        $google2fa = app(Google2FA::class);
        $valid = $google2fa->verifyKey($user->twoFactorSecret(), $request->otp);
        if($valid) {
            $user->disableTwoFactorAuthentication();
            return redirect()->route('two-factor.index')->with('success', 'Two factor authentication has been disabled.');
        }
        return redirect()->back()->withErrors(['otp' => 'The code you entered is invalid'])->withInput();
    }
}
