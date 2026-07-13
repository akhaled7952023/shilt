@extends('layouts.portal.app')

@section('title', __('portal.profile_title'))

@section('content')
@php
    $isHs      = ($delegate->platform?->code ?? '') === 'hungerstation';
    $platLabel = $isHs ? __('portal.platform_hungerstation') : __('portal.platform_chefz');
    $platColor = $isHs ? 'hs' : 'cz';

    $iban = $delegate->iban ?? '';
    $maskedIban = strlen($iban) > 8
        ? substr($iban, 0, 4) . str_repeat('•', strlen($iban) - 8) . substr($iban, -4)
        : $iban;
@endphp

{{-- ─── Profile hero ────────────────────────────────────────── --}}
<div class="p-card mb-4" style="overflow:visible;">
    <div style="background:linear-gradient(135deg,#1d4ed8,#2563eb,#7c3aed);
                padding:24px 20px 40px;border-radius:14px 14px 0 0;
                position:relative;">
        <div style="display:flex;align-items:center;gap:16px;position:relative;z-index:1;">
            <div style="width:64px;height:64px;border-radius:50%;
                        border:3px solid rgba(255,255,255,.4);
                        overflow:hidden;
                        background:rgba(255,255,255,.2);
                        display:flex;align-items:center;justify-content:center;
                        font-size:26px;font-weight:800;color:white;flex-shrink:0;">
                @if($delegate->profile_photo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($delegate->profile_photo) }}"
                         alt="" style="width:100%;height:100%;object-fit:cover;">
                @else
                    {{ mb_substr($delegate->name ?? __('portal.avatar_fallback'), 0, 1) }}
                @endif
            </div>
            <div>
                <div style="font-size:19px;font-weight:800;color:white;line-height:1.25;margin-bottom:6px;">
                    {{ $delegate->name }}
                </div>
                <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                    <span class="platform-badge {{ $platColor }}"
                          style="background:rgba(255,255,255,.22);color:white;">
                        {{ $platLabel }}
                    </span>
                    <span class="status-badge {{ $delegate->status->value === 'active' ? 'published' : 'open' }}"
                          style="font-size:11px;">
                        {{ $delegate->status->label() }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    {{-- Account info strip --}}
    <div style="padding:14px 20px;display:flex;flex-wrap:wrap;gap:16px;border-top:1px solid var(--border);">
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">
                {{ __('portal.delegate_code_field') }}
            </div>
            <div style="font-size:14px;font-weight:700;margin-top:2px;font-family:monospace;letter-spacing:.5px;">
                {{ $delegate->delegate_code }}
            </div>
        </div>
        @if($delegate->city)
            <div>
                <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">
                    {{ __('portal.region_field') }}
                </div>
                <div style="font-size:14px;font-weight:700;margin-top:2px;">{{ $delegate->city->name }}</div>
            </div>
        @endif
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">
                {{ __('portal.last_login_label') }}
            </div>
            <div style="font-size:14px;font-weight:700;margin-top:2px;">
                {{ $delegate->last_portal_login?->format('Y/m/d H:i') ?? '—' }}
            </div>
        </div>
    </div>
</div>

{{-- ─── Desktop 2-col layout ────────────────────────────────── --}}
<div class="row g-4">

    {{-- Left column: profile info --}}
    <div class="col-12 col-md-6">

        {{-- Personal info --}}
        <div class="section-header">{{ __('portal.personal_info') }}</div>
        <div class="p-card mb-4">
            <div class="info-row">
                <div class="info-icon"><i class="la la-user"></i></div>
                <div>
                    <div class="info-label">{{ __('portal.full_name') }}</div>
                    <div class="info-value">{{ $delegate->name }}</div>
                </div>
            </div>
            <div class="info-row">
                <div class="info-icon"><i class="la la-id-card"></i></div>
                <div>
                    <div class="info-label">{{ __('portal.delegate_code_label') }}</div>
                    <div class="info-value" style="font-family:monospace;letter-spacing:.5px;">
                        {{ $delegate->delegate_code }}
                    </div>
                </div>
            </div>
            @if($delegate->phone)
                <div class="info-row">
                    <div class="info-icon"><i class="la la-phone"></i></div>
                    <div>
                        <div class="info-label">{{ __('portal.phone_label') }}</div>
                        <div class="info-value">{{ $delegate->phone }}</div>
                    </div>
                </div>
            @endif
            @if($delegate->email)
                <div class="info-row">
                    <div class="info-icon"><i class="la la-envelope"></i></div>
                    <div>
                        <div class="info-label">{{ __('portal.email_label') }}</div>
                        <div class="info-value">{{ $delegate->email }}</div>
                    </div>
                </div>
            @endif
            @if($delegate->national_id)
                <div class="info-row">
                    <div class="info-icon" style="background:#fdf4ff;color:#9333ea;">
                        <i class="la la-id-badge"></i>
                    </div>
                    <div>
                        <div class="info-label">{{ __('portal.national_id_label') }}</div>
                        <div class="info-value" style="font-family:monospace;letter-spacing:.5px;">
                            {{ $delegate->national_id }}
                        </div>
                    </div>
                </div>
            @endif
            @if($delegate->hire_date)
                <div class="info-row">
                    <div class="info-icon" style="background:#fff7ed;color:#ea580c;">
                        <i class="la la-calendar-check-o"></i>
                    </div>
                    <div>
                        <div class="info-label">{{ __('portal.hire_date_label') }}</div>
                        <div class="info-value">{{ $delegate->hire_date->format('Y/m/d') }}</div>
                    </div>
                </div>
            @endif
        </div>

        {{-- Vehicle --}}
        @if($vehicleAssignment && $vehicleAssignment->vehicle)
            @php $v = $vehicleAssignment->vehicle; @endphp
            <div class="section-header">{{ __('portal.vehicle_section') }}</div>
            <div class="p-card mb-4">
                <div class="info-row">
                    <div class="info-icon" style="background:#eff6ff;color:#2563eb;">
                        <i class="la la-car"></i>
                    </div>
                    <div>
                        <div class="info-label">{{ __('portal.vehicle_type_label') }}</div>
                        <div class="info-value">
                            {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: __('portal.vehicle_fallback') }}
                            @if($v->year ?? null)
                                <span style="color:var(--muted);font-weight:400;">({{ $v->year }})</span>
                            @endif
                        </div>
                    </div>
                </div>
                @if($v->plate_number ?? null)
                    <div class="info-row">
                        <div class="info-icon" style="background:#f0fdf4;color:#16a34a;">
                            <i class="la la-tag"></i>
                        </div>
                        <div>
                            <div class="info-label">{{ __('portal.plate_number_label') }}</div>
                            <div class="info-value" style="font-family:monospace;letter-spacing:.8px;">
                                {{ $v->plate_number }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

        {{-- Bank info --}}
        @if($delegate->bank_name || $delegate->iban)
            <div class="section-header">{{ __('portal.bank_section') }}</div>
            <div class="p-card mb-4">
                @if($delegate->bank_name)
                    <div class="info-row">
                        <div class="info-icon" style="background:#fdf4ff;color:#9333ea;">
                            <i class="la la-university"></i>
                        </div>
                        <div>
                            <div class="info-label">{{ __('portal.bank_name_label') }}</div>
                            <div class="info-value">{{ $delegate->bank_name }}</div>
                        </div>
                    </div>
                @endif
                @if($delegate->iban)
                    <div class="info-row">
                        <div class="info-icon" style="background:#fff7ed;color:#ea580c;">
                            <i class="la la-credit-card"></i>
                        </div>
                        <div>
                            <div class="info-label">{{ __('portal.iban_label') }}</div>
                            <div class="info-value" style="font-family:monospace;font-size:13px;letter-spacing:.8px;">
                                {{ $maskedIban }}
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        @endif

    </div>

    {{-- Right column: password + logout --}}
    <div class="col-12 col-md-6">

        <div class="section-header">{{ __('portal.account_security') }}</div>
        <div class="p-card mb-4">
            <div style="padding:18px 18px 4px;">
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:16px;
                            padding-bottom:16px;border-bottom:1px solid var(--border);">
                    <div style="width:42px;height:42px;border-radius:11px;
                                background:#eff6ff;color:#2563eb;
                                display:flex;align-items:center;justify-content:center;
                                font-size:20px;flex-shrink:0;">
                        <i class="la la-lock"></i>
                    </div>
                    <div>
                        <div style="font-size:15px;font-weight:700;color:var(--text);">{{ __('portal.change_pw_section') }}</div>
                        <div style="font-size:12px;color:var(--muted);">{{ __('portal.change_pw_section_sub') }}</div>
                    </div>
                </div>

                <form method="POST" action="{{ route('portal.profile.password') }}">
                    @csrf

                    @if($errors->has('current_password') || $errors->has('new_password'))
                        <div class="portal-alert" style="background:#fee2e2;color:#dc2626;margin-bottom:14px;">
                            <i class="la la-times-circle" style="font-size:18px;"></i>
                            {{ $errors->first() }}
                        </div>
                    @endif

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:13px;font-weight:600;
                                      color:#374151;margin-bottom:7px;">
                            {{ __('portal.current_password') }}
                        </label>
                        <input type="password" name="current_password"
                               class="portal-input {{ $errors->has('current_password') ? 'is-invalid' : '' }}"
                               placeholder="••••••••">
                        @error('current_password')
                            <div style="color:var(--danger);font-size:12px;margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:13px;font-weight:600;
                                      color:#374151;margin-bottom:7px;">
                            {{ __('portal.new_password') }}
                            <span style="color:var(--muted);font-weight:400;">{{ __('portal.new_password_hint') }}</span>
                        </label>
                        <input type="password" name="new_password"
                               class="portal-input {{ $errors->has('new_password') ? 'is-invalid' : '' }}"
                               placeholder="••••••••">
                        @error('new_password')
                            <div style="color:var(--danger);font-size:12px;margin-top:5px;">{{ $message }}</div>
                        @enderror
                    </div>

                    <div style="margin-bottom:20px;">
                        <label style="display:block;font-size:13px;font-weight:600;
                                      color:#374151;margin-bottom:7px;">
                            {{ __('portal.confirm_new_password') }}
                        </label>
                        <input type="password" name="new_password_confirmation"
                               class="portal-input" placeholder="••••••••">
                    </div>

                    <button type="submit" class="portal-btn portal-btn-primary">
                        <i class="la la-lock" style="font-size:17px;{{ app()->getLocale() === 'ar' ? 'margin-left:6px;' : 'margin-right:6px;' }}"></i>
                        {{ __('portal.save_new_password') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Email address --}}
        <div class="section-header">{{ __('portal.email_update_heading') }}</div>
        <div class="p-card mb-4">
            <div style="padding:18px 18px 4px;">

                @if(session('email_success'))
                    <div class="portal-alert" style="background:#dcfce7;color:#16a34a;margin-bottom:14px;">
                        <i class="la la-check-circle" style="font-size:18px;"></i>
                        {{ session('email_success') }}
                    </div>
                @endif

                @if($errors->has('email'))
                    <div class="portal-alert" style="background:#fee2e2;color:#dc2626;margin-bottom:14px;">
                        <i class="la la-times-circle" style="font-size:18px;"></i>
                        {{ $errors->first('email') }}
                    </div>
                @endif

                <form method="POST" action="{{ route('portal.profile.email') }}">
                    @csrf
                    <div style="margin-bottom:14px;">
                        <label style="display:block;font-size:13px;font-weight:600;
                                      color:#374151;margin-bottom:7px;">
                            {{ __('portal.email_update_label') }}
                        </label>
                        <input type="email" name="email"
                               class="portal-input {{ $errors->has('email') ? 'is-invalid' : '' }}"
                               value="{{ old('email', $delegate->email) }}"
                               placeholder="{{ __('portal.email_update_ph') }}"
                               dir="ltr" style="text-align:left;">
                    </div>
                    @if(!$delegate->email)
                        <p style="font-size:12px;color:var(--muted);margin-bottom:14px;">
                            <i class="la la-info-circle" style="{{ app()->getLocale() === 'ar' ? 'margin-left:4px;' : 'margin-right:4px;' }}"></i>
                            {{ __('portal.email_no_address') }}
                        </p>
                    @endif
                    <button type="submit" class="portal-btn portal-btn-primary">
                        <i class="la la-save" style="font-size:17px;{{ app()->getLocale() === 'ar' ? 'margin-left:6px;' : 'margin-right:6px;' }}"></i>
                        {{ __('portal.email_update_save') }}
                    </button>
                </form>
            </div>
        </div>

        {{-- Logout --}}
        <div class="p-card" style="overflow:visible;">
            <div style="padding:16px 18px;">
                <div style="font-size:13px;color:var(--muted);margin-bottom:14px;">
                    <i class="la la-info-circle" style="{{ app()->getLocale() === 'ar' ? 'margin-left:5px;' : 'margin-right:5px;' }}"></i>
                    {{ __('portal.logout_info') }}
                </div>
                <form method="POST" action="{{ route('portal.logout') }}">
                    @csrf
                    <button type="submit" class="portal-btn portal-btn-outline"
                            style="color:#dc2626;border-color:#fecaca;">
                        <i class="la la-sign-out" style="font-size:17px;{{ app()->getLocale() === 'ar' ? 'margin-left:6px;' : 'margin-right:6px;' }}"></i>
                        {{ __('portal.nav_logout') }}
                    </button>
                </form>
            </div>
        </div>

    </div>
</div>

@endsection
