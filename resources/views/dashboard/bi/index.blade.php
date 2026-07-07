@extends('layouts.dashboard.app')
@section('title') الأعمال — لوحة الذكاء التجاري @endsection

@section('content')
@php
$hsColor  = '#c2410c';
$czColor  = '#15803d';
$profitColor = '#16a34a';
@endphp
<div class="app-content content" dir="rtl">
<div class="content-wrapper">
<div class="bi-wrap">

{{-- ── Styles ── --}}
<style>
.bi-wrap { background:#EEF2F7; min-height:100vh; font-family:'Arial','Tahoma',sans-serif; }

/* Header */
.bi-header {
    background:linear-gradient(135deg,#0f2444 0%,#1e3a8a 60%,#1e1b4b 100%);
    padding:28px 36px 0; color:#fff;
}
.bi-header-top { display:flex; align-items:center; justify-content:space-between; margin-bottom:22px; flex-wrap:wrap; gap:12px; }
.bi-header-icon { width:56px; height:56px; border-radius:14px; background:rgba(255,255,255,.15); display:flex; align-items:center; justify-content:center; font-size:28px; }
.bi-header-title h1 { font-size:28px; font-weight:800; margin:0 0 5px; }
.bi-header-title p  { font-size:13px; opacity:.7; margin:0; }

/* Tab bar */
.bi-tabs { display:flex; gap:0; border-bottom:none; }
.bi-tab {
    padding:12px 20px; font-size:13px; font-weight:700; color:rgba(255,255,255,.6);
    cursor:pointer; border-bottom:3px solid transparent; transition:.15s; white-space:nowrap;
    background:none; border-top:none; border-left:none; border-right:none;
}
.bi-tab:hover { color:rgba(255,255,255,.9); }
.bi-tab.active { color:#fff; border-bottom-color:#60a5fa; }

/* Body */
.bi-body { padding:28px 32px; }

/* Sections */
.bi-section { display:none; }
.bi-section.active { display:block; }

/* KPI cards */
.bi-kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(195px,1fr)); gap:16px; margin-bottom:24px; }
.bi-kpi {
    background:#fff; border-radius:14px; padding:24px 20px;
    box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 18px rgba(0,0,0,.06);
    border-top:4px solid var(--kpi-accent,#6366f1);
    transition:transform .15s;
}
.bi-kpi:hover { transform:translateY(-2px); }
.bi-kpi-icon { font-size:26px; margin-bottom:12px; }
.bi-kpi-val  { font-size:30px; font-weight:800; color:#0f172a; line-height:1; margin-bottom:6px; font-variant-numeric:tabular-nums; }
.bi-kpi-lbl  { font-size:13px; font-weight:700; color:#374151; }
.bi-kpi-sub  { font-size:12px; color:#64748b; margin-top:4px; }

/* Platform comparison row */
.bi-platform-row { display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:22px; }
@media(max-width:768px){ .bi-platform-row { grid-template-columns:1fr; } }

/* Cards */
.bi-card {
    background:#fff; border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,.07),0 4px 18px rgba(0,0,0,.06);
    overflow:hidden; margin-bottom:22px;
}
.bi-card-head {
    padding:16px 20px; display:flex; align-items:center; justify-content:space-between;
    border-bottom:1px solid #e2e8f0; font-size:15px; font-weight:700; color:#0f172a;
}
.bi-card-body { padding:20px; }

/* Tables */
.bi-table { width:100%; border-collapse:collapse; font-size:14px; }
.bi-table th { background:#f8fafc; padding:11px 14px; text-align:right; font-weight:800; color:#374151; font-size:12px; text-transform:uppercase; letter-spacing:.5px; border-bottom:2px solid #e2e8f0; }
.bi-table td { padding:12px 14px; border-bottom:1px solid #f1f5f9; color:#1e293b; }
.bi-table tr:last-child td { border-bottom:none; }
.bi-table tr:hover td { background:#f8fafc; }
.num { font-variant-numeric:tabular-nums; text-align:left; font-weight:600; }
.rank { width:36px; font-weight:800; color:#94a3b8; }

/* Badges */
.plat-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700; }
.plat-hs { background:#fff7ed; color:#c2410c; }
.plat-cz { background:#f0fdf4; color:#15803d; }

/* Progress bar */
.bi-bar-wrap { background:#f1f5f9; border-radius:6px; height:8px; overflow:hidden; margin-top:4px; }
.bi-bar-fill  { height:100%; border-radius:6px; background:var(--bar-color,#6366f1); transition:width .6s; }

/* Stat row */
.bi-stat-row { display:flex; align-items:center; justify-content:space-between; padding:12px 0; border-bottom:1px solid #f1f5f9; }
.bi-stat-row:last-child { border-bottom:none; }
.bi-stat-lbl { font-size:14px; color:#374151; }
.bi-stat-val { font-size:15px; font-weight:700; color:#0f172a; font-variant-numeric:tabular-nums; }

/* Insight cards */
.bi-insight-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(270px,1fr)); gap:16px; }
.bi-insight {
    background:#fff; border-radius:12px; padding:18px 20px;
    box-shadow:0 1px 3px rgba(0,0,0,.07); display:flex; align-items:flex-start; gap:14px;
    border-right:4px solid var(--ins-color);
}
.bi-insight-icon { font-size:26px; flex-shrink:0; }
.bi-insight-title { font-size:13px; font-weight:700; color:#475569; margin-bottom:5px; }
.bi-insight-body  { font-size:15px; font-weight:700; color:#0f172a; }

/* Empty state */
.bi-empty { text-align:center; padding:48px 24px; color:#94a3b8; }
.bi-empty-icon { font-size:40px; margin-bottom:12px; }
.bi-empty p { font-size:14px; margin:0; }

/* Chart container */
.bi-chart { position:relative; }

/* Growth indicators */
.grow-pos { color:#16a34a; font-weight:700; }
.grow-neg { color:#dc2626; font-weight:700; }
.grow-neu { color:#64748b; }
.bi-empty p { font-size:15px; }
</style>

{{-- ── Header ── --}}
<div class="bi-header">
    <div class="bi-header-top">
        <div style="display:flex;align-items:center;gap:14px;">
            <div class="bi-header-icon">🏢</div>
            <div class="bi-header-title">
                <h1>الأعمال</h1>
                <p>لوحة الذكاء التجاري — بيانات من التسويات المعتمدة فقط</p>
            </div>
        </div>
        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span style="background:rgba(255,255,255,.1);padding:6px 14px;border-radius:8px;font-size:12px;">
                📋 {{ $periods->count() }} فترة
            </span>
            <span style="background:rgba(255,255,255,.1);padding:6px 14px;border-radius:8px;font-size:12px;">
                👥 {{ $overview['totalDrivers'] }} مندوب
            </span>
        </div>
    </div>

    {{-- Tab bar --}}
    <div class="bi-tabs">
        <button class="bi-tab active" data-tab="overview">🏠 نظرة عامة</button>
        <button class="bi-tab" data-tab="platforms">📊 المنصات</button>
        <button class="bi-tab" data-tab="drivers">👥 المناديب</button>
        <button class="bi-tab" data-tab="costs">💸 التكاليف</button>
        <button class="bi-tab" data-tab="benefits">🎁 المزايا</button>
        <button class="bi-tab" data-tab="violations">🚦 المخالفات</button>
        <button class="bi-tab" data-tab="fuel">⛽ الوقود</button>
        <button class="bi-tab" data-tab="trend">📈 الاتجاه</button>
        <button class="bi-tab" data-tab="insights">💡 التحليل</button>
    </div>
</div>

<div class="bi-body">

{{-- ══════════════════════════════════════════════════
     TAB 1: OVERVIEW
══════════════════════════════════════════════════ --}}
<div class="bi-section active" id="tab-overview">

    {{-- Combined KPIs --}}
    <div style="font-size:13px;font-weight:700;color:#475569;letter-spacing:.5px;text-transform:uppercase;margin-bottom:14px;">
        المؤشرات الرئيسية — إجمالي المنصتين
    </div>
    <div class="bi-kpi-grid">
        <div class="bi-kpi" style="--kpi-accent:#0d9488;">
            <div class="bi-kpi-icon">💰</div>
            <div class="bi-kpi-val">{{ number_format($overview['totalRevenue'], 0) }}</div>
            <div class="bi-kpi-lbl">إجمالي إيراد الشركة</div>
            <div class="bi-kpi-sub">ريال سعودي</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#16a34a;">
            <div class="bi-kpi-icon">📈</div>
            <div class="bi-kpi-val">{{ number_format($overview['totalProfit'], 0) }}</div>
            <div class="bi-kpi-lbl">إجمالي ربح الشركة</div>
            <div class="bi-kpi-sub">هنقرستيشن + شيفز</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#7c3aed;">
            <div class="bi-kpi-icon">👥</div>
            <div class="bi-kpi-val">{{ number_format($overview['totalDriverPay'], 0) }}</div>
            <div class="bi-kpi-lbl">إجمالي رواتب المناديب</div>
            <div class="bi-kpi-sub">صافي ما يُصرف للمناديب</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#6366f1;">
            <div class="bi-kpi-icon">🛒</div>
            <div class="bi-kpi-val">{{ number_format($overview['totalOrders']) }}</div>
            <div class="bi-kpi-lbl">إجمالي الطلبات</div>
            <div class="bi-kpi-sub">طلب توصيل</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#0284c7;">
            <div class="bi-kpi-icon">📦</div>
            <div class="bi-kpi-val">{{ number_format($overview['avgProfitPerOrder'], 2) }}</div>
            <div class="bi-kpi-lbl">متوسط الربح / طلب</div>
            <div class="bi-kpi-sub">ريال لكل طلب</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#be185d;">
            <div class="bi-kpi-icon">💵</div>
            <div class="bi-kpi-val">{{ number_format($overview['avgDriverSalary'], 0) }}</div>
            <div class="bi-kpi-lbl">متوسط راتب المندوب</div>
            <div class="bi-kpi-sub">ريال شهرياً</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#475569;">
            <div class="bi-kpi-icon">🪪</div>
            <div class="bi-kpi-val">{{ number_format($overview['totalDrivers']) }}</div>
            <div class="bi-kpi-lbl">إجمالي المناديب</div>
            <div class="bi-kpi-sub">عبر جميع المنصات</div>
        </div>
    </div>

    {{-- Platform split --}}
    <div class="bi-platform-row">
        {{-- HungerStation --}}
        <div class="bi-card">
            <div class="bi-card-head" style="background:#fff7ed;border-right:4px solid #c2410c;">
                <span style="color:#c2410c;font-weight:800;">🟠 هنقرستيشن</span>
                <span style="font-size:12px;color:#94a3b8;">FTR</span>
            </div>
            <div class="bi-card-body">
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">الإيراد (Basic Payment)</span>
                    <span class="bi-stat-val">{{ number_format((float)$overview['hs']->revenue, 2) }} ر.س</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">ربح الشركة</span>
                    <span class="bi-stat-val" style="color:#16a34a;">{{ number_format($overview['hsProfit'], 2) }} ر.س</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">رواتب المناديب</span>
                    <span class="bi-stat-val">{{ number_format((float)$overview['hs']->driver_pay, 2) }} ر.س</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">عدد الطلبات</span>
                    <span class="bi-stat-val">{{ number_format((int)$overview['hs']->orders) }}</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">عدد المناديب</span>
                    <span class="bi-stat-val">{{ (int)$overview['hs']->drivers }}</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">مساهمة في الربح</span>
                    <span class="bi-stat-val">{{ $overview['hsContrib'] }}%</span>
                </div>
                <div class="bi-bar-wrap mt-2"><div class="bi-bar-fill" style="--bar-color:#c2410c;width:{{ $overview['hsContrib'] }}%;"></div></div>
            </div>
        </div>

        {{-- Chefz --}}
        <div class="bi-card">
            <div class="bi-card-head" style="background:#f0fdf4;border-right:4px solid #15803d;">
                <span style="color:#15803d;font-weight:800;">🟢 شيفز</span>
                <span style="font-size:12px;color:#94a3b8;">The Chefz</span>
            </div>
            <div class="bi-card-body">
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">رسوم التوصيل الإجمالية</span>
                    <span class="bi-stat-val">{{ number_format((float)$overview['cz']->gross, 2) }} ر.س</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">حصة الشركة (ربح)</span>
                    <span class="bi-stat-val" style="color:#16a34a;">{{ number_format((float)$overview['cz']->profit, 2) }} ر.س</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">رواتب المناديب</span>
                    <span class="bi-stat-val">{{ number_format((float)$overview['cz']->driver_pay, 2) }} ر.س</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">عدد الطلبات</span>
                    <span class="bi-stat-val">{{ number_format((int)$overview['cz']->orders) }}</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">عدد المناديب</span>
                    <span class="bi-stat-val">{{ (int)$overview['cz']->drivers }}</span>
                </div>
                <div class="bi-stat-row">
                    <span class="bi-stat-lbl">مساهمة في الربح</span>
                    <span class="bi-stat-val">{{ $overview['czContrib'] }}%</span>
                </div>
                <div class="bi-bar-wrap mt-2"><div class="bi-bar-fill" style="--bar-color:#15803d;width:{{ $overview['czContrib'] }}%;"></div></div>
            </div>
        </div>
    </div>

    {{-- Compact profit split bar --}}
    <div class="bi-card">
        <div class="bi-card-head">
            توزيع الربح بين المنصتين
            <span style="font-size:12px;font-weight:600;color:#64748b;">
                إجمالي: {{ number_format($overview['totalProfit'], 2) }} ر.س
            </span>
        </div>
        <div class="bi-card-body" style="padding:14px 18px;">
            <canvas id="chartOverviewBar" height="60"></canvas>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     TAB 2: PLATFORM ANALYTICS
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-platforms">

    <div class="bi-platform-row">
        {{-- HS per-period --}}
        <div class="bi-card">
            <div class="bi-card-head" style="border-right:4px solid #c2410c;">
                <span>🟠 هنقرستيشن — بيانات تفصيلية</span>
            </div>
            <div class="bi-card-body" style="padding:0;">
                @if($platformAnalytics['hsByPeriod']->isEmpty())
                    <div class="bi-empty"><div class="bi-empty-icon">📭</div><p>لا توجد فترات</p></div>
                @else
                <div style="overflow-x:auto;">
                <table class="bi-table">
                    <thead><tr>
                        <th>الفترة</th><th>الطلبات</th><th>الإيراد</th><th>رواتب المناديب</th><th>المناديب</th>
                    </tr></thead>
                    <tbody>
                    @foreach($platformAnalytics['hsByPeriod'] as $row)
                    <tr>
                        <td><strong>{{ $row->label }}</strong></td>
                        <td class="num">{{ number_format($row->orders) }}</td>
                        <td class="num">{{ number_format($row->revenue, 2) }}</td>
                        <td class="num">{{ number_format($row->driver_pay, 2) }}</td>
                        <td class="num">{{ $row->drivers }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot style="background:#fff7ed;">
                        <tr>
                            <td><strong>الإجمالي</strong></td>
                            <td class="num"><strong>{{ number_format($platformAnalytics['hsTotals']['orders']) }}</strong></td>
                            <td class="num"><strong>{{ number_format($platformAnalytics['hsTotals']['revenue'], 2) }}</strong></td>
                            <td class="num"><strong>{{ number_format($platformAnalytics['hsTotals']['driver_pay'], 2) }}</strong></td>
                            <td class="num"><strong>{{ $platformAnalytics['hsTotals']['drivers'] }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
                @endif
            </div>
        </div>

        {{-- Chefz per-period --}}
        <div class="bi-card">
            <div class="bi-card-head" style="border-right:4px solid #15803d;">
                <span>🟢 شيفز — بيانات تفصيلية</span>
            </div>
            <div class="bi-card-body" style="padding:0;">
                @if($platformAnalytics['czByPeriod']->isEmpty())
                    <div class="bi-empty"><div class="bi-empty-icon">📭</div><p>لا توجد فترات</p></div>
                @else
                <div style="overflow-x:auto;">
                <table class="bi-table">
                    <thead><tr>
                        <th>الفترة</th><th>الطلبات</th><th>الإجمالي</th><th>حصة الشركة</th><th>الرواتب</th>
                    </tr></thead>
                    <tbody>
                    @foreach($platformAnalytics['czByPeriod'] as $row)
                    <tr>
                        <td><strong>{{ $row->label }}</strong></td>
                        <td class="num">{{ number_format($row->orders) }}</td>
                        <td class="num">{{ number_format($row->gross, 2) }}</td>
                        <td class="num" style="color:#be185d;">{{ number_format($row->profit, 2) }}</td>
                        <td class="num">{{ number_format($row->driver_pay, 2) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                    <tfoot style="background:#f0fdf4;">
                        <tr>
                            <td><strong>الإجمالي</strong></td>
                            <td class="num"><strong>{{ number_format($platformAnalytics['czTotals']['orders']) }}</strong></td>
                            <td class="num"><strong>{{ number_format($platformAnalytics['czTotals']['gross'], 2) }}</strong></td>
                            <td class="num" style="color:#be185d;"><strong>{{ number_format($platformAnalytics['czTotals']['profit'], 2) }}</strong></td>
                            <td class="num"><strong>{{ number_format($platformAnalytics['czTotals']['driver_pay'], 2) }}</strong></td>
                        </tr>
                    </tfoot>
                </table>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Platform comparison chart (compact) --}}
    <div class="bi-card">
        <div class="bi-card-head">مقارنة المنصتين — الإيراد والرواتب</div>
        <div class="bi-card-body" style="padding:10px 16px;">
            <canvas id="chartPlatformCompare" height="55"></canvas>
        </div>
    </div>

    {{-- Chefz extras --}}
    @if($platformAnalytics['czTotals']['compensations'] > 0 || $platformAnalytics['czTotals']['bonuses'] > 0)
    <div class="bi-card">
        <div class="bi-card-head">شيفز — تفاصيل الإضافات</div>
        <div class="bi-card-body">
            <div class="bi-kpi-grid">
                <div class="bi-kpi" style="--kpi-accent:#0891b2;">
                    <div class="bi-kpi-icon">🔄</div>
                    <div class="bi-kpi-val">{{ number_format($platformAnalytics['czTotals']['compensations'], 2) }}</div>
                    <div class="bi-kpi-lbl">تعويضات المنصة</div>
                    <div class="bi-kpi-sub">ريال — من التسويات</div>
                </div>
                <div class="bi-kpi" style="--kpi-accent:#d97706;">
                    <div class="bi-kpi-icon">🎁</div>
                    <div class="bi-kpi-val">{{ number_format($platformAnalytics['czTotals']['bonuses'], 2) }}</div>
                    <div class="bi-kpi-lbl">المكافآت الصالحة</div>
                    <div class="bi-kpi-sub">ريال — من التسويات</div>
                </div>
                <div class="bi-kpi" style="--kpi-accent:#ea580c;">
                    <div class="bi-kpi-icon">%</div>
                    <div class="bi-kpi-val">{{ number_format($platformAnalytics['czTotals']['vat'], 2) }}</div>
                    <div class="bi-kpi-lbl">ضريبة القيمة المضافة</div>
                    <div class="bi-kpi-sub">ريال — محسوب بالتسويات</div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════
     TAB 3: DRIVER RANKINGS
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-drivers">

    @php
    $rankTabs = [
        'top-salary-hs'   => ['label'=>'🏆 أعلى رواتب (HS)',   'data'=>$driverRankings['hsTopSalary'],   'col'=>'val',   'colLabel'=>'الراتب (ر.س)',    'platform'=>'hs'],
        'top-salary-cz'   => ['label'=>'🏆 أعلى رواتب (شيفز)', 'data'=>$driverRankings['czTopSalary'],   'col'=>'val',   'colLabel'=>'الراتب (ر.س)',    'platform'=>'cz'],
        'top-orders-hs'   => ['label'=>'📦 أكثر طلبات (HS)',   'data'=>$driverRankings['hsTopOrders'],   'col'=>'val',   'colLabel'=>'الطلبات',         'platform'=>'hs'],
        'top-orders-cz'   => ['label'=>'📦 أكثر طلبات (شيفز)', 'data'=>$driverRankings['czTopOrders'],   'col'=>'val',   'colLabel'=>'الطلبات',         'platform'=>'cz'],
        'top-distance'    => ['label'=>'🗺️ أعلى Distance (HS)', 'data'=>$driverRankings['hsTopDistance'], 'col'=>'val',   'colLabel'=>'Distance Pay (ر.س)','platform'=>'hs'],
        'top-comp'        => ['label'=>'🔄 أعلى تعويضات (CZ)', 'data'=>$driverRankings['czTopComp'],     'col'=>'val',   'colLabel'=>'تعويضات (ر.س)',   'platform'=>'cz'],
        'top-bonus'       => ['label'=>'🎁 أعلى مكافآت (CZ)',  'data'=>$driverRankings['czTopBonus'],    'col'=>'val',   'colLabel'=>'مكافآت (ر.س)',    'platform'=>'cz'],
        'top-ded-hs'      => ['label'=>'⚠️ أعلى خصومات (HS)', 'data'=>$driverRankings['hsTopDeductions'],'col'=>'val',  'colLabel'=>'الخصومات (ر.س)', 'platform'=>'hs'],
        'profit-per-order'=> ['label'=>'💡 ربح/طلب (HS)',     'data'=>$driverRankings['hsTopProfitPerOrder'],'col'=>'val','colLabel'=>'ر.س/طلب',       'platform'=>'hs'],
    ];
    $first = true;
    @endphp

    {{-- Sub-tabs --}}
    <div style="display:flex;flex-wrap:wrap;gap:6px;margin-bottom:16px;">
        @foreach($rankTabs as $key => $tab)
        <button class="rank-sub-btn {{ $first ? 'rank-sub-active' : '' }}" data-rank="{{ $key }}"
                style="padding:6px 12px;border:1.5px solid #e2e8f0;border-radius:20px;font-size:12px;font-weight:700;
                       background:{{ $first ? '#1e40af' : '#fff' }};color:{{ $first ? '#fff' : '#475569' }};cursor:pointer;">
            {{ $tab['label'] }}
        </button>
        @php $first = false; @endphp
        @endforeach
    </div>

    @foreach($rankTabs as $key => $tab)
    <div class="rank-panel bi-card" id="rank-{{ $key }}" style="{{ !($key === array_key_first($rankTabs)) ? 'display:none;' : '' }}">
        <div class="bi-card-head">
            {{ $tab['label'] }}
            <span class="plat-badge {{ $tab['platform'] === 'hs' ? 'plat-hs' : 'plat-cz' }}">
                {{ $tab['platform'] === 'hs' ? 'هنقرستيشن' : 'شيفز' }}
            </span>
        </div>
        <div class="bi-card-body" style="padding:0;">
            @if($tab['data']->isEmpty())
                <div class="bi-empty"><div class="bi-empty-icon">👤</div><p>لا توجد بيانات</p></div>
            @else
            @php $max = max(1, (float)$tab['data']->max($tab['col'])); @endphp
            <div style="overflow-x:auto;">
            <table class="bi-table">
                <thead><tr>
                    <th class="rank">#</th>
                    <th>المندوب</th>
                    <th>{{ $tab['colLabel'] }}</th>
                    <th>الطلبات</th>
                    <th style="width:140px;">النسبة</th>
                    <th></th>
                </tr></thead>
                <tbody>
                @foreach($tab['data'] as $i => $d)
                @php $pct = $max > 0 ? round((float)$d->{$tab['col']} / $max * 100) : 0; @endphp
                <tr>
                    <td class="rank">{{ $i+1 }}</td>
                    <td>
                        <a href="{{ route('dashboard.reports.bi.driver', $d->id) }}" style="color:#1e293b;font-weight:600;text-decoration:none;">
                            {{ $d->name }}
                        </a>
                        <br><small style="color:#94a3b8;font-size:10px;">{{ $d->delegate_code ?? '' }}</small>
                    </td>
                    <td class="num" style="color:{{ $tab['platform']==='hs'?'#c2410c':'#15803d' }};font-weight:800;">
                        {{ number_format((float)$d->{$tab['col']}, 2) }}
                    </td>
                    <td class="num">{{ isset($d->orders) ? number_format((int)$d->orders) : '—' }}</td>
                    <td>
                        <div class="bi-bar-wrap"><div class="bi-bar-fill" style="--bar-color:{{ $tab['platform']==='hs'?'#c2410c':'#15803d' }};width:{{ $pct }}%;"></div></div>
                    </td>
                    <td><span style="font-size:11px;color:#94a3b8;">{{ $pct }}%</span></td>
                </tr>
                @endforeach
                </tbody>
            </table>
            </div>
            @endif
        </div>
    </div>
    @endforeach
</div>

{{-- ══════════════════════════════════════════════════
     TAB 4: COST ANALYSIS
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-costs">

    {{-- Compact deductions bar chart --}}
    @if($costAnalysis['hsDedsByType']->isNotEmpty())
    <div class="bi-card" style="margin-bottom:16px;">
        <div class="bi-card-head">
            📊 توزيع الخصومات اليدوية — هنقرستيشن
            <strong style="color:#dc2626;">{{ number_format($costAnalysis['hsDeductionsTotal'], 2) }} ر.س</strong>
        </div>
        <div class="bi-card-body" style="padding:12px 16px;">
            <canvas id="chartDeductionsBar" height="70"></canvas>
        </div>
    </div>
    @endif

    <div class="bi-platform-row">
        {{-- HS manual deductions by type --}}
        <div class="bi-card">
            <div class="bi-card-head" style="border-right:4px solid #dc2626;">
                ⚠️ خصومات هنقرستيشن — تفصيل
                <strong style="color:#dc2626;">{{ number_format($costAnalysis['hsDeductionsTotal'], 2) }} ر.س</strong>
            </div>
            <div class="bi-card-body" style="padding:0;">
                @if($costAnalysis['hsDedsByType']->isEmpty())
                    <div class="bi-empty"><div class="bi-empty-icon">✅</div><p>لا توجد خصومات</p></div>
                @else
                @php $dedMax = max(1, $costAnalysis['hsDedsByType']->max('total')); @endphp
                <table class="bi-table">
                    <thead><tr><th>نوع الخصم</th><th>المبلغ</th><th>العدد</th><th>النسبة</th></tr></thead>
                    <tbody>
                    @foreach($costAnalysis['hsDedsByType'] as $d)
                    @php $pct = $costAnalysis['hsDeductionsTotal'] > 0 ? round($d['total']/$costAnalysis['hsDeductionsTotal']*100,1) : 0; @endphp
                    <tr>
                        <td>{{ $d['label'] }}</td>
                        <td class="num" style="color:#dc2626;">{{ number_format($d['total'], 2) }}</td>
                        <td class="num">{{ $d['count'] }}</td>
                        <td style="min-width:100px;">
                            <div class="bi-bar-wrap"><div class="bi-bar-fill" style="--bar-color:#dc2626;width:{{ $pct }}%;"></div></div>
                            <small style="color:#94a3b8;">{{ $pct }}%</small>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Company expenses --}}
        <div class="bi-card">
            <div class="bi-card-head" style="border-right:4px solid #d97706;">
                📋 مصروفات الشركة — حسب الفئة
                <strong style="color:#d97706;">{{ number_format($costAnalysis['compExpTotal'], 2) }} ر.س</strong>
            </div>
            <div class="bi-card-body" style="padding:0;">
                @if($costAnalysis['compExpByCategory']->isEmpty())
                    <div class="bi-empty"><div class="bi-empty-icon">📭</div><p>لا توجد مصروفات مسجلة</p></div>
                @else
                @php $expMax = max(1, $costAnalysis['compExpByCategory']->max('total')); @endphp
                <table class="bi-table">
                    <thead><tr><th>الفئة</th><th>المبلغ</th><th>العدد</th><th>النسبة</th></tr></thead>
                    <tbody>
                    @foreach($costAnalysis['compExpByCategory'] as $e)
                    @php $pct = $costAnalysis['compExpTotal'] > 0 ? round($e['total']/$costAnalysis['compExpTotal']*100,1) : 0; @endphp
                    <tr>
                        <td>{{ $e['label'] }}</td>
                        <td class="num" style="color:#d97706;">{{ number_format($e['total'], 2) }}</td>
                        <td class="num">{{ $e['count'] }}</td>
                        <td style="min-width:100px;">
                            <div class="bi-bar-wrap"><div class="bi-bar-fill" style="--bar-color:#d97706;width:{{ $pct }}%;"></div></div>
                            <small style="color:#94a3b8;">{{ $pct }}%</small>
                        </td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>

    {{-- Chefz deductions --}}
    <div class="bi-card">
        <div class="bi-card-head" style="border-right:4px solid #be185d;">
            ⚠️ خصومات شيفز — من التسويات المعتمدة
        </div>
        <div class="bi-card-body">
            <div class="bi-kpi-grid">
                <div class="bi-kpi" style="--kpi-accent:#be185d;">
                    <div class="bi-kpi-icon">📉</div>
                    <div class="bi-kpi-val">{{ number_format((float)($costAnalysis['czDeds']->total ?? 0), 2) }}</div>
                    <div class="bi-kpi-lbl">خصومات المنصة</div>
                    <div class="bi-kpi-sub">من عمود platform_deductions</div>
                </div>
                <div class="bi-kpi" style="--kpi-accent:#991b1b;">
                    <div class="bi-kpi-icon">📋</div>
                    <div class="bi-kpi-val">{{ number_format((float)($costAnalysis['czDeds']->grand_total ?? 0), 2) }}</div>
                    <div class="bi-kpi-lbl">إجمالي الخصومات</div>
                    <div class="bi-kpi-sub">منصة + يدوية</div>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     TAB 5: BENEFITS
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-benefits">

    <div class="bi-kpi-grid" style="margin-bottom:20px;">
        <div class="bi-kpi" style="--kpi-accent:#6366f1;">
            <div class="bi-kpi-icon">🏠</div>
            <div class="bi-kpi-val">{{ number_format((float)$benefitsAnalysis['hsBenefits']->housing, 2) }}</div>
            <div class="bi-kpi-lbl">بدل السكن (HS)</div>
            <div class="bi-kpi-sub">ريال — من تسويات HS</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#0284c7;">
            <div class="bi-kpi-icon">🎯</div>
            <div class="bi-kpi-val">{{ number_format((float)$benefitsAnalysis['hsBenefits']->benefits_total, 2) }}</div>
            <div class="bi-kpi-lbl">مزايا الشركة (HS)</div>
            <div class="bi-kpi-sub">ريال — من تسويات HS</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#0891b2;">
            <div class="bi-kpi-icon">🔄</div>
            <div class="bi-kpi-val">{{ number_format((float)$benefitsAnalysis['czBenefits']->compensations, 2) }}</div>
            <div class="bi-kpi-lbl">تعويضات شيفز</div>
            <div class="bi-kpi-sub">ريال — من تسويات شيفز</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#d97706;">
            <div class="bi-kpi-icon">🎁</div>
            <div class="bi-kpi-val">{{ number_format((float)$benefitsAnalysis['czBenefits']->bonuses, 2) }}</div>
            <div class="bi-kpi-lbl">مكافآت شيفز</div>
            <div class="bi-kpi-sub">ريال — من تسويات شيفز</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#7c3aed;">
            <div class="bi-kpi-icon">💳</div>
            <div class="bi-kpi-val">{{ number_format((float)$benefitsAnalysis['advanceTotal'], 2) }}</div>
            <div class="bi-kpi-lbl">السلف</div>
            <div class="bi-kpi-sub">ريال</div>
        </div>
    </div>

    <div class="bi-platform-row">
        {{-- HS benefits by driver --}}
        <div class="bi-card">
            <div class="bi-card-head" style="border-right:4px solid #c2410c;">أعلى مستفيدين — هنقرستيشن</div>
            <div class="bi-card-body" style="padding:0;">
                @if($benefitsAnalysis['hsBenefitsByDriver']->isEmpty())
                    <div class="bi-empty"><div class="bi-empty-icon">📭</div><p>لا توجد مزايا مسجلة</p></div>
                @else
                <table class="bi-table">
                    <thead><tr><th>المندوب</th><th>السكن</th><th>المزايا</th></tr></thead>
                    <tbody>
                    @foreach($benefitsAnalysis['hsBenefitsByDriver'] as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td class="num">{{ number_format($r->housing, 2) }}</td>
                        <td class="num">{{ number_format($r->benefits, 2) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>

        {{-- Chefz benefits by driver --}}
        <div class="bi-card">
            <div class="bi-card-head" style="border-right:4px solid #15803d;">أعلى مستفيدين — شيفز</div>
            <div class="bi-card-body" style="padding:0;">
                @if($benefitsAnalysis['czBenefitsByDriver']->isEmpty())
                    <div class="bi-empty"><div class="bi-empty-icon">📭</div><p>لا توجد مزايا مسجلة</p></div>
                @else
                <table class="bi-table">
                    <thead><tr><th>المندوب</th><th>التعويضات</th><th>المكافآت</th></tr></thead>
                    <tbody>
                    @foreach($benefitsAnalysis['czBenefitsByDriver'] as $r)
                    <tr>
                        <td>{{ $r->name }}</td>
                        <td class="num">{{ number_format($r->comp, 2) }}</td>
                        <td class="num">{{ number_format($r->bonus, 2) }}</td>
                    </tr>
                    @endforeach
                    </tbody>
                </table>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════
     TAB 6: VIOLATIONS
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-violations">

    @if($benefitsAnalysis['violTotal'] == 0 && $benefitsAnalysis['violMonthly']->isEmpty())
    <div class="bi-card">
        <div class="bi-card-body">
            <div class="bi-empty">
                <div class="bi-empty-icon">✅</div>
                <p>لا توجد مخالفات مسجلة في الفترات المحددة</p>
            </div>
        </div>
    </div>
    @else
    <div class="bi-kpi-grid">
        <div class="bi-kpi" style="--kpi-accent:#dc2626;">
            <div class="bi-kpi-icon">🚦</div>
            <div class="bi-kpi-val">{{ number_format($benefitsAnalysis['violTotal'], 2) }}</div>
            <div class="bi-kpi-lbl">إجمالي تكلفة المخالفات</div>
            <div class="bi-kpi-sub">ريال</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#991b1b;">
            <div class="bi-kpi-icon">📋</div>
            <div class="bi-kpi-val">{{ $benefitsAnalysis['violMonthly']->sum('cnt') }}</div>
            <div class="bi-kpi-lbl">عدد المخالفات</div>
            <div class="bi-kpi-sub">إجمالي</div>
        </div>
        @php $avgViol = $benefitsAnalysis['violMonthly']->sum('cnt') > 0 ? round($benefitsAnalysis['violTotal'] / $benefitsAnalysis['violMonthly']->sum('cnt'), 2) : 0; @endphp
        <div class="bi-kpi" style="--kpi-accent:#ea580c;">
            <div class="bi-kpi-icon">📊</div>
            <div class="bi-kpi-val">{{ number_format($avgViol, 2) }}</div>
            <div class="bi-kpi-lbl">متوسط تكلفة المخالفة</div>
            <div class="bi-kpi-sub">ريال</div>
        </div>
    </div>

    <div class="bi-card">
        <div class="bi-card-head">أعلى المناديب مخالفةً</div>
        <div class="bi-card-body" style="padding:0;">
            @if($benefitsAnalysis['topViolDrivers']->isEmpty())
                <div class="bi-empty"><div class="bi-empty-icon">✅</div><p>لا بيانات</p></div>
            @else
            <table class="bi-table">
                <thead><tr><th>#</th><th>المندوب</th><th>المبلغ</th><th>العدد</th></tr></thead>
                <tbody>
                @foreach($benefitsAnalysis['topViolDrivers'] as $i => $d)
                <tr>
                    <td class="rank">{{ $i+1 }}</td>
                    <td>{{ $d->name }}</td>
                    <td class="num" style="color:#dc2626;">{{ number_format($d->total, 2) }}</td>
                    <td class="num">{{ $d->cnt }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════
     TAB 7: FUEL
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-fuel">

    @if($benefitsAnalysis['fuelTotal'] == 0)
    <div class="bi-card">
        <div class="bi-card-body">
            <div class="bi-empty">
                <div class="bi-empty-icon">⛽</div>
                <p>لا توجد بيانات وقود مسجلة في الفترات المحددة</p>
            </div>
        </div>
    </div>
    @else
    <div class="bi-kpi-grid">
        <div class="bi-kpi" style="--kpi-accent:#0284c7;">
            <div class="bi-kpi-icon">⛽</div>
            <div class="bi-kpi-val">{{ number_format($benefitsAnalysis['fuelTotal'], 2) }}</div>
            <div class="bi-kpi-lbl">إجمالي الوقود</div>
            <div class="bi-kpi-sub">ريال</div>
        </div>
        <div class="bi-kpi" style="--kpi-accent:#0891b2;">
            <div class="bi-kpi-icon">👤</div>
            <div class="bi-kpi-val">{{ $benefitsAnalysis['topFuelDrivers']->count() > 0 ? number_format($benefitsAnalysis['fuelTotal'] / $benefitsAnalysis['topFuelDrivers']->count(), 2) : '0.00' }}</div>
            <div class="bi-kpi-lbl">متوسط الوقود / مندوب</div>
            <div class="bi-kpi-sub">ريال</div>
        </div>
    </div>
    <div class="bi-card">
        <div class="bi-card-head">أعلى مستهلكي الوقود</div>
        <div class="bi-card-body" style="padding:0;">
            <table class="bi-table">
                <thead><tr><th>#</th><th>المندوب</th><th>المبلغ</th></tr></thead>
                <tbody>
                @foreach($benefitsAnalysis['topFuelDrivers'] as $i => $d)
                <tr>
                    <td class="rank">{{ $i+1 }}</td>
                    <td>{{ $d->name }}</td>
                    <td class="num" style="color:#0284c7;">{{ number_format($d->total, 2) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════
     TAB 8: MONTHLY TREND
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-trend">

    @if(empty($monthlyTrend['labels']))
    <div class="bi-card">
        <div class="bi-card-body">
            <div class="bi-empty"><div class="bi-empty-icon">📈</div><p>لا توجد فترات كافية لعرض الاتجاه</p></div>
        </div>
    </div>
    @else
    <div class="bi-card">
        <div class="bi-card-head">اتجاه الإيراد والربح الشهري</div>
        <div class="bi-card-body"><canvas id="chartTrendRevenue" height="120"></canvas></div>
    </div>
    <div class="bi-card">
        <div class="bi-card-head">اتجاه الطلبات الشهري</div>
        <div class="bi-card-body"><canvas id="chartTrendOrders" height="100"></canvas></div>
    </div>
    <div class="bi-card">
        <div class="bi-card-head">جدول الاتجاه الشهري</div>
        <div class="bi-card-body" style="padding:0;overflow-x:auto;">
            <table class="bi-table">
                <thead><tr>
                    <th>الفترة</th>
                    <th>إيراد HS</th>
                    <th>ربح شيفز</th>
                    <th>الربح المجمع</th>
                    <th>رواتب المناديب</th>
                    <th>الطلبات</th>
                </tr></thead>
                <tbody>
                @foreach($monthlyTrend['labels'] as $i => $label)
                @php
                    $prev_profit = $i > 0 ? $monthlyTrend['combinedProfit'][$i-1] : null;
                    $this_profit = $monthlyTrend['combinedProfit'][$i];
                    $growthClass = $prev_profit === null ? '' : ($this_profit >= $prev_profit ? 'grow-pos' : 'grow-neg');
                    $growthArrow = $prev_profit === null ? '' : ($this_profit >= $prev_profit ? '↑' : '↓');
                @endphp
                <tr>
                    <td><strong>{{ $label }}</strong></td>
                    <td class="num">{{ number_format($monthlyTrend['hsRevenue'][$i], 2) }}</td>
                    <td class="num">{{ number_format($monthlyTrend['czProfit'][$i], 2) }}</td>
                    <td class="num {{ $growthClass }}">{{ $growthArrow }} {{ number_format($this_profit, 2) }}</td>
                    <td class="num">{{ number_format($monthlyTrend['driverPay'][$i], 2) }}</td>
                    <td class="num">{{ number_format($monthlyTrend['orders'][$i]) }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════════
     TAB 9: EXECUTIVE INSIGHTS
══════════════════════════════════════════════════ --}}
<div class="bi-section" id="tab-insights">

    <div class="bi-insight-grid">
        @foreach($insights as $ins)
        <div class="bi-insight" style="--ins-color:{{ $ins['color'] }};">
            <div class="bi-insight-icon">{{ $ins['icon'] }}</div>
            <div>
                <div class="bi-insight-title">{{ $ins['title'] }}</div>
                <div class="bi-insight-body">{{ $ins['body'] }}</div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Summary table --}}
    <div class="bi-card" style="margin-top:20px;">
        <div class="bi-card-head">ملخص المؤشرات التنفيذية</div>
        <div class="bi-card-body" style="padding:0;">
            <table class="bi-table">
                <thead><tr><th>المؤشر</th><th>المنصة</th><th>القيمة</th></tr></thead>
                <tbody>
                    <tr>
                        <td>إجمالي إيراد الشركة</td>
                        <td><span class="plat-badge" style="background:#f1f5f9;color:#475569;">الكل</span></td>
                        <td class="num">{{ number_format($overview['totalRevenue'], 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <td>إجمالي الربح</td>
                        <td><span class="plat-badge" style="background:#f1f5f9;color:#475569;">الكل</span></td>
                        <td class="num" style="color:#16a34a;">{{ number_format($overview['totalProfit'], 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <td>ربح هنقرستيشن</td>
                        <td><span class="plat-badge plat-hs">HS</span></td>
                        <td class="num">{{ number_format($overview['hsProfit'], 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <td>ربح شيفز (حصة الشركة)</td>
                        <td><span class="plat-badge plat-cz">CZ</span></td>
                        <td class="num">{{ number_format($overview['czProfit'], 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <td>إجمالي رواتب المناديب</td>
                        <td><span class="plat-badge" style="background:#f1f5f9;color:#475569;">الكل</span></td>
                        <td class="num">{{ number_format($overview['totalDriverPay'], 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <td>إجمالي الطلبات</td>
                        <td><span class="plat-badge" style="background:#f1f5f9;color:#475569;">الكل</span></td>
                        <td class="num">{{ number_format($overview['totalOrders']) }} طلب</td>
                    </tr>
                    <tr>
                        <td>متوسط الربح / طلب</td>
                        <td><span class="plat-badge" style="background:#f1f5f9;color:#475569;">الكل</span></td>
                        <td class="num">{{ number_format($overview['avgProfitPerOrder'], 2) }} ر.س</td>
                    </tr>
                    <tr>
                        <td>متوسط راتب المندوب</td>
                        <td><span class="plat-badge" style="background:#f1f5f9;color:#475569;">الكل</span></td>
                        <td class="num">{{ number_format($overview['avgDriverSalary'], 2) }} ر.س</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div>{{-- .bi-body --}}
</div>{{-- .bi-wrap --}}
</div>{{-- content-wrapper --}}
</div>{{-- app-content --}}

{{-- ── Charts & Interactivity ── --}}
<script src="{{ asset('asset/dashboard') }}/vendors/js/charts/chart.min.js"></script>
<script>
(function(){
    // ── Tab switching ──
    document.querySelectorAll('.bi-tab').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.bi-tab').forEach(function(b){ b.classList.remove('active'); });
            document.querySelectorAll('.bi-section').forEach(function(s){ s.classList.remove('active'); });
            btn.classList.add('active');
            var tab = document.getElementById('tab-' + btn.dataset.tab);
            if(tab) tab.classList.add('active');
        });
    });

    // ── Driver rankings sub-tabs ──
    document.querySelectorAll('.rank-sub-btn').forEach(function(btn){
        btn.addEventListener('click', function(){
            document.querySelectorAll('.rank-sub-btn').forEach(function(b){
                b.style.background='#fff'; b.style.color='#475569'; b.classList.remove('rank-sub-active');
            });
            document.querySelectorAll('.rank-panel').forEach(function(p){ p.style.display='none'; });
            btn.style.background='#1e40af'; btn.style.color='#fff'; btn.classList.add('rank-sub-active');
            var panel = document.getElementById('rank-' + btn.dataset.rank);
            if(panel) panel.style.display='block';
        });
    });

    // ── Overview compact profit split bar ──
    var oBarEl = document.getElementById('chartOverviewBar');
    if(oBarEl) {
        new Chart(oBarEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['هنقرستيشن', 'شيفز'],
                datasets: [{
                    label: 'الربح (ر.س)',
                    data: [{{ $overview['hsProfit'] }}, {{ $overview['czProfit'] }}],
                    backgroundColor: ['rgba(194,65,12,.8)', 'rgba(21,128,61,.8)'],
                    borderRadius: 6,
                    borderSkipped: false
                }]
            },
            options: {
                responsive:true, indexAxis:'y',
                plugins: {
                    legend:{ display:false },
                    tooltip:{ callbacks:{ label: function(c){ return ' ' + c.parsed.x.toLocaleString('en',{minimumFractionDigits:2}) + ' ر.س'; } } }
                },
                scales: { x:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.05)' } }, y:{ grid:{ display:false } } }
            }
        });
    }

    // ── Deductions by type bar ──
    @if($costAnalysis['hsDedsByType']->isNotEmpty())
    var dedBarEl = document.getElementById('chartDeductionsBar');
    if(dedBarEl) {
        new Chart(dedBarEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: @json($costAnalysis['hsDedsByType']->pluck('label')->values()),
                datasets: [{
                    label: 'المبلغ (ر.س)',
                    data: @json($costAnalysis['hsDedsByType']->pluck('total')->values()),
                    backgroundColor: [
                        'rgba(220,38,38,.75)','rgba(234,88,12,.75)','rgba(217,119,6,.75)',
                        'rgba(22,163,74,.75)','rgba(2,132,199,.75)','rgba(99,102,241,.75)',
                        'rgba(190,24,93,.75)','rgba(71,85,105,.75)','rgba(153,27,27,.75)'
                    ],
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                responsive:true,
                plugins: {
                    legend:{ display:false },
                    tooltip:{ callbacks:{ label: function(c){ return ' ' + c.parsed.y.toLocaleString('en',{minimumFractionDigits:2}) + ' ر.س'; } } }
                },
                scales: {
                    x:{ grid:{ display:false }, ticks:{ font:{ size:11 } } },
                    y:{ beginAtZero:true, grid:{ color:'rgba(0,0,0,.05)' } }
                }
            }
        });
    }
    @endif

    // ── Platform Compare Bar ──
    var platEl = document.getElementById('chartPlatformCompare');
    if(platEl) {
        new Chart(platEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: ['الطلبات', 'الإيراد / حصة الشركة', 'رواتب المناديب'],
                datasets: [
                    {
                        label: 'هنقرستيشن',
                        data: [{{ $platformAnalytics['hsTotals']['orders'] }}, {{ $platformAnalytics['hsTotals']['revenue'] }}, {{ $platformAnalytics['hsTotals']['driver_pay'] }}],
                        backgroundColor: 'rgba(194,65,12,.75)',
                        borderRadius: 4
                    },
                    {
                        label: 'شيفز',
                        data: [{{ $platformAnalytics['czTotals']['orders'] }}, {{ $platformAnalytics['czTotals']['profit'] }}, {{ $platformAnalytics['czTotals']['driver_pay'] }}],
                        backgroundColor: 'rgba(21,128,61,.75)',
                        borderRadius: 4
                    }
                ]
            },
            options: {
                responsive:true, indexAxis:'y',
                plugins: { legend: { position:'top' } },
                scales: { x: { beginAtZero:true } }
            }
        });
    }

    // ── Trend: Revenue & Profit ──
    @if(!empty($monthlyTrend['labels']))
    var trendLabels   = @json($monthlyTrend['labels']);
    var trendHsRev    = @json($monthlyTrend['hsRevenue']);
    var trendCzProfit = @json($monthlyTrend['czProfit']);
    var trendProfit   = @json($monthlyTrend['combinedProfit']);
    var trendOrders   = @json($monthlyTrend['orders']);

    var tRevEl = document.getElementById('chartTrendRevenue');
    if(tRevEl) {
        new Chart(tRevEl.getContext('2d'), {
            type: 'line',
            data: {
                labels: trendLabels,
                datasets: [
                    { label:'إيراد HS', data:trendHsRev, borderColor:'#c2410c', backgroundColor:'rgba(194,65,12,.08)', fill:true, tension:.4, pointRadius:4 },
                    { label:'ربح شيفز', data:trendCzProfit, borderColor:'#15803d', backgroundColor:'rgba(21,128,61,.08)', fill:true, tension:.4, pointRadius:4 },
                    { label:'الربح المجمع', data:trendProfit, borderColor:'#1e40af', backgroundColor:'transparent', tension:.4, borderDash:[5,3], pointRadius:3 }
                ]
            },
            options: {
                responsive:true,
                plugins:{ legend:{ position:'top' } },
                scales:{ y:{ beginAtZero:true } }
            }
        });
    }

    var tOrdEl = document.getElementById('chartTrendOrders');
    if(tOrdEl) {
        new Chart(tOrdEl.getContext('2d'), {
            type: 'bar',
            data: {
                labels: trendLabels,
                datasets: [
                    { label:'الطلبات', data:trendOrders, backgroundColor:'rgba(99,102,241,.7)', borderRadius:6 }
                ]
            },
            options: {
                responsive:true,
                plugins:{ legend:{ display:false } },
                scales:{ y:{ beginAtZero:true } }
            }
        });
    }
    @endif

})();
</script>
@endsection
