@php $locale = app()->getLocale(); $isRtl = $locale === 'ar'; @endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $isRtl ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, user-scalable=no, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ __('portal.portal_title') }} | @yield('title', __('portal.dashboard_title'))</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@300;400;500;700;800&display=swap">
    @if($isRtl)
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.rtl.min.css">
    @else
        <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
    @endif
    <link rel="stylesheet" href="https://maxcdn.icons8.com/fonts/line-awesome/1.1/css/line-awesome.min.css">
    <style>
        /* ─── Tokens ─────────────────────────────────────────────── */
        :root {
            --primary:        #2563eb;
            --primary-dark:   #1d4ed8;
            --primary-light:  #eff6ff;
            --hs:             #ea580c;
            --hs-light:       #fff7ed;
            --cz:             #16a34a;
            --cz-light:       #f0fdf4;
            --bg:             #f1f5f9;
            --surface:        #ffffff;
            --border:         #e2e8f0;
            --text:           #0f172a;
            --muted:          #64748b;
            --success:        #16a34a;
            --danger:         #dc2626;
            --warning:        #d97706;
            --radius:         14px;
            --radius-sm:      10px;
            --shadow:         0 1px 3px rgba(0,0,0,.08), 0 1px 2px rgba(0,0,0,.06);
            --shadow-md:      0 4px 14px rgba(0,0,0,.1);
            --sidebar-w:      240px;
            --header-mob:     58px;
            --header-desk:    64px;
            --bottom-nav:     64px;
        }

        *, *::before, *::after { box-sizing: border-box; }

        html, body {
            margin: 0; padding: 0;
            font-family: 'Tajawal', sans-serif;
            color: var(--text);
            direction: {{ $isRtl ? 'rtl' : 'ltr' }};
            -webkit-text-size-adjust: 100%;
            font-size: 15px;
            min-height: 100%;
        }

        /* ─── Mobile: dark chrome around the phone card ───────── */
        @media (max-width: 767px) {
            body { background: #1e293b; }
        }

        /* ─── Desktop: neutral background ────────────────────── */
        @media (min-width: 768px) {
            body { background: var(--bg); }
        }

        /* ─── Phone-card wrapper (mobile only) ───────────────── */
        .portal-wrapper {
            max-width: 480px;
            margin: 0 auto;
            min-height: 100vh;
            background: var(--bg);
            position: relative;
        }

        @media (max-width: 767px) {
            .portal-wrapper { box-shadow: 0 0 50px rgba(0,0,0,.5); }
        }

        @media (min-width: 768px) {
            .portal-wrapper { max-width: none; background: transparent; }
        }

        /* ─── Header ─────────────────────────────────────────── */
        .portal-header {
            position: fixed;
            top: 0;
            width: 100%;
            max-width: 480px;
            height: var(--header-mob);
            background: var(--primary);
            z-index: 1000;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 16px;
            box-shadow: var(--shadow-md);
        }

        @media (min-width: 768px) {
            .portal-header {
                max-width: none;
                height: var(--header-desk);
                padding: 0 28px 0 20px;
                background: white;
                border-bottom: 1px solid var(--border);
                box-shadow: var(--shadow);
            }
        }

        .portal-brand {
            display: flex;
            align-items: center;
            gap: 9px;
            color: white;
            font-size: 14px;
            font-weight: 700;
            text-decoration: none;
            /* Take all available space, never overflow into actions */
            flex: 1 1 0;
            min-width: 0;
            overflow: hidden;
        }

        @media (min-width: 768px) {
            .portal-brand { color: var(--text); max-width: 320px; flex: 0 0 auto; }
        }

        .portal-brand-icon {
            width: 34px; height: 34px;
            border-radius: 8px;
            background: rgba(255,255,255,.18);
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; flex-shrink: 0; color: white;
        }

        @media (min-width: 768px) {
            .portal-brand-icon { background: var(--primary); }
        }

        .portal-brand-name {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .portal-header-actions {
            display: flex; align-items: center; gap: 10px;
            flex-shrink: 0;
        }

        /* On mobile, hide lang-btn text labels — show flags only */
        @media (max-width: 767px) {
            .lang-btn span:not(.lang-flag) { display: none; }
            .lang-btn { padding: 5px 8px; }
        }

        .portal-header-delegate {
            display: none;
            font-size: 13px; font-weight: 600; color: var(--muted);
            white-space: nowrap;
        }

        @media (min-width: 768px) {
            .portal-header-delegate { display: block; }
        }

        .portal-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: rgba(255,255,255,.22);
            border: 2px solid rgba(255,255,255,.4);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: white;
            overflow: hidden; text-decoration: none;
        }

        @media (min-width: 768px) {
            .portal-avatar {
                background: var(--primary-light);
                border-color: var(--border);
                color: var(--primary);
            }
        }

        .portal-avatar img { width: 100%; height: 100%; object-fit: cover; }

        .portal-logout-btn {
            background: rgba(255,255,255,.15);
            border: none; border-radius: 8px; color: white;
            width: 34px; height: 34px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; cursor: pointer; text-decoration: none;
            transition: background .2s, color .2s;
        }

        .portal-logout-btn:hover { background: rgba(255,255,255,.28); color: white; }

        @media (min-width: 768px) {
            .portal-logout-btn {
                background: var(--bg);
                color: var(--muted);
                border: 1.5px solid var(--border);
                border-radius: 9px;
            }
            .portal-logout-btn:hover { background: #fee2e2; color: var(--danger); border-color: #fecaca; }
        }

        /* ─── Sidebar (desktop only) ─────────────────────────── */
        .portal-sidebar { display: none; }

        @media (min-width: 768px) {
            .portal-sidebar {
                display: flex;
                flex-direction: column;
                position: fixed;
                top: var(--header-desk);
                right: 0;
                width: var(--sidebar-w);
                height: calc(100vh - var(--header-desk));
                background: white;
                border-left: 1px solid var(--border);
                z-index: 900;
                overflow-y: auto;
                padding: 12px 10px;
            }
        }

        .sidebar-delegate-widget {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px;
            border-radius: 12px;
            background: var(--bg);
            margin-bottom: 16px;
            text-decoration: none;
        }

        .sidebar-delegate-widget:hover { background: var(--primary-light); }

        .sidebar-av {
            width: 38px; height: 38px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), #7c3aed);
            display: flex; align-items: center; justify-content: center;
            font-size: 15px; font-weight: 800; color: white;
            overflow: hidden; flex-shrink: 0;
        }

        .sidebar-av img { width: 100%; height: 100%; object-fit: cover; }

        .sidebar-del-name {
            font-size: 13px; font-weight: 700; color: var(--text);
            line-height: 1.3;
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }

        .sidebar-del-id { font-size: 11px; color: var(--muted); }

        .sidebar-section-label {
            font-size: 10px; font-weight: 700; letter-spacing: .8px;
            color: var(--muted); text-transform: uppercase;
            padding: 0 12px; margin: 4px 0 6px;
        }

        .sidebar-nav-item {
            display: flex; align-items: center; gap: 10px;
            padding: 10px 12px; border-radius: 10px;
            text-decoration: none; color: var(--muted);
            font-size: 14px; font-weight: 500;
            transition: background .15s, color .15s;
            margin-bottom: 2px; border: none; background: none;
            cursor: pointer; width: 100%; font-family: 'Tajawal', sans-serif;
            text-align: right;
        }

        .sidebar-nav-item i { font-size: 20px; flex-shrink: 0; }
        .sidebar-nav-item:hover { background: var(--bg); color: var(--text); }

        .sidebar-nav-item.active {
            background: var(--primary-light);
            color: var(--primary);
            font-weight: 700;
        }

        .sidebar-nav-item.disabled { opacity: .38; pointer-events: none; }

        .sidebar-logout-zone {
            margin-top: auto; padding-top: 12px;
            border-top: 1px solid var(--border);
        }

        .sidebar-nav-item.logout-item {
            color: var(--danger);
        }

        .sidebar-nav-item.logout-item:hover { background: #fff1f2; color: #b91c1c; }

        /* ─── Content ─────────────────────────────────────────── */
        .portal-content {
            padding-top: var(--header-mob);
            padding-bottom: var(--bottom-nav);
            min-height: 100vh;
        }

        @media (min-width: 768px) {
            .portal-content {
                padding-top: var(--header-desk);
                padding-bottom: 40px;
                padding-right: var(--sidebar-w);
            }
        }

        .portal-page {
            padding: 14px 14px 6px;
        }

        @media (min-width: 768px) {
            .portal-page { padding: 28px 32px; }
        }

        /* ─── Bottom Nav (mobile only) ───────────────────────── */
        .portal-bottom-nav {
            position: fixed;
            bottom: 0; width: 100%; max-width: 480px;
            height: var(--bottom-nav);
            background: white;
            border-top: 1px solid var(--border);
            display: flex; z-index: 1000;
            box-shadow: 0 -2px 12px rgba(0,0,0,.08);
        }

        @media (min-width: 768px) {
            .portal-bottom-nav { display: none; }
        }

        .portal-nav-item {
            flex: 1;
            display: flex; flex-direction: column;
            align-items: center; justify-content: center;
            height: 100%; text-decoration: none;
            color: var(--muted); font-size: 10px; font-weight: 500;
            gap: 2px; position: relative; transition: color .2s;
        }

        .portal-nav-item i { font-size: 24px; line-height: 1; }
        .portal-nav-item.active { color: var(--primary); }

        .portal-nav-item.active::after {
            content: '';
            position: absolute; top: 0;
            left: 25%; right: 25%;
            height: 3px; background: var(--primary);
            border-radius: 0 0 4px 4px;
        }

        .portal-nav-item.disabled { opacity: .38; pointer-events: none; }

        /* ─── Flash alerts ───────────────────────────────────── */
        .portal-alert {
            border-radius: 10px; font-size: 14px;
            padding: 12px 16px;
            display: flex; align-items: center; gap: 8px;
            margin-bottom: 14px;
        }

        /* ─── Base card ──────────────────────────────────────── */
        .p-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
        }

        /* ─── Welcome gradient card ──────────────────────────── */
        .welcome-card {
            background: linear-gradient(135deg, #1d4ed8 0%, var(--primary) 55%, #7c3aed 100%);
            border-radius: var(--radius);
            padding: 20px;
            color: white;
            position: relative;
            overflow: hidden;
        }

        .welcome-card::before {
            content: ''; position: absolute;
            top: -30px; left: -30px;
            width: 100px; height: 100px;
            border-radius: 50%;
            background: rgba(255,255,255,.07);
        }

        .welcome-card::after {
            content: ''; position: absolute;
            bottom: -40px; right: -25px;
            width: 120px; height: 120px;
            border-radius: 50%;
            background: rgba(255,255,255,.05);
        }

        .welcome-avatar {
            width: 52px; height: 52px;
            border-radius: 50%;
            border: 3px solid rgba(255,255,255,.4);
            overflow: hidden;
            background: rgba(255,255,255,.2);
            display: flex; align-items: center; justify-content: center;
            font-size: 20px; font-weight: 800; flex-shrink: 0;
        }

        .welcome-avatar img { width: 100%; height: 100%; object-fit: cover; }

        /* ─── KPI grid ───────────────────────────────────────── */
        .kpi-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px;
        }

        @media (min-width: 768px)  { .kpi-grid { grid-template-columns: repeat(3, 1fr); gap: 14px; } }
        @media (min-width: 1100px) { .kpi-grid { grid-template-columns: repeat(6, 1fr); } }

        .kpi-card {
            background: white;
            border-radius: var(--radius-sm);
            padding: 14px;
            box-shadow: var(--shadow);
            transition: box-shadow .2s;
        }

        @media (min-width: 768px) { .kpi-card { padding: 18px 16px; } }
        .kpi-card:hover { box-shadow: var(--shadow-md); }

        .kpi-icon {
            width: 38px; height: 38px;
            border-radius: 9px;
            display: flex; align-items: center; justify-content: center;
            font-size: 19px; margin-bottom: 10px;
        }

        @media (min-width: 768px) { .kpi-icon { width: 42px; height: 42px; font-size: 21px; } }

        .kpi-value {
            font-size: 17px; font-weight: 800; line-height: 1;
            margin-bottom: 4px; font-variant-numeric: tabular-nums;
        }

        @media (min-width: 768px) { .kpi-value { font-size: 19px; } }

        .kpi-label { font-size: 11px; color: var(--muted); }

        /* ─── Settlement row (dashboard recent) ──────────────── */
        .settlement-row {
            background: white;
            border-radius: var(--radius-sm);
            box-shadow: var(--shadow);
            display: flex; align-items: stretch;
            overflow: hidden; margin-bottom: 10px;
            transition: box-shadow .15s;
        }

        .settlement-row:hover { box-shadow: var(--shadow-md); }
        .settlement-row .sr-bar { width: 4px; flex-shrink: 0; }
        .settlement-row .sr-bar.hs  { background: var(--hs); }
        .settlement-row .sr-bar.cz  { background: var(--cz); }
        .settlement-row .sr-bar.def { background: var(--primary); }
        .settlement-row .sr-body { padding: 13px 14px; flex: 1; min-width: 0; }

        /* ─── Month card (settlements index) ────────────────── */
        .month-card {
            background: white;
            border-radius: var(--radius);
            box-shadow: var(--shadow);
            overflow: hidden;
            transition: box-shadow .2s, transform .15s;
            display: flex; flex-direction: column;
        }

        .month-card:hover { box-shadow: var(--shadow-md); transform: translateY(-2px); }

        .month-card-top {
            height: 5px; flex-shrink: 0;
        }

        .month-card-top.hs { background: linear-gradient(90deg, var(--hs), #f97316); }
        .month-card-top.cz { background: linear-gradient(90deg, var(--cz), #4ade80); }
        .month-card-top.def { background: linear-gradient(90deg, var(--primary), #7c3aed); }

        .month-card-body { padding: 16px; flex: 1; }
        .month-card-footer {
            padding: 10px 16px;
            background: var(--bg);
            border-top: 1px solid var(--border);
            display: flex; align-items: center; justify-content: space-between;
        }

        /* ─── Settlements grid ───────────────────────────────── */
        .settlements-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 12px;
        }

        @media (min-width: 640px)  { .settlements-grid { grid-template-columns: repeat(2, 1fr); } }
        @media (min-width: 1100px) { .settlements-grid { grid-template-columns: repeat(3, 1fr); } }

        /* ─── Badges ─────────────────────────────────────────── */
        .status-badge {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 9px; border-radius: 20px;
            font-size: 11px; font-weight: 600;
        }

        .status-badge.published { background: #dcfce7; color: #15803d; }
        .status-badge.closed    { background: #dbeafe; color: #1d4ed8; }
        .status-badge.approved  { background: #fef9c3; color: #854d0e; }
        .status-badge.open      { background: #f1f5f9; color: var(--muted); }
        .status-badge.editing   { background: #fef3c7; color: #92400e; }

        .platform-badge {
            display: inline-flex; align-items: center; gap: 4px;
            padding: 2px 8px; border-radius: 6px;
            font-size: 11px; font-weight: 600;
        }

        .platform-badge.hs { background: var(--hs-light); color: var(--hs); }
        .platform-badge.cz { background: var(--cz-light); color: var(--cz); }

        /* ─── Section heading ────────────────────────────────── */
        .section-header {
            font-size: 12px; font-weight: 700;
            color: var(--muted); letter-spacing: .4px;
            margin-bottom: 10px; padding-right: 2px;
            text-transform: uppercase;
        }

        /* ─── Profile info rows ──────────────────────────────── */
        .info-row {
            display: flex; align-items: center; gap: 12px;
            padding: 12px 16px;
            border-bottom: 1px solid var(--border);
        }

        .info-row:last-child { border-bottom: none; }

        .info-icon {
            width: 34px; height: 34px; border-radius: 9px;
            background: var(--primary-light); color: var(--primary);
            display: flex; align-items: center; justify-content: center;
            font-size: 17px; flex-shrink: 0;
        }

        .info-label { font-size: 11px; color: var(--muted); margin-bottom: 1px; }
        .info-value { font-size: 14px; font-weight: 600; color: var(--text); }

        /* ─── Form controls ──────────────────────────────────── */
        .portal-input {
            width: 100%; padding: 12px 14px;
            border: 1.5px solid var(--border); border-radius: 10px;
            font-family: 'Tajawal', sans-serif; font-size: 15px;
            color: var(--text); background: white;
            outline: none; transition: border-color .2s, box-shadow .2s;
        }

        .portal-input:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(37,99,235,.12);
        }

        .portal-input.is-invalid { border-color: var(--danger); }

        .portal-btn {
            display: block; width: 100%; padding: 13px;
            border-radius: 11px; border: none;
            font-family: 'Tajawal', sans-serif; font-size: 16px; font-weight: 700;
            cursor: pointer; transition: opacity .2s, transform .1s;
        }

        .portal-btn:active { transform: scale(.98); }
        .portal-btn-primary { background: linear-gradient(135deg, var(--primary), var(--primary-dark)); color: white; }
        .portal-btn-outline { background: white; border: 1.5px solid var(--border); color: var(--text); }

        /* ─── Net salary box ─────────────────────────────────── */
        .net-box {
            background: linear-gradient(135deg, #f0fdf4, #dcfce7);
            border: 1px solid #bbf7d0;
            border-radius: var(--radius-sm);
            padding: 20px; text-align: center;
        }

        .net-box .amount { font-size: 34px; font-weight: 800; color: #15803d; line-height: 1; font-variant-numeric: tabular-nums; }
        .net-box .sub    { font-size: 13px; color: var(--muted); margin-top: 4px; }

        /* ─── Detail tables ──────────────────────────────────── */
        .detail-table { width: 100%; font-size: 14px; border-collapse: collapse; }
        .detail-table td { padding: 10px 14px; border-bottom: 1px solid var(--border); }
        .detail-table tr:last-child td { border-bottom: none; }
        .detail-table tr:nth-child(even) td { background: #f8fafc; }
        .detail-table td:last-child { font-weight: 700; text-align: left; font-variant-numeric: tabular-nums; }
        .detail-table .total-row td  { background: #1e40af; color: white; font-weight: 700; border-bottom: none; }
        .detail-table .total-row td:last-child { color: white; }
        .detail-table .ded-row td:last-child  { color: var(--danger); }
        .detail-table .cred-row td:last-child { color: var(--success); }
        .detail-table .net-row td  { background: #15803d; color: white; font-weight: 800; font-size: 15px; border-bottom: none; }
        .detail-table .net-row td:last-child { color: white; }

        /* ─── Settlement detail 2-col (desktop) ──────────────── */
        @media (min-width: 768px) {
            .settlement-detail-grid {
                display: grid;
                grid-template-columns: 1fr 300px;
                gap: 22px;
                align-items: start;
            }
        }

        /* ─── Trend bars ─────────────────────────────────────── */
        .trend-wrap {
            display: flex; align-items: flex-end; gap: 5px;
            height: 36px;
        }

        .trend-bar {
            flex: 1; border-radius: 4px 4px 0 0;
            min-height: 4px;
            background: rgba(37,99,235,.2);
        }

        .trend-bar.current { background: var(--primary); }

        /* ─── Comparison chip ────────────────────────────────── */
        .cmp-chip {
            display: inline-flex; align-items: center; gap: 3px;
            padding: 2px 7px; border-radius: 20px;
            font-size: 11px; font-weight: 700;
        }

        .cmp-chip.up   { background: #dcfce7; color: #15803d; }
        .cmp-chip.down { background: #fee2e2; color: #dc2626; }
        .cmp-chip.flat { background: #f1f5f9; color: var(--muted); }

        /* ─── Empty state ────────────────────────────────────── */
        .empty-state {
            text-align: center; padding: 48px 20px; color: var(--muted);
        }

        .empty-state i { font-size: 52px; opacity: .22; display: block; margin-bottom: 14px; }
        .empty-state h6 { font-size: 15px; font-weight: 700; color: #334155; margin-bottom: 6px; }
        .empty-state p  { font-size: 13px; margin: 0; line-height: 1.6; }

        /* ─── Language switch ────────────────────────────────── */
        .lang-switch {
            display: inline-flex; align-items: stretch;
            border-radius: 9px; overflow: hidden;
            /* Mobile: on the header gradient */
            border: 1.5px solid rgba(255,255,255,.25);
            background: rgba(255,255,255,.08);
        }

        .lang-btn {
            display: inline-flex; align-items: center; gap: 5px;
            font-size: 12px; font-weight: 500;
            padding: 5px 11px;
            text-decoration: none; cursor: pointer;
            color: rgba(255,255,255,.6);
            font-family: 'Tajawal', sans-serif;
            transition: background .15s, color .15s;
            line-height: 1; white-space: nowrap;
        }

        .lang-btn .lang-flag { font-size: 15px; line-height: 1; flex-shrink: 0; }

        .lang-btn + .lang-btn {
            border-inline-start: 1px solid rgba(255,255,255,.2);
        }

        .lang-btn:hover { color: rgba(255,255,255,.9); background: rgba(255,255,255,.12); }

        .lang-btn.active-lang {
            background: rgba(255,255,255,.22); color: white; font-weight: 700;
        }

        @media (min-width: 768px) {
            .lang-switch {
                border: 1.5px solid var(--border);
                background: var(--bg);
            }
            .lang-btn {
                color: #64748b;
                background: transparent;
            }
            .lang-btn + .lang-btn {
                border-inline-start: 1px solid var(--border);
            }
            .lang-btn:hover { background: #f1f5f9; color: var(--text); }
            .lang-btn.active-lang {
                background: #eff6ff; color: #2563eb;
                font-weight: 700;
            }
        }

        /* ─── LTR layout overrides ───────────────────────────── */
        @media (min-width: 768px) {
            html[dir="ltr"] .portal-sidebar {
                right: auto;
                left: 0;
                border-left: none;
                border-right: 1px solid var(--border);
            }
            html[dir="ltr"] .portal-content {
                padding-right: 0;
                padding-left: var(--sidebar-w);
            }
        }

        html[dir="ltr"] .sidebar-nav-item { text-align: left; }
        html[dir="ltr"] .section-header { padding-right: 0; padding-left: 2px; }
        html[dir="ltr"] .detail-table td:last-child { text-align: right; }
        html[dir="ltr"] .notif-dot-sidebar { left: auto !important; right: 8px !important; }
        html[dir="ltr"] .notif-dot-bottom {
            left: auto !important;
            right: 50% !important;
            transform: translateX(6px) !important;
        }
    </style>
    @stack('styles')
</head>
<body>
@php
    $companyNameEn = \App\Models\SystemSetting::get('company_name_en') ?? '';
    $companyName  = (app()->getLocale() === 'en' && $companyNameEn)
        ? $companyNameEn
        : (\App\Models\SystemSetting::get('company_name_ar') ?? 'شيلت للخدمات اللوجستية');
    $logoPath     = \App\Models\SystemSetting::get('company_logo_path') ?? '';
    $authDelegate = auth('delegate')->user();
    $fallback     = app()->getLocale() === 'en' ? 'D' : 'م';
    $initials     = $authDelegate ? mb_substr($authDelegate->name ?? $fallback, 0, 1) : $fallback;
    $unreadNotifs = 0;
    if ($authDelegate) {
        $unreadNotifs = \App\Models\DelegateNotification::where('delegate_id', $authDelegate->id)
                ->whereNull('read_at')->count()
            + \Illuminate\Support\Facades\DB::table('notifications')
                ->where('recipient_type', 'delegate')
                ->where('recipient_id', $authDelegate->id)
                ->whereNull('read_at')
                ->count();
    }
@endphp
<div class="portal-wrapper">

    {{-- ─── Header ─────────────────────────────────────────────── --}}
    <header class="portal-header">
        <a href="{{ route('portal.dashboard') }}" class="portal-brand">
            @if($logoPath)
                <img src="{{ \Illuminate\Support\Facades\Storage::url($logoPath) }}" alt="logo"
                     style="height:30px; border-radius:6px; flex-shrink:0;">
            @else
                <div class="portal-brand-icon"><i class="la la-truck"></i></div>
            @endif
            <span class="portal-brand-name">{{ $companyName }}</span>
        </a>

        <div class="portal-header-actions">
            <div class="lang-switch">
                <a href="{{ route('portal.lang', 'ar') }}" class="lang-btn {{ $isRtl ? 'active-lang' : '' }}">
                    <span class="lang-flag">🇸🇦</span><span>العربية</span>
                </a>
                <a href="{{ route('portal.lang', 'en') }}" class="lang-btn {{ !$isRtl ? 'active-lang' : '' }}">
                    <span class="lang-flag">🇬🇧</span><span>English</span>
                </a>
            </div>
            <span class="portal-header-delegate">{{ $authDelegate?->name ?? '' }}</span>
            {{-- P6-006: Bell button — visible on mobile header only (sidebar shows it on desktop) --}}
            @auth('delegate')
            <a href="{{ route('portal.notifications.index') }}"
               class="portal-logout-btn d-md-none"
               title="{{ __('portal.nav_notifications') }}"
               style="position:relative;">
                <i class="la la-bell"></i>
                @if($unreadNotifs > 0)
                    <span style="position:absolute;top:2px;{{ $isRtl ? 'left:2px;' : 'right:2px;' }}
                                 width:8px;height:8px;border-radius:50%;
                                 background:#ef4444;display:block;"></span>
                @endif
            </a>
            @endauth
            <a href="{{ route('portal.profile') }}" class="portal-avatar">
                @if($authDelegate?->profile_photo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($authDelegate->profile_photo) }}" alt="">
                @else
                    {{ $initials }}
                @endif
            </a>
            <form method="POST" action="{{ route('portal.logout') }}" style="margin:0;">
                @csrf
                <button type="submit" class="portal-logout-btn" title="{{ __('portal.logout_title') }}">
                    <i class="la la-sign-out"></i>
                </button>
            </form>
        </div>
    </header>

    {{-- ─── Sidebar (desktop only) ───────────────────────────── --}}
    <nav class="portal-sidebar">
        <a href="{{ route('portal.profile') }}" class="sidebar-delegate-widget" style="text-decoration:none;">
            <div class="sidebar-av">
                @if($authDelegate?->profile_photo)
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($authDelegate->profile_photo) }}" alt="">
                @else
                    {{ $initials }}
                @endif
            </div>
            <div style="min-width:0;">
                <div class="sidebar-del-name">{{ $authDelegate?->name ?? __('portal.sidebar_delegate_fallback') }}</div>
                <div class="sidebar-del-id">{{ $authDelegate?->delegate_code ?? '' }}</div>
            </div>
        </a>

        <div class="sidebar-section-label">{{ __('portal.nav_main_menu') }}</div>

        <a href="{{ route('portal.dashboard') }}"
           class="sidebar-nav-item {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
            <i class="la la-home"></i>
            <span>{{ __('portal.nav_home') }}</span>
        </a>

        <a href="{{ route('portal.settlements.index') }}"
           class="sidebar-nav-item {{ request()->routeIs('portal.settlements.*') ? 'active' : '' }}">
            <i class="la la-history"></i>
            <span>{{ __('portal.nav_settlements') }}</span>
        </a>

        <a href="{{ route('portal.profile') }}"
           class="sidebar-nav-item {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
            <i class="la la-user"></i>
            <span>{{ __('portal.nav_profile') }}</span>
        </a>

        <a href="{{ route('portal.support.tickets.index') }}"
           class="sidebar-nav-item {{ request()->routeIs('portal.support.*') ? 'active' : '' }}">
            <i class="la la-life-ring"></i>
            <span>{{ __('portal.nav_support') }}</span>
        </a>

        <a href="{{ route('portal.notifications.index') }}"
           class="sidebar-nav-item {{ request()->routeIs('portal.notifications.*') ? 'active' : '' }}"
           style="position:relative;">
            <i class="la la-bell"></i>
            <span>{{ __('portal.nav_notifications') }}</span>
            @if($unreadNotifs > 0)
                <span id="notif-badge"
                      class="notif-dot-sidebar"
                      style="position:absolute;top:8px;left:8px;
                             min-width:18px;height:18px;border-radius:10px;
                             background:#dc2626;color:white;font-size:10px;font-weight:700;
                             display:flex;align-items:center;justify-content:center;padding:0 4px;">
                    {{ $unreadNotifs > 99 ? '99+' : $unreadNotifs }}
                </span>
            @endif
        </a>

        <div class="sidebar-logout-zone">
            <form method="POST" action="{{ route('portal.logout') }}">
                @csrf
                <button type="submit" class="sidebar-nav-item logout-item">
                    <i class="la la-sign-out"></i>
                    <span>{{ __('portal.nav_logout') }}</span>
                </button>
            </form>
        </div>
    </nav>

    {{-- ─── Main content ───────────────────────────────────────── --}}
    <main class="portal-content">
        <div class="portal-page">
            @if(session('success'))
                <div class="portal-alert" style="background:#dcfce7; color:#15803d;">
                    <i class="la la-check-circle" style="font-size:18px;"></i>
                    {{ session('success') }}
                </div>
            @endif
            @if(session('error'))
                <div class="portal-alert" style="background:#fee2e2; color:#dc2626;">
                    <i class="la la-times-circle" style="font-size:18px;"></i>
                    {{ session('error') }}
                </div>
            @endif
            @if(session('info'))
                <div class="portal-alert" style="background:#dbeafe; color:#1d4ed8;">
                    <i class="la la-info-circle" style="font-size:18px;"></i>
                    {{ session('info') }}
                </div>
            @endif
            @yield('content')
        </div>
    </main>

    {{-- ─── Bottom nav (mobile only) ──────────────────────────── --}}
    <nav class="portal-bottom-nav">
        <a href="{{ route('portal.dashboard') }}"
           class="portal-nav-item {{ request()->routeIs('portal.dashboard') ? 'active' : '' }}">
            <i class="la la-home"></i><span>{{ __('portal.nav_home') }}</span>
        </a>
        <a href="{{ route('portal.settlements.index') }}"
           class="portal-nav-item {{ request()->routeIs('portal.settlements.*') ? 'active' : '' }}">
            <i class="la la-history"></i><span>{{ __('portal.nav_settlements') }}</span>
        </a>
        <a href="{{ route('portal.profile') }}"
           class="portal-nav-item {{ request()->routeIs('portal.profile') ? 'active' : '' }}">
            <i class="la la-user"></i><span>{{ __('portal.nav_profile_short') }}</span>
        </a>
        <a href="{{ route('portal.support.tickets.index') }}"
           class="portal-nav-item {{ request()->routeIs('portal.support.*') ? 'active' : '' }}">
            <i class="la la-life-ring"></i><span>{{ __('portal.nav_support_short') }}</span>
        </a>
        <a href="{{ route('portal.notifications.index') }}"
           class="portal-nav-item {{ request()->routeIs('portal.notifications.*') ? 'active' : '' }}"
           style="position:relative;">
            <i class="la la-bell"></i>
            <span>{{ __('portal.nav_notifications_short') }}</span>
            @if($unreadNotifs > 0)
                <span class="notif-dot-bottom"
                      style="position:absolute;top:8px;
                             left:50%;transform:translateX(6px);
                             min-width:16px;height:16px;border-radius:8px;
                             background:#dc2626;color:white;font-size:9px;font-weight:700;
                             display:flex;align-items:center;justify-content:center;padding:0 3px;">
                    {{ $unreadNotifs > 9 ? '9+' : $unreadNotifs }}
                </span>
            @endif
        </a>
    </nav>

</div>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
@stack('scripts')
</body>
</html>
