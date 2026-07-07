<?php

namespace App\Http\Controllers\Delegate;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

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
