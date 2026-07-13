<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class DelegateProfileController extends Controller
{
    public function show()
    {
        $delegate = Auth::guard('delegate')->user();
        $delegate->load(['platform', 'city']);

        $vehicleAssignment = $delegate->vehicleAssignments()
            ->where('is_active', true)
            ->with('vehicle')
            ->latest('assigned_at')
            ->first();

        return view('portal.profile.show', compact('delegate', 'vehicleAssignment'));
    }

    public function updateEmail(Request $request): \Illuminate\Http\RedirectResponse
    {
        $delegate = Auth::guard('delegate')->user();

        $request->validate([
            'email' => ['nullable', 'email', 'max:255', Rule::unique('delegates', 'email')->ignore($delegate->id)],
        ], [
            'email.email'   => __('portal.val_email_invalid'),
            'email.max'     => __('portal.val_email_max'),
            'email.unique'  => __('portal.val_email_taken'),
        ]);

        $delegate->update(['email' => $request->filled('email') ? $request->email : null]);

        return redirect()->route('portal.profile')
            ->with('email_success', __('portal.email_update_success'));
    }

    public function changePassword(Request $request)
    {
        $delegate = Auth::guard('delegate')->user();

        $request->validate([
            'current_password' => 'required|string',
            'new_password'     => 'required|string|min:8|confirmed',
        ], [
            'current_password.required' => __('portal.val_current_password_required'),
            'new_password.required'     => __('portal.val_new_password_required'),
            'new_password.min'          => __('portal.val_new_password_min'),
            'new_password.confirmed'    => __('portal.val_new_password_confirmed'),
        ]);

        if (!Hash::check($request->current_password, $delegate->portal_password)) {
            return back()->withErrors(['current_password' => __('portal.val_current_password_wrong')])
                ->with('tab', 'password');
        }

        $delegate->update([
            'portal_password' => Hash::make($request->new_password),
        ]);

        return redirect()->route('portal.profile')
            ->with('success', __('portal.flash_password_changed_short'));
    }
}
