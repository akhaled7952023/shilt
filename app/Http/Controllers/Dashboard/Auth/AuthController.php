<?php

namespace App\Http\Controllers\Dashboard\Auth;

use Ichtrojan\Otp\Otp;
use App\Http\Requests\LoginRequest;
use App\Http\Controllers\Controller;
use App\Http\Requests\ResetPasswordRequest;
use App\Http\Requests\ForgetPasswordRequest;
use App\Services\Dashboard\Auth\IAuthService;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Routing\Controllers\HasMiddleware;

class AuthController extends Controller implements HasMiddleware
{
    protected $authService;
    protected $otp2;

    public function __construct(IAuthService $authService)
    {
        $this->authService = $authService;
        $this->otp2 = new Otp();
    }
    public static function middleware()
    {
        return [new Middleware(middleware: 'guest', except: ['logout'])];
    }
    public function showLoginForm()
    {
        return view('dashboard.auth.login');
    }

    public function login(LoginRequest $request)
    {
        $credentials = $request->only('email', 'password');
        $remember = $request->has('remember') ? true : false;

        if ($this->authService->login($credentials, 'web', $remember)) {
            return redirect()->intended(route('dashboard.welcome'));
        }

        return redirect()
            ->back()
            ->withErrors(['email' => __('auth.not_match')]);
    }

    public function logout()
    {
        $this->authService->logout('web');
        return redirect()->route('dashboard.login');
    }

    public function showEmailForm()
    {
        return view('dashboard.auth.emailform');
    }

    public function sendOtp(ForgetPasswordRequest $request)
    {
        $admin = $this->authService->sendOtp($request->email);
        if (!$admin) {
            return redirect()
                ->back()
                ->withErrors(['email' => __('auth.email_not_registered')]);
        }

        session(['reset_email' => $admin->email]);
        return redirect()->route('dashboard.password.verify');
    }

    public function showOtpForm()
    {
        $email = session('reset_email');

        if (!$email) {
            return redirect()
                ->route('dashboard.auth.emailform')
                ->withErrors(['error' => 'Session expired. Please try again.']);
        }

        return view('dashboard.auth.otpform');
    }

    public function verifyOtp(ForgetPasswordRequest $request)
    {

        $email = session('reset_email');

        if (!$email) {
            return redirect()
                ->route('dashboard.auth.showEmailForm')
                ->withErrors(['error' => 'Session expired. Please try again.']);
        }

        $data = [
            'email' => $email,
            'code' => $request->code,
        ];

        if (!$this->authService->verifyOtp($data)) {
            return redirect()
                ->back()
                ->withErrors(['error' => __('auth.code_invalid')]);
        }

        return redirect()->route('dashboard.password.reset');
    }

    public function showResetForm()
    {
        return view('dashboard.auth.resetpasswordform');
    }

    public function resetPassword(ResetPasswordRequest $request)
{
    $email = session('reset_email');

    if (!$email) {
        return redirect()->route('dashboard.auth.emailform')
            ->withErrors(['error' => 'Session expired. Please try again.']);
    }

    $admin = $this->authService->resetPassword($email, $request->password);
    if (!$admin) {
        return redirect()->back()->with(['error' => 'Try Again Later!']);
    }

    session()->forget('reset_email');

    return redirect()->route('dashboard.login')->with('success', 'Your Password Updated Successfully!');
}

}
