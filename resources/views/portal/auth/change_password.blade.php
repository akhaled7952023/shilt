@extends('layouts.portal.app')

@section('title', __('portal.change_password_title'))

@section('content')
@php $delegate = auth('delegate')->user(); @endphp

<div style="max-width:420px; margin:0 auto;">

    {{-- Icon + heading --}}
    <div style="text-align:center; margin-bottom:24px;">
        <div style="width:64px;height:64px;border-radius:16px;background:var(--primary-light);color:var(--primary);
                    display:flex;align-items:center;justify-content:center;font-size:28px;margin:0 auto 14px;">
            <i class="la la-lock"></i>
        </div>
        @if($delegate->portal_first_login)
            <h5 style="font-weight:800;font-size:18px;margin-bottom:6px;">{{ __('portal.welcome_first_login') }}</h5>
            <p style="color:var(--muted);font-size:14px;margin:0;">
                {{ __('portal.welcome_first_login_sub') }}
            </p>
        @else
            <h5 style="font-weight:800;font-size:18px;margin-bottom:6px;">{{ __('portal.change_password_title') }}</h5>
            <p style="color:var(--muted);font-size:14px;margin:0;">
                {{ __('portal.change_password_sub') }}
            </p>
        @endif
    </div>

    <div class="p-card">
        <form method="POST" action="{{ route('portal.change-password.post') }}" style="padding:20px;">
            @csrf

            @if(!$delegate->portal_first_login)
                <div class="form-group mb-3">
                    <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:7px;">
                        {{ __('portal.current_password') }}
                    </label>
                    <input type="password"
                           name="current_password"
                           class="portal-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                           placeholder="••••••••">
                    @error('current_password')
                        <div style="color:var(--danger);font-size:12px;margin-top:5px;">{{ $message }}</div>
                    @enderror
                </div>
            @endif

            <div class="mb-3">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:7px;">
                    {{ __('portal.new_password') }}
                    <span style="color:var(--muted);font-weight:400;">{{ __('portal.new_password_hint') }}</span>
                </label>
                <input type="password"
                       name="new_password"
                       class="portal-input {{ $errors->has('new_password') ? 'is-invalid' : '' }}"
                       placeholder="••••••••">
                @error('new_password')
                    <div style="color:var(--danger);font-size:12px;margin-top:5px;">{{ $message }}</div>
                @enderror
            </div>

            <div class="mb-4">
                <label style="display:block;font-size:13px;font-weight:600;color:#374151;margin-bottom:7px;">
                    {{ __('portal.confirm_new_password') }}
                </label>
                <input type="password"
                       name="new_password_confirmation"
                       class="portal-input"
                       placeholder="••••••••">
            </div>

            <button type="submit" class="portal-btn portal-btn-primary">
                <i class="la la-check" style="font-size:17px;{{ app()->getLocale() === 'ar' ? 'margin-left:6px;' : 'margin-right:6px;' }}"></i>
                {{ __('portal.save_password') }}
            </button>
        </form>
    </div>

    @if(!$delegate->portal_first_login)
        <div style="text-align:center;margin-top:16px;">
            <a href="{{ route('portal.dashboard') }}"
               style="font-size:13px;color:var(--muted);text-decoration:none;">
                <i class="la la-arrow-{{ app()->getLocale() === 'ar' ? 'right' : 'left' }}" style="font-size:14px;"></i>
                {{ __('portal.back_to_dashboard') }}
            </a>
        </div>
    @endif
</div>
@endsection
