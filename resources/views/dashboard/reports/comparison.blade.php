@extends('layouts.dashboard.app')
@section('title') مقارنة الفترات @endsection

@section('content')
<div class="app-content content" dir="rtl">
<div class="content-wrapper">
<style>
/* ── Base ── */
.cmp-body { font-family:'Arial','Tahoma',sans-serif; padding:4px 0 24px; }

/* ── Page header ── */
.cmp-head {
    background:linear-gradient(135deg,#0c1b3a 0%,#1e3a8a 70%,#312e81 100%);
    padding:16px 20px; color:#fff; display:flex; align-items:center; gap:14px;
    border-radius:10px; margin-bottom:16px;
    box-shadow:0 2px 8px rgba(0,0,0,.15);
}
.cmp-head-icon { width:40px; height:40px; border-radius:10px; background:rgba(255,255,255,.12); display:flex; align-items:center; justify-content:center; font-size:20px; flex-shrink:0; }
.cmp-head h1 { font-size:17px; font-weight:800; margin:0 0 2px; }
.cmp-head p  { font-size:11px; opacity:.6; margin:0; }

/* ── Section card ── */
.cmp-card { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 12px rgba(0,0,0,.05); margin-bottom:16px; overflow:hidden; }
.cmp-card-head { padding:12px 16px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; }
.cmp-card-title { font-size:13px; font-weight:800; color:#1e293b; }
.cmp-card-body  { padding:16px; }

/* ════════════════════════════════════════
   SELECTOR: two-panel layout
   ════════════════════════════════════════ */
.sel-wrapper {
    background:#fff;
    border-radius:12px;
    box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 12px rgba(0,0,0,.05);
    margin-bottom:16px;
    overflow:hidden;
}
.sel-wrapper-head {
    padding:12px 18px;
    border-bottom:1px solid #f1f5f9;
    font-size:13px; font-weight:800; color:#1e293b;
    display:flex; align-items:center; gap:8px;
}

/* Panel grid: A | VS | B */
.panel-grid {
    display:grid;
    grid-template-columns:1fr 52px 1fr;
    align-items:stretch;
}
@media(max-width:680px){
    .panel-grid { grid-template-columns:1fr; }
    .vs-col { border-top:1px solid #f1f5f9; border-bottom:1px solid #f1f5f9; padding:10px; }
}

/* Comparison panel */
.cmp-panel { padding:18px 20px; }
.panel-a   { border-left:1px solid #f1f5f9; }

/* Panel heading strip */
.cmp-panel-head {
    display:flex; align-items:center; gap:10px;
    margin-bottom:16px; padding-bottom:12px;
    border-bottom:2px solid #f1f5f9;
}
.panel-a .cmp-panel-head { border-bottom-color:#bfdbfe; }
.panel-b .cmp-panel-head { border-bottom-color:#fde68a; }
.panel-badge {
    width:28px; height:28px; border-radius:50%;
    display:flex; align-items:center; justify-content:center;
    font-size:13px; font-weight:900; color:#fff; flex-shrink:0;
}
.panel-a .panel-badge { background:#1e40af; }
.panel-b .panel-badge { background:#b45309; }
.panel-head-title { font-size:13px; font-weight:800; color:#1e293b; }
.panel-head-sub   { font-size:10px; color:#94a3b8; font-weight:600; margin-top:1px; }

/* VS column */
.vs-col {
    display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    gap:6px; color:#94a3b8;
    background:#fafbfc;
    border-left:1px solid #f1f5f9;
    border-right:1px solid #f1f5f9;
}
.vs-circle {
    width:36px; height:36px; border-radius:50%;
    border:2px solid #e2e8f0; background:#fff;
    display:flex; align-items:center; justify-content:center;
    font-size:10px; font-weight:900; color:#475569;
}

/* Form rows */
.sel-row { margin-bottom:13px; }
.sel-row:last-child { margin-bottom:0; }
.sel-label {
    font-size:10px; font-weight:800; color:#94a3b8;
    text-transform:uppercase; letter-spacing:.5px;
    margin-bottom:5px;
}

/* Segmented control (platform & period type) */
.seg-ctrl {
    display:flex; border:1.5px solid #e2e8f0;
    border-radius:8px; overflow:hidden; background:#f8fafc;
}
.seg-ctrl input[type=radio] { display:none; }
.seg-ctrl label {
    flex:1; text-align:center; padding:7px 6px;
    font-size:11px; font-weight:700; color:#64748b;
    cursor:pointer; transition:.12s; user-select:none;
    white-space:nowrap;
}
.seg-ctrl label:hover { background:#f1f5f9; }
.panel-a .seg-ctrl input:checked + label { background:#1e40af; color:#fff; }
.panel-b .seg-ctrl input:checked + label { background:#b45309; color:#fff; }

/* Dropdowns */
.cmp-select {
    width:100%; padding:8px 10px;
    border:1.5px solid #e2e8f0; border-radius:8px;
    font-size:12px; font-weight:600; color:#1e293b;
    background:#f8fafc; outline:none;
    appearance:none; -webkit-appearance:none;
    cursor:pointer; font-family:inherit;
}
.cmp-select:focus { border-color:#93c5fd; background:#fff; box-shadow:0 0 0 3px rgba(147,197,253,.2); }
.panel-b .cmp-select:focus { border-color:#fcd34d; box-shadow:0 0 0 3px rgba(252,211,77,.2); }

.sel-two-col { display:grid; grid-template-columns:1fr 1fr; gap:8px; }

/* Period type sub-sections */
.period-section { margin-top:10px; }

/* Result mini-strip (shown when data computed) */
.panel-result {
    margin-top:14px; padding:10px 12px;
    border-radius:8px; display:flex; align-items:center; gap:14px;
    flex-wrap:wrap;
}
.panel-a .panel-result { background:#eff6ff; border:1px solid #bfdbfe; }
.panel-b .panel-result { background:#fffbeb; border:1px solid #fde68a; }
.panel-result-stat { text-align:center; min-width:0; }
.panel-result-val {
    font-size:16px; font-weight:800;
    font-variant-numeric:tabular-nums; line-height:1.2;
}
.panel-a .panel-result-val { color:#1e40af; }
.panel-b .panel-result-val { color:#b45309; }
.panel-result-lbl { font-size:10px; color:#64748b; font-weight:600; }
.panel-result-sep { width:1px; height:32px; background:#e2e8f0; flex-shrink:0; }

/* Compare button */
.sel-footer {
    padding:14px 18px;
    border-top:1px solid #f1f5f9;
    display:flex; align-items:center; gap:12px;
}
.cmp-btn {
    background:linear-gradient(135deg,#1e40af,#3b82f6); color:#fff;
    border:none; border-radius:10px; padding:11px 36px;
    font-size:14px; font-weight:800; cursor:pointer;
    box-shadow:0 2px 8px rgba(30,64,175,.3); letter-spacing:.3px;
    font-family:inherit;
}
.cmp-btn:hover { background:linear-gradient(135deg,#1d3a9a,#2563eb); }
.cmp-btn:active { transform:translateY(1px); }
.sel-hint { font-size:11px; color:#94a3b8; font-weight:600; }

/* ── Group summary strips ── */
.grp-strips { display:grid; grid-template-columns:1fr auto 1fr; gap:0; margin-bottom:16px; align-items:stretch; }
.grp-strip {
    border-radius:12px; padding:14px 16px;
    box-shadow:0 1px 3px rgba(0,0,0,.07);
    display:flex; align-items:center; gap:14px; flex-wrap:wrap;
}
.grp-strip-a { background:linear-gradient(135deg,#eff6ff,#dbeafe); border:1.5px solid #93c5fd; }
.grp-strip-b { background:linear-gradient(135deg,#fffbeb,#fef3c7); border:1.5px solid #fcd34d; }
.grp-strip-label { font-size:13px; font-weight:800; color:#1e293b; flex:1; min-width:0; }
.grp-stat { text-align:center; flex-shrink:0; }
.grp-stat-val { font-size:17px; font-weight:800; font-variant-numeric:tabular-nums; color:#1e293b; }
.grp-stat-lbl { font-size:10px; color:#64748b; font-weight:600; }
.vs-divider { display:flex; align-items:center; justify-content:center; padding:0 12px; font-size:20px; font-weight:900; color:#94a3b8; }

/* ── KPI metric cards ── */
.metric-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(200px,1fr)); gap:12px; margin-bottom:16px; }
.metric-card { background:#fff; border-radius:12px; padding:14px; box-shadow:0 1px 3px rgba(0,0,0,.07); border-top:3px solid var(--mc,#6366f1); }
.metric-name { font-size:11px; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.5px; margin-bottom:10px; }
.metric-row  { display:flex; justify-content:space-between; align-items:center; padding:4px 0; border-bottom:1px solid #f8fafc; }
.metric-row:last-child { border-bottom:none; }
.metric-lbl  { font-size:11px; color:#94a3b8; font-weight:600; display:flex; align-items:center; gap:5px; }
.metric-val  { font-size:12px; font-weight:800; font-variant-numeric:tabular-nums; }
.val-a { color:#1e40af; }
.val-b { color:#b45309; }

/* ── Delta badge ── */
.delta { display:inline-flex; align-items:center; gap:2px; padding:1px 7px; border-radius:8px; font-size:11px; font-weight:800; font-variant-numeric:tabular-nums; }
.delta-up   { background:#dcfce7; color:#15803d; }
.delta-down { background:#fee2e2; color:#dc2626; }
.delta-zero { background:#f1f5f9; color:#94a3b8; }
.pct-badge  { font-size:10px; color:#94a3b8; margin-right:3px; }

/* ── Charts ── */
.chart-row { display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:12px; }
@media(max-width:768px){ .chart-row { grid-template-columns:1fr; } }
.chart-card { background:#fff; border-radius:12px; box-shadow:0 1px 3px rgba(0,0,0,.07); overflow:hidden; }
.chart-card-head { padding:10px 14px; border-bottom:1px solid #f1f5f9; font-size:12px; font-weight:800; color:#1e293b; display:flex; align-items:center; justify-content:space-between; }
.chart-card-body { padding:12px 14px; }

/* ── Legend ── */
.legend { display:flex; gap:12px; font-size:11px; font-weight:700; }
.legend-dot { width:10px; height:10px; border-radius:2px; display:inline-block; margin-left:4px; }

/* ── Empty state ── */
.cmp-empty { text-align:center; padding:48px 24px; }
.cmp-empty-icon { font-size:44px; margin-bottom:14px; }
.cmp-empty h3 { font-size:17px; font-weight:700; color:#334155; margin:0 0 8px; }
.cmp-empty p  { font-size:13px; color:#64748b; max-width:380px; margin:0 auto; line-height:1.7; }

/* ── Helper ── */
.num { font-variant-numeric:tabular-nums; }
.text-a { color:#1e40af; }
.text-b { color:#b45309; }
</style>

    {{-- Breadcrumb --}}
    <div class="content-header row">
        <div class="mb-2 content-header-left col-12 breadcrumb-new">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dashboard.reports.executive') }}">التقارير</a></li>
                <li class="breadcrumb-item active">مقارنة الفترات</li>
            </ol>
        </div>
    </div>

    <div class="content-body">
<div class="cmp-body">

{{-- Page header --}}
<div class="cmp-head">
    <div class="cmp-head-icon">⚖️</div>
    <div>
        <h1>مقارنة الفترات</h1>
        <p>قارن أداء المنصات بين فترتين — جميع البيانات من التسويات المعتمدة</p>
    </div>
</div>

{{-- ═══════════════ SELECTOR ═══════════════ --}}
@php
$arabicMonths = [1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
                 7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر'];
$aType       = $aParams['type']       ?? 'single';
$aYear       = $aParams['year']       ?? ($availableYears[count($availableYears)-1] ?? date('Y'));
$aMonth      = (int)($aParams['month']      ?? 0);
$aFromMonth  = (int)($aParams['from_month'] ?? 0);
$aToMonth    = (int)($aParams['to_month']   ?? 0);
$bType       = $bParams['type']       ?? 'single';
$bYear       = $bParams['year']       ?? ($availableYears[count($availableYears)-1] ?? date('Y'));
$bMonth      = (int)($bParams['month']      ?? 0);
$bFromMonth  = (int)($bParams['from_month'] ?? 0);
$bToMonth    = (int)($bParams['to_month']   ?? 0);
@endphp

<form method="GET" action="{{ route('dashboard.reports.comparison') }}" id="cmpForm">

<div class="sel-wrapper">
    <div class="sel-wrapper-head">⚙️ إعداد المقارنة</div>

    <div class="panel-grid">

        {{-- ════ PANEL A ════ --}}
        <div class="cmp-panel panel-a">

            <div class="cmp-panel-head">
                <div class="panel-badge">أ</div>
                <div>
                    <div class="panel-head-title">الفترة الأولى</div>
                    <div class="panel-head-sub">المجموعة المرجعية</div>
                </div>
            </div>

            {{-- Platform --}}
            <div class="sel-row">
                <div class="sel-label">المنصة</div>
                <div class="seg-ctrl">
                    <input type="radio" name="a[platform]" id="a-plat-all" value="all" {{ $aPlatform === 'all' ? 'checked' : '' }}>
                    <label for="a-plat-all">الكل</label>
                    <input type="radio" name="a[platform]" id="a-plat-hs"  value="hs"  {{ $aPlatform === 'hs'  ? 'checked' : '' }}>
                    <label for="a-plat-hs">هنقرستيشن</label>
                    <input type="radio" name="a[platform]" id="a-plat-cz"  value="cz"  {{ $aPlatform === 'cz'  ? 'checked' : '' }}>
                    <label for="a-plat-cz">شيفز</label>
                </div>
            </div>

            {{-- Period type --}}
            <div class="sel-row">
                <div class="sel-label">نوع الفترة</div>
                <div class="seg-ctrl">
                    <input type="radio" name="a[type]" id="a-type-single" value="single" {{ $aType === 'single' ? 'checked' : '' }}>
                    <label for="a-type-single">شهر واحد</label>
                    <input type="radio" name="a[type]" id="a-type-range"  value="range"  {{ $aType === 'range'  ? 'checked' : '' }}>
                    <label for="a-type-range">نطاق شهري</label>
                </div>
            </div>

            {{-- Year (always shown) --}}
            <div class="sel-row">
                <div class="sel-label">السنة</div>
                <select name="a[year]" class="cmp-select">
                    @foreach($availableYears as $yr)
                    <option value="{{ $yr }}" {{ (string)$aYear === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Single month --}}
            <div class="period-section" id="a-single" style="{{ $aType === 'range' ? 'display:none;' : '' }}">
                <div class="sel-row">
                    <div class="sel-label">الشهر</div>
                    <select name="a[month]" class="cmp-select">
                        <option value="">اختر الشهر...</option>
                        @foreach($arabicMonths as $mn => $mLabel)
                        <option value="{{ $mn }}" {{ $aMonth === $mn ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Month range --}}
            <div class="period-section" id="a-range" style="{{ $aType === 'range' ? '' : 'display:none;' }}">
                <div class="sel-row">
                    <div class="sel-label">النطاق الشهري</div>
                    <div class="sel-two-col">
                        <select name="a[from_month]" class="cmp-select">
                            <option value="">من...</option>
                            @foreach($arabicMonths as $mn => $mLabel)
                            <option value="{{ $mn }}" {{ $aFromMonth === $mn ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                        <select name="a[to_month]" class="cmp-select">
                            <option value="">إلى...</option>
                            @foreach($arabicMonths as $mn => $mLabel)
                            <option value="{{ $mn }}" {{ $aToMonth === $mn ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Mini result for A --}}
            @if($groupA)
            <div class="panel-result">
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ number_format($groupA['orders']) }}</div>
                    <div class="panel-result-lbl">طلب</div>
                </div>
                <div class="panel-result-sep"></div>
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ number_format($groupA['combined_profit'], 0) }}</div>
                    <div class="panel-result-lbl">ربح ر.س</div>
                </div>
                <div class="panel-result-sep"></div>
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ number_format($groupA['driver_pay'], 0) }}</div>
                    <div class="panel-result-lbl">رواتب ر.س</div>
                </div>
                <div class="panel-result-sep"></div>
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ $groupA['drivers'] }}</div>
                    <div class="panel-result-lbl">مندوب</div>
                </div>
            </div>
            @endif

        </div>{{-- panel-a --}}

        {{-- VS column --}}
        <div class="vs-col">
            <div class="vs-circle">VS</div>
        </div>

        {{-- ════ PANEL B ════ --}}
        <div class="cmp-panel panel-b">

            <div class="cmp-panel-head">
                <div class="panel-badge">ب</div>
                <div>
                    <div class="panel-head-title">الفترة الثانية</div>
                    <div class="panel-head-sub">المجموعة المقارَنة</div>
                </div>
            </div>

            {{-- Platform --}}
            <div class="sel-row">
                <div class="sel-label">المنصة</div>
                <div class="seg-ctrl">
                    <input type="radio" name="b[platform]" id="b-plat-all" value="all" {{ $bPlatform === 'all' ? 'checked' : '' }}>
                    <label for="b-plat-all">الكل</label>
                    <input type="radio" name="b[platform]" id="b-plat-hs"  value="hs"  {{ $bPlatform === 'hs'  ? 'checked' : '' }}>
                    <label for="b-plat-hs">هنقرستيشن</label>
                    <input type="radio" name="b[platform]" id="b-plat-cz"  value="cz"  {{ $bPlatform === 'cz'  ? 'checked' : '' }}>
                    <label for="b-plat-cz">شيفز</label>
                </div>
            </div>

            {{-- Period type --}}
            <div class="sel-row">
                <div class="sel-label">نوع الفترة</div>
                <div class="seg-ctrl">
                    <input type="radio" name="b[type]" id="b-type-single" value="single" {{ $bType === 'single' ? 'checked' : '' }}>
                    <label for="b-type-single">شهر واحد</label>
                    <input type="radio" name="b[type]" id="b-type-range"  value="range"  {{ $bType === 'range'  ? 'checked' : '' }}>
                    <label for="b-type-range">نطاق شهري</label>
                </div>
            </div>

            {{-- Year (always shown) --}}
            <div class="sel-row">
                <div class="sel-label">السنة</div>
                <select name="b[year]" class="cmp-select">
                    @foreach($availableYears as $yr)
                    <option value="{{ $yr }}" {{ (string)$bYear === (string)$yr ? 'selected' : '' }}>{{ $yr }}</option>
                    @endforeach
                </select>
            </div>

            {{-- Single month --}}
            <div class="period-section" id="b-single" style="{{ $bType === 'range' ? 'display:none;' : '' }}">
                <div class="sel-row">
                    <div class="sel-label">الشهر</div>
                    <select name="b[month]" class="cmp-select">
                        <option value="">اختر الشهر...</option>
                        @foreach($arabicMonths as $mn => $mLabel)
                        <option value="{{ $mn }}" {{ $bMonth === $mn ? 'selected' : '' }}>{{ $mLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            {{-- Month range --}}
            <div class="period-section" id="b-range" style="{{ $bType === 'range' ? '' : 'display:none;' }}">
                <div class="sel-row">
                    <div class="sel-label">النطاق الشهري</div>
                    <div class="sel-two-col">
                        <select name="b[from_month]" class="cmp-select">
                            <option value="">من...</option>
                            @foreach($arabicMonths as $mn => $mLabel)
                            <option value="{{ $mn }}" {{ $bFromMonth === $mn ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                        <select name="b[to_month]" class="cmp-select">
                            <option value="">إلى...</option>
                            @foreach($arabicMonths as $mn => $mLabel)
                            <option value="{{ $mn }}" {{ $bToMonth === $mn ? 'selected' : '' }}>{{ $mLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
            </div>

            {{-- Mini result for B --}}
            @if($groupB)
            <div class="panel-result">
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ number_format($groupB['orders']) }}</div>
                    <div class="panel-result-lbl">طلب</div>
                </div>
                <div class="panel-result-sep"></div>
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ number_format($groupB['combined_profit'], 0) }}</div>
                    <div class="panel-result-lbl">ربح ر.س</div>
                </div>
                <div class="panel-result-sep"></div>
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ number_format($groupB['driver_pay'], 0) }}</div>
                    <div class="panel-result-lbl">رواتب ر.س</div>
                </div>
                <div class="panel-result-sep"></div>
                <div class="panel-result-stat">
                    <div class="panel-result-val">{{ $groupB['drivers'] }}</div>
                    <div class="panel-result-lbl">مندوب</div>
                </div>
            </div>
            @endif

        </div>{{-- panel-b --}}

    </div>{{-- panel-grid --}}

    {{-- Footer with compare button --}}
    <div class="sel-footer">
        <button type="submit" class="cmp-btn">⚖️ قارن الآن</button>
        <span class="sel-hint">اختر المنصة والفترة لكل مجموعة ثم اضغط مقارنة</span>
    </div>

</div>{{-- sel-wrapper --}}
</form>

{{-- ═══════════════ RESULTS ═══════════════ --}}
@if($groupA && $groupB)

{{-- Group summary strips --}}
<div class="grp-strips">
    <div class="grp-strip grp-strip-a">
        <div class="grp-strip-label">
            <span style="font-size:16px;margin-left:4px;">🔵</span>
            {{ $groupA['label'] }}
        </div>
        <div class="grp-stat">
            <div class="grp-stat-val text-a">{{ number_format($groupA['orders']) }}</div>
            <div class="grp-stat-lbl">طلب</div>
        </div>
        <div class="grp-stat">
            <div class="grp-stat-val text-a">{{ number_format($groupA['combined_profit'], 0) }}</div>
            <div class="grp-stat-lbl">ربح ر.س</div>
        </div>
        <div class="grp-stat">
            <div class="grp-stat-val text-a">{{ number_format($groupA['driver_pay'], 0) }}</div>
            <div class="grp-stat-lbl">رواتب ر.س</div>
        </div>
    </div>

    <div class="vs-divider">مقابل</div>

    <div class="grp-strip grp-strip-b">
        <div class="grp-strip-label">
            <span style="font-size:16px;margin-left:4px;">🟡</span>
            {{ $groupB['label'] }}
        </div>
        <div class="grp-stat">
            <div class="grp-stat-val text-b">{{ number_format($groupB['orders']) }}</div>
            <div class="grp-stat-lbl">طلب</div>
        </div>
        <div class="grp-stat">
            <div class="grp-stat-val text-b">{{ number_format($groupB['combined_profit'], 0) }}</div>
            <div class="grp-stat-lbl">ربح ر.س</div>
        </div>
        <div class="grp-stat">
            <div class="grp-stat-val text-b">{{ number_format($groupB['driver_pay'], 0) }}</div>
            <div class="grp-stat-lbl">رواتب ر.س</div>
        </div>
    </div>
</div>

{{-- KPI metric cards --}}
@php
$metricDefs = [
    ['key'=>'orders',              'label'=>'الطلبات',                'icon'=>'🛒','color'=>'#6366f1','fmt'=>'int'],
    ['key'=>'revenue',             'label'=>'إيراد الشركة',           'icon'=>'💰','color'=>'#0284c7','fmt'=>'sar'],
    ['key'=>'combined_profit',     'label'=>'ربح الشركة',             'icon'=>'📈','color'=>'#16a34a','fmt'=>'sar'],
    ['key'=>'driver_pay',          'label'=>'رواتب المناديب',         'icon'=>'💵','color'=>'#7c3aed','fmt'=>'sar'],
    ['key'=>'platform_deductions', 'label'=>'خصومات المنصة',          'icon'=>'📉','color'=>'#dc2626','fmt'=>'sar'],
    ['key'=>'compensations',       'label'=>'تعويضات شيفز',           'icon'=>'🔄','color'=>'#0891b2','fmt'=>'sar'],
    ['key'=>'benefits',            'label'=>'المزايا (سكن + شركة)',   'icon'=>'🎁','color'=>'#d97706','fmt'=>'sar'],
    ['key'=>'manual_deductions',   'label'=>'الخصومات اليدوية',       'icon'=>'⚠️','color'=>'#be185d','fmt'=>'sar'],
    ['key'=>'avg_salary',          'label'=>'متوسط الراتب',           'icon'=>'📊','color'=>'#475569','fmt'=>'sar'],
    ['key'=>'avg_orders',          'label'=>'متوسط الطلبات/مندوب',   'icon'=>'📦','color'=>'#0d9488','fmt'=>'dec'],
];
$fmtVal   = fn($v,$fmt) => match($fmt){ 'int'=>number_format((int)$v), 'sar'=>number_format((float)$v,2), 'dec'=>number_format((float)$v,1), default=>$v };
$fmtDelta = fn($v,$fmt) => ($v >= 0 ? '+' : '') . match($fmt){ 'int'=>number_format((int)$v), 'sar'=>number_format((float)$v,2), 'dec'=>number_format((float)$v,1), default=>$v };
@endphp

<div class="metric-grid">
@foreach($metricDefs as $md)
@php
    $d     = $diff[$md['key']] ?? null;
    $va    = $d ? $d['a'] : ($groupA[$md['key']] ?? 0);
    $vb    = $d ? $d['b'] : ($groupB[$md['key']] ?? 0);
    $delta = $d ? $d['delta'] : ($vb - $va);
    $pct   = $d ? $d['pct'] : null;
    $up    = $delta >= 0;
    $deltaClass = $delta == 0 ? 'delta-zero' : ($up ? 'delta-up' : 'delta-down');
    $arrow = $delta == 0 ? '—' : ($up ? '↑' : '↓');
    $suffix = match($md['fmt']) { 'sar' => ' ر.س', default => '' };
@endphp
<div class="metric-card" style="--mc:{{ $md['color'] }};">
    <div class="metric-name">{{ $md['icon'] }} {{ $md['label'] }}</div>
    <div class="metric-row">
        <span class="metric-lbl">
            <span style="width:8px;height:8px;border-radius:50%;background:#1e40af;display:inline-block;"></span>
            أ
        </span>
        <span class="metric-val val-a">{{ $fmtVal($va, $md['fmt']) }}{{ $suffix }}</span>
    </div>
    <div class="metric-row">
        <span class="metric-lbl">
            <span style="width:8px;height:8px;border-radius:50%;background:#b45309;display:inline-block;"></span>
            ب
        </span>
        <span class="metric-val val-b">{{ $fmtVal($vb, $md['fmt']) }}{{ $suffix }}</span>
    </div>
    <div class="metric-row" style="padding-top:6px;margin-top:2px;border-top:1px solid #f1f5f9;border-bottom:none;">
        <span class="metric-lbl" style="font-size:10px;">التغيير</span>
        <span>
            <span class="delta {{ $deltaClass }}">{{ $arrow }} {{ $fmtDelta($delta, $md['fmt']) }}{{ $suffix }}</span>
            @if($pct !== null)
            <span class="pct-badge">{{ $pct }}%</span>
            @endif
        </span>
    </div>
</div>
@endforeach
</div>

{{-- Chart row 1: Main grouped bar + Growth % bar --}}
@php
$kpiLabels  = ['الطلبات','إيراد الشركة','ربح الشركة','رواتب المناديب'];
$kpiA       = [$groupA['orders'],$groupA['revenue'],$groupA['combined_profit'],$groupA['driver_pay']];
$kpiB       = [$groupB['orders'],$groupB['revenue'],$groupB['combined_profit'],$groupB['driver_pay']];

$growthKeys   = ['orders','revenue','combined_profit','driver_pay','platform_deductions','compensations'];
$growthLabels = ['الطلبات','الإيراد','الربح','الرواتب','خصومات المنصة','التعويضات'];
$growthVals   = array_map(fn($k) => $diff[$k]['pct'] ?? 0, $growthKeys);
$growthColors = array_map(fn($v) => $v >= 0 ? 'rgba(22,163,74,.75)' : 'rgba(220,38,38,.75)', $growthVals);
@endphp

<div class="chart-row">
    <div class="chart-card">
        <div class="chart-card-head">
            المؤشرات الرئيسية
            <div class="legend">
                <span><span class="legend-dot" style="background:#1e40af;"></span>أ</span>
                <span><span class="legend-dot" style="background:#b45309;"></span>ب</span>
            </div>
        </div>
        <div class="chart-card-body"><canvas id="chartMain" height="120"></canvas></div>
    </div>

    <div class="chart-card">
        <div class="chart-card-head">نسبة التغيير (ب مقارنةً بـ أ)</div>
        <div class="chart-card-body"><canvas id="chartGrowth" height="120"></canvas></div>
    </div>
</div>

{{-- Chart row 2: platform detail charts --}}
@if($showHs)
@php
$hsLabels = ['الإيراد (HS)','رواتب HS','خصومات المنصة','المزايا'];
$hsA      = [$groupA['hs_revenue'],$groupA['hs_driver_pay'],$groupA['platform_deductions'],$groupA['benefits']];
$hsB      = [$groupB['hs_revenue'],$groupB['hs_driver_pay'],$groupB['platform_deductions'],$groupB['benefits']];
@endphp
@endif

@if($showCz)
@php
$czLabels = ['ربح CZ','رواتب CZ','تعويضات','مكافآت'];
$czA      = [$groupA['cz_profit'],$groupA['cz_driver_pay'],$groupA['compensations'],$groupA['bonuses']];
$czB      = [$groupB['cz_profit'],$groupB['cz_driver_pay'],$groupB['compensations'],$groupB['bonuses']];
@endphp
@endif

@if($showHs && $showCz)
<div class="chart-row">
    <div class="chart-card">
        <div class="chart-card-head" style="border-right:3px solid #c2410c;">
            هنقرستيشن — تفصيل
            <div class="legend"><span><span class="legend-dot" style="background:#1e40af;"></span>أ</span><span><span class="legend-dot" style="background:#b45309;"></span>ب</span></div>
        </div>
        <div class="chart-card-body"><canvas id="chartHs" height="100"></canvas></div>
    </div>
    <div class="chart-card">
        <div class="chart-card-head" style="border-right:3px solid #15803d;">
            شيفز — تفصيل
            <div class="legend"><span><span class="legend-dot" style="background:#1e40af;"></span>أ</span><span><span class="legend-dot" style="background:#b45309;"></span>ب</span></div>
        </div>
        <div class="chart-card-body"><canvas id="chartCz" height="100"></canvas></div>
    </div>
</div>
@elseif($showHs)
<div class="cmp-card">
    <div class="cmp-card-head" style="border-right:3px solid #c2410c;">
        <span class="cmp-card-title">هنقرستيشن — تفصيل</span>
        <div class="legend"><span><span class="legend-dot" style="background:#1e40af;"></span>أ</span><span><span class="legend-dot" style="background:#b45309;"></span>ب</span></div>
    </div>
    <div class="cmp-card-body"><canvas id="chartHs" height="70"></canvas></div>
</div>
@elseif($showCz)
<div class="cmp-card">
    <div class="cmp-card-head" style="border-right:3px solid #15803d;">
        <span class="cmp-card-title">شيفز — تفصيل</span>
        <div class="legend"><span><span class="legend-dot" style="background:#1e40af;"></span>أ</span><span><span class="legend-dot" style="background:#b45309;"></span>ب</span></div>
    </div>
    <div class="cmp-card-body"><canvas id="chartCz" height="70"></canvas></div>
</div>
@endif

@elseif($groupA || $groupB)
{{-- One group only --}}
@php $g = $groupA ?? $groupB; $col = $groupA ? '#1e40af' : '#b45309'; $lbl = $groupA ? 'أ' : 'ب'; @endphp
<div class="cmp-card">
    <div class="cmp-card-body" style="text-align:center;padding:28px;">
        <p style="font-size:13px;color:#64748b;margin:0 0 16px;">
            تم تحديد الفترة {{ $lbl }} فقط — حدد الفترة الأخرى لعرض المقارنة الكاملة.
        </p>
        <div style="display:flex;flex-wrap:wrap;gap:10px;justify-content:center;">
            @foreach([['v'=>number_format($g['orders']),'l'=>'الطلبات'],['v'=>number_format($g['combined_profit'],0).' ر.س','l'=>'الربح'],['v'=>number_format($g['driver_pay'],0).' ر.س','l'=>'الرواتب'],['v'=>number_format($g['avg_salary'],0).' ر.س','l'=>'متوسط الراتب']] as $kpi)
            <div style="background:#f8fafc;border-radius:10px;padding:12px 18px;border-top:3px solid {{ $col }};">
                <div style="font-size:18px;font-weight:800;color:{{ $col }};">{{ $kpi['v'] }}</div>
                <div style="font-size:11px;color:#64748b;">{{ $kpi['l'] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</div>

@else
{{-- Nothing selected --}}
<div class="cmp-card">
    <div class="cmp-card-body">
        <div class="cmp-empty">
            <div class="cmp-empty-icon">⚖️</div>
            <h3>اختر فترتين للمقارنة</h3>
            <p>
                حدد المنصة والشهر (أو النطاق الشهري) لكل فترة، ثم اضغط <strong>قارن الآن</strong>.
                <br><br>
                مثال: هنقرستيشن — مايو مقابل يونيو 2026.
                أو: الكل — الربع الأول مقابل الربع الثاني.
            </p>
        </div>
    </div>
</div>
@endif

</div>{{-- .cmp-body --}}
    </div>{{-- content-body --}}
</div>{{-- content-wrapper --}}
</div>{{-- app-content --}}

{{-- ── Chart JS ── --}}
@if($groupA && $groupB)
<script src="{{ asset('asset/dashboard') }}/vendors/js/charts/chart.min.js"></script>
<script>
(function(){
    var colorA = 'rgba(30,64,175,.8)';
    var colorB = 'rgba(180,83,9,.8)';
    var opts   = {
        responsive:true,
        plugins:{ legend:{ display:false }, tooltip:{ rtl:true } },
        scales:{ y:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.04)' } }, x:{ grid:{ display:false } } }
    };

    function makeBar(id, labels, dataA, dataB) {
        var el = document.getElementById(id);
        if (!el) return;
        new Chart(el.getContext('2d'), {
            type:'bar',
            data:{
                labels: labels,
                datasets:[
                    { label:'أ', data:dataA, backgroundColor:colorA, borderRadius:4 },
                    { label:'ب', data:dataB, backgroundColor:colorB, borderRadius:4 }
                ]
            },
            options: Object.assign({}, opts)
        });
    }

    makeBar('chartMain', @json($kpiLabels), @json($kpiA), @json($kpiB));

    var el2 = document.getElementById('chartGrowth');
    if (el2) {
        new Chart(el2.getContext('2d'), {
            type:'bar',
            data:{
                labels: @json($growthLabels),
                datasets:[{
                    label:'التغيير %',
                    data: @json($growthVals),
                    backgroundColor: @json($growthColors),
                    borderRadius:4, borderSkipped:false
                }]
            },
            options:{
                indexAxis:'y', responsive:true,
                plugins:{ legend:{ display:false }, tooltip:{ callbacks:{ label:function(c){ return ' ' + (c.parsed.x>0?'+':'') + c.parsed.x + '%'; } } } },
                scales:{
                    x:{ grid:{ color:'rgba(0,0,0,.04)' }, ticks:{ callback:function(v){ return v+'%'; } } },
                    y:{ grid:{ display:false }, ticks:{ font:{ size:11 } } }
                }
            }
        });
    }

    @if($showHs)
    makeBar('chartHs', @json($hsLabels ?? []), @json($hsA ?? []), @json($hsB ?? []));
    @endif
    @if($showCz)
    makeBar('chartCz', @json($czLabels ?? []), @json($czA ?? []), @json($czB ?? []));
    @endif
})();
</script>
@endif

<script>
(function(){
    // Toggle single / range sections
    function togglePeriod(prefix) {
        var checked = document.querySelector('input[name="' + prefix + '[type]"]:checked');
        var isRange = checked && checked.value === 'range';
        var single = document.getElementById(prefix + '-single');
        var range  = document.getElementById(prefix + '-range');
        if (single) single.style.display = isRange ? 'none' : 'block';
        if (range)  range.style.display  = isRange ? 'block' : 'none';
    }

    ['a', 'b'].forEach(function(p) {
        document.querySelectorAll('input[name="' + p + '[type]"]').forEach(function(r) {
            r.addEventListener('change', function() { togglePeriod(p); });
        });
        // Initialise from server-rendered state
        togglePeriod(p);
    });
})();
</script>
@endsection
