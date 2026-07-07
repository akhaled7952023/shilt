@php
    $locale = app()->getLocale();
    $isRtl  = $locale === 'ar';
    $companyNameEn = \App\Models\SystemSetting::get('company_name_en') ?? '';
    $companyName = ($isRtl === false && $companyNameEn)
        ? $companyNameEn
        : (\App\Models\SystemSetting::get('company_name_ar') ?? 'شيلت للخدمات اللوجستية');
    $logoPath    = \App\Models\SystemSetting::get('company_logo_path') ?? '';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('portal.login_submit') }} — {{ __('portal.portal_title') }}</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800;900&display=swap">
    <link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">
    <style>
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        html, body {
            min-height: 100%;
            font-family: 'Tajawal', sans-serif;
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            background: #0f172a;
            -webkit-text-size-adjust: 100%;
        }

        body {
            min-height: 100svh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px 16px;
            position: relative;
            overflow-x: hidden;
        }

        /* Decorative background shapes */
        body::before {
            content: '';
            position: fixed;
            top: -60px; right: -60px;
            width: 220px; height: 220px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(37,99,235,.35) 0%, transparent 70%);
            pointer-events: none;
        }

        body::after {
            content: '';
            position: fixed;
            bottom: -80px; left: -60px;
            width: 260px; height: 260px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(124,58,237,.25) 0%, transparent 70%);
            pointer-events: none;
        }

        /* Login card */
        .login-card {
            width: 100%;
            max-width: 380px;
            background: #ffffff;
            border-radius: 22px;
            box-shadow: 0 24px 60px rgba(0,0,0,.5), 0 4px 16px rgba(0,0,0,.3);
            overflow: hidden;
            position: relative;
            z-index: 1;
        }

        /* Card top gradient band */
        .card-top {
            background: linear-gradient(135deg, #1d4ed8 0%, #2563eb 50%, #7c3aed 100%);
            padding: 32px 24px 28px;
            text-align: center;
            position: relative;
        }

        .card-top::after {
            content: '';
            position: absolute;
            bottom: -1px;
            left: 0; right: 0;
            height: 24px;
            background: white;
            border-radius: 24px 24px 0 0;
        }

        /* Company logo / icon */
        .brand-circle {
            width: 72px; height: 72px;
            border-radius: 18px;
            background: rgba(255,255,255,.18);
            border: 2px solid rgba(255,255,255,.3);
            margin: 0 auto 14px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 32px;
            color: white;
            overflow: hidden;
        }

        .brand-circle img {
            width: 100%; height: 100%;
            object-fit: contain;
            padding: 6px;
        }

        .brand-title {
            color: white;
            font-size: 18px;
            font-weight: 800;
            margin-bottom: 4px;
            text-shadow: 0 1px 4px rgba(0,0,0,.2);
        }

        .brand-subtitle {
            color: rgba(255,255,255,.75);
            font-size: 13px;
            font-weight: 400;
        }

        /* Card body */
        .card-body {
            padding: 24px 24px 28px;
        }

        .form-group {
            margin-bottom: 16px;
        }

        .form-label {
            display: block;
            font-size: 13px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 7px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            font-size: 19px;
            color: #9ca3af;
            pointer-events: none;
            {{ $isRtl ? 'right: 13px;' : 'left: 13px;' }}
        }

        .form-input {
            width: 100%;
            {{ $isRtl
                ? 'padding: 13px 42px 13px 14px;'
                : 'padding: 13px 14px 13px 42px;' }}
            border: 1.5px solid #e5e7eb;
            border-radius: 11px;
            font-family: 'Tajawal', sans-serif;
            font-size: 15px;
            color: #111827;
            background: #f9fafb;
            outline: none;
            transition: border-color .2s, box-shadow .2s, background .2s;
            -webkit-appearance: none;
        }

        .form-input:focus {
            border-color: #2563eb;
            background: white;
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        .form-input.is-invalid {
            border-color: #ef4444;
            background: #fff5f5;
        }

        .invalid-feedback {
            display: block;
            color: #ef4444;
            font-size: 12px;
            margin-top: 5px;
            {{ $isRtl ? 'padding-right: 2px;' : 'padding-left: 2px;' }}
        }

        .password-toggle {
            position: absolute;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #9ca3af;
            font-size: 18px;
            padding: 2px;
            line-height: 1;
            {{ $isRtl ? 'left: 12px;' : 'right: 12px;' }}
        }

        /* Remember me */
        .remember-row {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .remember-row input[type="checkbox"] {
            width: 17px; height: 17px;
            border-radius: 4px;
            border: 1.5px solid #d1d5db;
            cursor: pointer;
            accent-color: #2563eb;
            flex-shrink: 0;
        }

        .remember-row label {
            font-size: 13px;
            color: #6b7280;
            cursor: pointer;
        }

        /* Submit button */
        .submit-btn {
            width: 100%;
            padding: 15px;
            border-radius: 12px;
            border: none;
            background: linear-gradient(135deg, #2563eb 0%, #1d4ed8 100%);
            color: white;
            font-family: 'Tajawal', sans-serif;
            font-size: 17px;
            font-weight: 700;
            cursor: pointer;
            transition: opacity .2s, transform .1s, box-shadow .2s;
            box-shadow: 0 4px 14px rgba(37,99,235,.4);
            letter-spacing: .3px;
        }

        .submit-btn:hover { opacity: .92; box-shadow: 0 6px 18px rgba(37,99,235,.45); }
        .submit-btn:active { transform: scale(.98); }

        /* Forgot password placeholder */
        .forgot-link {
            display: block;
            text-align: center;
            margin-top: 16px;
            font-size: 13px;
            color: #9ca3af;
        }

        /* Footer */
        .login-footer {
            margin-top: 28px;
            text-align: center;
            color: rgba(255,255,255,.35);
            font-size: 12px;
            position: relative;
            z-index: 1;
        }

        /* Language switch */
        .lang-bar {
            position: absolute;
            top: 12px;
            {{ $isRtl ? 'left: 12px;' : 'right: 12px;' }}
            display: inline-flex; align-items: stretch;
            border-radius: 9px; overflow: hidden;
            border: 1.5px solid rgba(255,255,255,.3);
            background: rgba(255,255,255,.1);
            z-index: 10;
        }

        .lang-bar a {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 500;
            padding: 5px 11px;
            text-decoration: none; line-height: 1; white-space: nowrap;
            font-family: 'Tajawal', sans-serif;
            transition: background .15s, color .15s;
        }

        .lang-bar a .lang-flag { font-size: 15px; line-height: 1; flex-shrink: 0; }

        .lang-bar a + a { border-inline-start: 1px solid rgba(255,255,255,.25); }

        .lang-bar a.active { background: rgba(255,255,255,.25); color: white; font-weight: 700; }
        .lang-bar a:not(.active) { color: rgba(255,255,255,.6); }
        .lang-bar a:not(.active):hover { background: rgba(255,255,255,.15); color: rgba(255,255,255,.9); }
    </style>
</head>
<body>

<div class="login-card">
    <div class="card-top">
        <div class="lang-bar">
            <a href="{{ route('portal.lang', 'ar') }}" class="{{ $isRtl ? 'active' : '' }}">
                <span class="lang-flag">🇸🇦</span><span>العربية</span>
            </a>
            <a href="{{ route('portal.lang', 'en') }}" class="{{ !$isRtl ? 'active' : '' }}">
                <span class="lang-flag">🇬🇧</span><span>English</span>
            </a>
        </div>
        <div class="brand-circle">
            @if($logoPath)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="logo">
            @else
                <i class="la la-truck"></i>
            @endif
        </div>
        <div class="brand-title">{{ $companyName }}</div>
        <div class="brand-subtitle">{{ __('portal.login_subtitle') }}</div>
    </div>

    <div class="card-body">
        <form method="POST" action="{{ route('portal.login.post') }}" autocomplete="off">
            @csrf

            {{-- Driver ID --}}
            <div class="form-group">
                <label class="form-label" for="driver_id">{{ __('portal.login_driver_id') }}</label>
                <div class="input-wrapper">
                    <i class="la la-id-card input-icon"></i>
                    <input type="text"
                           id="driver_id"
                           name="driver_id"
                           class="form-input {{ $errors->has('driver_id') ? 'is-invalid' : '' }}"
                           value="{{ old('driver_id') }}"
                           placeholder="{{ __('portal.login_driver_id_ph') }}"
                           autocomplete="username"
                           inputmode="text">
                </div>
                @error('driver_id')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Password --}}
            <div class="form-group">
                <label class="form-label" for="password">{{ __('portal.login_password') }}</label>
                <div class="input-wrapper">
                    <i class="la la-lock input-icon"></i>
                    <input type="password"
                           id="password"
                           name="password"
                           class="form-input {{ $errors->has('password') ? 'is-invalid' : '' }}"
                           placeholder="••••••••"
                           autocomplete="current-password">
                    <button type="button" class="password-toggle" onclick="togglePassword()" tabindex="-1">
                        <i class="la la-eye" id="pw-icon"></i>
                    </button>
                </div>
                @error('password')
                    <span class="invalid-feedback">{{ $message }}</span>
                @enderror
            </div>

            {{-- Remember me --}}
            <div class="remember-row">
                <input type="checkbox" id="remember" name="remember"
                       {{ old('remember') ? 'checked' : '' }}>
                <label for="remember">{{ __('portal.login_remember') }}</label>
            </div>

            <button type="submit" class="submit-btn">
                <i class="la la-sign-in" style="font-size:18px; {{ $isRtl ? 'margin-left:6px;' : 'margin-right:6px;' }}"></i>
                {{ __('portal.login_submit') }}
            </button>

            <span class="forgot-link">{{ __('portal.login_forgot') }}</span>
        </form>
    </div>
</div>

<p class="login-footer">{{ $companyName }} &mdash; {{ __('portal.login_footer_rights') }} {{ date('Y') }}</p>

<script>
    function togglePassword() {
        var inp  = document.getElementById('password');
        var icon = document.getElementById('pw-icon');
        if (inp.type === 'password') {
            inp.type = 'text';
            icon.className = 'la la-eye-slash';
        } else {
            inp.type = 'password';
            icon.className = 'la la-eye';
        }
    }
</script>
</body>
</html>
