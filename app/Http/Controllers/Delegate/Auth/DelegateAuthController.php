<?php

namespace App\Http\Controllers\Delegate\Auth;

use App\Http\Controllers\Controller;
use App\Models\Delegate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;

class DelegateAuthController extends Controller
{
    public function showLoginForm()
    {
        return view('portal.auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'driver_id' => 'required|string',
            'password'  => 'required|string',
        ], [
            'driver_id.required' => __('portal.val_driver_id_required'),
            'password.required'  => __('portal.val_password_required'),
        ]);

        $throttleKey = 'portal-login:' . $request->ip();
        $maxAttempts = (int) (\App\Models\SystemSetting::get('portal_max_login_attempts') ?? 5);
        $lockMinutes = (int) (\App\Models\SystemSetting::get('portal_lockout_minutes') ?? 30);

        if (RateLimiter::tooManyAttempts($throttleKey, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            return back()->withErrors([
                'driver_id' => __('portal.val_too_many_attempts', ['seconds' => $seconds]),
            ])->withInput($request->only('driver_id'));
        }

        $delegate = Delegate::where('delegate_code', $request->driver_id)->first();

        if (!$delegate || !$delegate->portal_enabled) {
            RateLimiter::hit($throttleKey, $lockMinutes * 60);
            return back()->withErrors([
                'driver_id' => __('portal.val_no_portal_account'),
            ])->withInput($request->only('driver_id'));
        }

        if (!Hash::check($request->password, $delegate->portal_password)) {
            RateLimiter::hit($throttleKey, $lockMinutes * 60);
            return back()->withErrors([
                'password' => __('portal.val_wrong_password'),
            ])->withInput($request->only('driver_id'));
        }

        RateLimiter::clear($throttleKey);

        Auth::guard('delegate')->login($delegate, $request->boolean('remember'));
        $delegate->update(['last_portal_login' => now()]);

        if ($delegate->portal_first_login) {
            return redirect()->route('portal.change-password')
                ->with('info', __('portal.flash_first_login'));
        }

        return redirect()->route('portal.dashboard');
    }

    public function showChangePassword()
    {
        return view('portal.auth.change_password');
    }

    public function changePassword(Request $request)
    {
        $delegate = Auth::guard('delegate')->user();

        $rules = ['new_password' => 'required|string|min:8|confirmed'];

        // If not first login, require current password
        if (!$delegate->portal_first_login) {
            $rules['current_password'] = 'required|string';
        }

        $request->validate($rules, [
            'current_password.required' => __('portal.val_current_password_required'),
            'new_password.required'     => __('portal.val_new_password_required'),
            'new_password.min'          => __('portal.val_new_password_min'),
            'new_password.confirmed'    => __('portal.val_new_password_confirmed'),
        ]);

        if (!$delegate->portal_first_login && !Hash::check($request->current_password, $delegate->portal_password)) {
            return back()->withErrors(['current_password' => __('portal.val_current_password_wrong')]);
        }

        $delegate->update([
            'portal_password'    => Hash::make($request->new_password),
            'portal_first_login' => false,
        ]);

        return redirect()->route('portal.dashboard')
            ->with('success', __('portal.flash_password_changed'));
    }

    public function logout(Request $request)
    {
        Auth::guard('delegate')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('portal.login');
    }
}
