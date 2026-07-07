@extends('layouts.dashboard.app')

@section('title') لوحة التقارير التنفيذية @endsection

@section('content')
@php
    $isAll = $filters['platform'] === 'all';
    $isHs  = in_array($filters['platform'], ['all', 'hungerstation']);
    $isCz  = in_array($filters['platform'], ['all', 'the-chefz']);
    $platLabel = $isAll ? 'جميع المنصات' : ($filters['platform'] === 'hungerstation' ? 'هنقرستيشن' : 'ذا شيفز');
    $platColor = $isAll ? '#475569' : ($filters['platform'] === 'hungerstation' ? '#c2410c' : '#15803d');
    $platBg    = $isAll ? '#f1f5f9' : ($filters['platform'] === 'hungerstation' ? '#fff7ed' : '#f0fdf4');
@endphp
<div class="app-content content exec-dash" dir="rtl">
<div class="content-wrapper" style="padding:0;">

<style>
.exec-dash * { box-sizing:border-box; }
.exec-dash { background:#EEF2F7; min-height:100vh; font-family:'Arial','Tahoma',sans-serif; }

/* ── Header ── */
.exc-header {
    background:linear-gradient(135deg,#0f2444 0%,#1e40af 55%,#2d1b69 100%);
    padding:28px 36px 24px;
    display:flex; align-items:center; justify-content:space-between;
    border-bottom:3px solid rgba(255,255,255,.08);
}
.exc-header-left { display:flex; align-items:center; gap:18px; }
.exc-header-icon {
    width:52px; height:52px; border-radius:14px;
    background:rgba(255,255,255,.15); border:1px solid rgba(255,255,255,.2);
    display:flex; align-items:center; justify-content:center;
    font-size:24px; flex-shrink:0;
}
.exc-header-text h1 {
    font-size:26px; font-weight:800; color:#fff; margin:0 0 5px;
    letter-spacing:-.5px; line-height:1.1;
    text-shadow:0 1px 4px rgba(0,0,0,.25);
}
.exc-header-text .exc-sub {
    display:flex; align-items:center; gap:8px; flex-wrap:wrap;
}
.exc-header-text .exc-sub span { font-size:12px; color:rgba(255,255,255,.7); }
.exc-plat-chip {
    display:inline-flex; align-items:center; gap:4px;
    padding:2px 10px; border-radius:20px; font-size:11px; font-weight:700;
    background:rgba(255,255,255,.2); color:#fff; border:1px solid rgba(255,255,255,.3);
}
.exc-header-actions { display:flex; gap:10px; align-items:center; }
.exc-btn {
    display:inline-flex; align-items:center; gap:6px;
    padding:9px 18px; border-radius:9px; font-size:13px; font-weight:700;
    cursor:pointer; border:none; text-decoration:none; transition:.15s;
}
.exc-btn-primary { background:#fff; color:#1e40af; box-shadow:0 2px 6px rgba(0,0,0,.12); }
.exc-btn-primary:hover { background:#eff6ff; color:#1e40af; text-decoration:none; box-shadow:0 4px 12px rgba(0,0,0,.15); }
.exc-btn-ghost { background:rgba(255,255,255,.12); color:#fff; border:1px solid rgba(255,255,255,.25); }
.exc-btn-ghost:hover { background:rgba(255,255,255,.22); color:#fff; text-decoration:none; }

/* ── Body ── */
.exc-body { padding:24px 28px; }

/* ── Filter card ── */
.exc-filter-card {
    background:#fff; border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
    padding:20px 24px; margin-bottom:20px;
}
.exc-filter-card h6 {
    font-size:11px; font-weight:700; color:#94a3b8; letter-spacing:.8px;
    text-transform:uppercase; margin:0 0 14px;
}
.exc-filter-row { display:flex; flex-wrap:wrap; gap:10px; align-items:flex-end; }
.exc-filter-group { display:flex; flex-direction:column; gap:4px; min-width:140px; }
.exc-filter-group label { font-size:11px; font-weight:600; color:#64748b; }
.exc-filter-input, .exc-filter-select {
    border:1.5px solid #e2e8f0; border-radius:8px; padding:7px 11px;
    font-size:13px; color:#1e293b; background:#fff; height:36px; width:100%; transition:.15s;
}
.exc-filter-input:focus, .exc-filter-select:focus {
    border-color:#3b82f6; outline:none; box-shadow:0 0 0 3px rgba(59,130,246,.1);
}
.exc-filter-apply {
    background:#1e40af; color:#fff; border:none; border-radius:8px;
    padding:7px 20px; font-size:13px; font-weight:700; height:36px;
    cursor:pointer; display:flex; align-items:center; gap:6px; transition:.15s; white-space:nowrap;
}
.exc-filter-apply:hover { background:#1d4ed8; }
.exc-filter-reset {
    background:#f8fafc; color:#475569; border:1.5px solid #e2e8f0; border-radius:8px;
    padding:7px 14px; font-size:13px; height:36px; cursor:pointer; text-decoration:none;
    display:inline-flex; align-items:center;
}
.exc-filter-reset:hover { background:#f1f5f9; color:#334155; }

/* ── Period strip ── */
.exc-period-strip {
    background:#fff; border-radius:10px; border:1.5px solid #e2e8f0;
    padding:10px 18px; display:flex; align-items:center; justify-content:space-between;
    font-size:12px; color:#475569; margin-bottom:20px;
}
.exc-period-strip strong { color:#1e293b; }

/* ── KPI section header ── */
.exc-kpi-section-head {
    font-size:11px; font-weight:700; color:#94a3b8; letter-spacing:.7px;
    text-transform:uppercase; margin:0 0 10px; display:flex; align-items:center; gap:6px;
}
.exc-kpi-section-head::after {
    content:''; flex:1; height:1px; background:#e2e8f0;
}

/* ── KPI Grid ── */
.kpi-grid-exec {
    display:grid; grid-template-columns:repeat(auto-fill,minmax(185px,1fr));
    gap:14px; margin-bottom:20px;
}
.kpi-exec {
    background:#fff; border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
    padding:18px 16px; border-right:4px solid var(--kpi-color,#6366f1);
    display:flex; flex-direction:column; gap:4px; transition:.2s;
}
.kpi-exec:hover { transform:translateY(-2px); box-shadow:0 4px 20px rgba(0,0,0,.09); }
.kpi-exec-icon {
    width:34px; height:34px; border-radius:8px;
    background:var(--kpi-bg,rgba(99,102,241,.1));
    display:flex; align-items:center; justify-content:center;
    font-size:16px; margin-bottom:8px;
}
.kpi-exec-num  { font-size:22px; font-weight:700; color:#0f172a; letter-spacing:-.5px; line-height:1; }
.kpi-exec-lbl  { font-size:11px; color:#64748b; font-weight:500; }
.kpi-exec-sub  { font-size:10.5px; color:#94a3b8; }

/* ── Cards ── */
.exc-card {
    background:#fff; border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04); overflow:hidden;
}
.exc-card-head {
    padding:14px 18px; border-bottom:1px solid #f1f5f9;
    font-size:13px; font-weight:700; color:#1e293b; display:flex; align-items:center; gap:8px;
}
.exc-card-body   { padding:16px 18px; }
.exc-card-body-0 { padding:0; }

/* ── Chart rows ── */
.exc-row { display:grid; gap:16px; margin-bottom:20px; }
.exc-row-8-4  { grid-template-columns:8fr 4fr; }
.exc-row-6-6  { grid-template-columns:1fr 1fr; }
.exc-row-4-4-4{ grid-template-columns:1fr 1fr 1fr; }
.exc-row-full { grid-template-columns:1fr; }

/* ── Top lists ── */
.top-row {
    display:flex; align-items:center; gap:10px;
    padding:8px 18px; border-bottom:1px solid #f8fafc;
}
.top-row:last-child { border-bottom:none; }
.top-rank {
    width:22px; height:22px; border-radius:50%;
    background:#f1f5f9; color:#64748b; font-size:10px; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.top-rank-1 { background:#fef3c7; color:#b45309; }
.top-rank-2 { background:#e5e7eb; color:#374151; }
.top-rank-3 { background:#fde8d8; color:#c2410c; }
.top-name  { flex:1; font-size:12px; font-weight:600; color:#334155; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
.top-bar-wrap { flex:2; height:5px; background:#f1f5f9; border-radius:3px; overflow:hidden; }
.top-bar-fill { height:100%; border-radius:3px; }
.top-val { font-size:11.5px; font-weight:700; min-width:68px; text-align:left; }
.plat-badge { font-size:9.5px; padding:1px 5px; border-radius:4px; font-weight:600; margin-right:4px; flex-shrink:0; }
.plat-hs { background:#fff7ed; color:#c2410c; }
.plat-cz { background:#f0fdf4; color:#15803d; }

/* ── Data table ── */
.exc-table-wrap { overflow-x:auto; }
.exc-table { width:100%; border-collapse:collapse; font-size:12px; white-space:nowrap; }
.exc-table thead th {
    background:#0f2444; color:#fff; padding:10px 14px;
    font-weight:700; text-align:right; white-space:nowrap;
}
.exc-table thead th a { color:#fff; text-decoration:none; display:flex; align-items:center; gap:4px; }
.exc-table thead th a:hover { opacity:.8; }
.exc-table tbody td { padding:8px 14px; border-bottom:1px solid #f1f5f9; color:#334155; }
.exc-table tbody tr:nth-child(even) { background:#f8fafc; }
.exc-table tbody tr:hover { background:#eff6ff; }
.exc-table tfoot td { background:#0f2444; color:#fff; font-weight:700; padding:9px 14px; }
.num-pos { color:#16a34a; font-weight:700; }
.num-neg { color:#dc2626; font-weight:700; }
.num-neu { color:#0284c7; font-weight:700; }
.num-muted { color:#64748b; }

/* ── Pagination ── */
.exc-pagination { display:flex; gap:6px; justify-content:center; padding:16px; }
.exc-pagination a, .exc-pagination span {
    width:32px; height:32px; border-radius:8px;
    display:flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:600; text-decoration:none;
    border:1.5px solid #e2e8f0; color:#475569; background:#fff;
}
.exc-pagination a:hover { background:#eff6ff; border-color:#3b82f6; color:#1e40af; }
.exc-pagination span.active { background:#1e40af; border-color:#1e40af; color:#fff; }

/* ── Empty ── */
.exc-empty { text-align:center; padding:40px 20px; color:#94a3b8; }
.exc-empty i { font-size:36px; display:block; margin-bottom:10px; }

@media (max-width:768px) {
    .exc-row-8-4, .exc-row-6-6, .exc-row-4-4-4 { grid-template-columns:1fr; }
    .exc-body { padding:14px 12px; }
    .exc-header { padding:18px 16px 16px; flex-direction:column; align-items:flex-start; gap:14px; }
    .exc-header-text h1 { font-size:20px; }
}
</style>

{{-- ══ PAGE HEADER ══ --}}
<div class="exc-header">
    <div class="exc-header-left">
        <div class="exc-header-icon">📊</div>
        <div class="exc-header-text">
            <h1>لوحة التقارير التنفيذية</h1>
            <div class="exc-sub">
                <span>{{ $filters['date_from'] }} — {{ $filters['date_to'] }}</span>
                <span class="exc-plat-chip">
                    @if($isAll) 🔀 @elseif($isHs) 🟠 @else 🟢 @endif
                    {{ $platLabel }}
                </span>
                @if($filters['driver_id'])
                    <span class="exc-plat-chip" style="background:rgba(255,255,255,.1);">
                        👤 {{ $filters['driver_id'] }}
                    </span>
                @endif
                @if($filters['region'])
                    <span class="exc-plat-chip" style="background:rgba(255,255,255,.1);">
                        📍 {{ $filters['region'] }}
                    </span>
                @endif
            </div>
        </div>
    </div>
    <div class="exc-header-actions">
        <a href="{{ route('dashboard.reports.executive.pdf', request()->query()) }}"
           target="_blank" class="exc-btn exc-btn-primary">
            <i class="la la-file-pdf-o"></i> تصدير PDF
        </a>
    </div>
</div>

{{-- ══ BODY ══ --}}
<div class="exc-body">

    {{-- ── Filter Card ── --}}
    <div class="exc-filter-card">
        <h6>فلاتر البحث والتصفية</h6>
        <form method="GET" action="{{ route('dashboard.reports.executive') }}" id="filterForm">
            <div class="exc-filter-row">
                <div class="exc-filter-group" style="min-width:130px;">
                    <label>من تاريخ</label>
                    <input type="date" name="date_from" class="exc-filter-input" value="{{ $filters['date_from'] }}">
                </div>
                <div class="exc-filter-group" style="min-width:130px;">
                    <label>إلى تاريخ</label>
                    <input type="date" name="date_to" class="exc-filter-input" value="{{ $filters['date_to'] }}">
                </div>
                <div class="exc-filter-group" style="min-width:140px;">
                    <label>المنصة</label>
                    <select name="platform" class="exc-filter-select">
                        <option value="all"          {{ $filters['platform'] === 'all'          ? 'selected' : '' }}>جميع المنصات</option>
                        <option value="hungerstation" {{ $filters['platform'] === 'hungerstation' ? 'selected' : '' }}>هنقرستيشن</option>
                        <option value="the-chefz"    {{ $filters['platform'] === 'the-chefz'    ? 'selected' : '' }}>ذا شيفز</option>
                    </select>
                </div>
                <div class="exc-filter-group" style="min-width:150px;">
                    <label>رقم المندوب (Driver ID)</label>
                    <input type="text" name="driver_id" class="exc-filter-input"
                           placeholder="أدخل رقم المندوب" value="{{ $filters['driver_id'] }}">
                </div>
                @if($isHs && $regions->isNotEmpty())
                <div class="exc-filter-group" style="min-width:140px;">
                    <label>المنطقة / الفريق</label>
                    <select name="region" class="exc-filter-select">
                        <option value="">جميع المناطق</option>
                        @foreach($regions as $r)
                            <option value="{{ $r }}" {{ $filters['region'] === $r ? 'selected' : '' }}>{{ $r }}</option>
                        @endforeach
                    </select>
                </div>
                @endif
                <div style="display:flex;gap:8px;align-items:flex-end;">
                    <button type="submit" class="exc-filter-apply">
                        <i class="la la-search"></i> تطبيق
                    </button>
                    <a href="{{ route('dashboard.reports.executive') }}" class="exc-filter-reset">
                        <i class="la la-refresh" style="margin-left:4px;"></i> إعادة ضبط
                    </a>
                </div>
            </div>
        </form>
    </div>

    {{-- Active period strip --}}
    <div class="exc-period-strip">
        <span>
            الفترة:
            <strong>{{ $filters['date_from'] }}</strong> إلى <strong>{{ $filters['date_to'] }}</strong>
            · المنصة: <strong>{{ $platLabel }}</strong>
            @if($filters['driver_id']) · مندوب: <strong>{{ $filters['driver_id'] }}</strong> @endif
            @if($filters['region'])    · منطقة: <strong>{{ $filters['region'] }}</strong>   @endif
        </span>
        <span style="color:#94a3b8;font-size:11px;">{{ $periodIds->count() }} فترة شهرية</span>
    </div>

    {{-- ══════════ KPI CARDS ══════════ --}}
    {{-- Common KPIs --}}
    <div class="exc-kpi-section-head">مؤشرات الأداء الرئيسية</div>
    @php
    $kpiCommon = [
        ['icon'=>'🛒','lbl'=>'إجمالي الطلبات',       'val'=>number_format($kpis['totalOrders']),      'sub'=>'طلب توصيل','color'=>'#6366f1','bg'=>'rgba(99,102,241,.1)'],
        ['icon'=>'💰','lbl'=>'رسوم التوصيل',          'val'=>number_format($kpis['grossFees'],0),       'sub'=>'ريال سعودي','color'=>'#0d9488','bg'=>'rgba(13,148,136,.1)'],
        ['icon'=>'👥','lbl'=>'رواتب المناديب',        'val'=>number_format($kpis['totalSalaries'],0),   'sub'=>'ريال','color'=>'#7c3aed','bg'=>'rgba(124,58,237,.1)'],
        ['icon'=>$kpis['netProfit']>=0?'✓':'✗',
         'lbl'=>'صافي الربح',
         'val'=>($kpis['netProfit']>=0?'+':'').number_format($kpis['netProfit'],0),
         'sub'=>$isAll ? 'ربح هنقرستيشن + ربح شيفز' : 'ريال',
         'color'=>$kpis['netProfit']>=0?'#16a34a':'#dc2626',
         'bg'=>$kpis['netProfit']>=0?'rgba(22,163,74,.1)':'rgba(220,38,38,.08)'],
        ['icon'=>'🪪','lbl'=>'عدد المناديب',          'val'=>number_format($kpis['totalDrivers']),      'sub'=>'مندوب','color'=>'#475569','bg'=>'rgba(71,85,105,.1)'],
        ['icon'=>'📊','lbl'=>'متوسط الطلبات/مندوب',  'val'=>$kpis['avgOrders'],                        'sub'=>'طلب','color'=>'#0284c7','bg'=>'rgba(2,132,199,.1)'],
        ['icon'=>'💵','lbl'=>'متوسط الراتب',          'val'=>number_format($kpis['avgSalary'],0),       'sub'=>'ريال','color'=>'#6366f1','bg'=>'rgba(99,102,241,.08)'],
    ];
    @endphp
    <div class="kpi-grid-exec" style="margin-bottom:14px;">
        @foreach($kpiCommon as $k)
        <div class="kpi-exec" style="--kpi-color:{{ $k['color'] }};--kpi-bg:{{ $k['bg'] }};">
            <div class="kpi-exec-icon">{{ $k['icon'] }}</div>
            <div class="kpi-exec-num">{{ $k['val'] }}</div>
            <div class="kpi-exec-lbl">{{ $k['lbl'] }}</div>
            <div class="kpi-exec-sub">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>

    {{-- HungerStation-specific KPIs --}}
    @if($isHs)
    <div class="exc-kpi-section-head" style="color:#c2410c;">هنقرستيشن — مؤشرات المنصة</div>
    @php
    $kpiHs = [
        ['icon'=>'↓','lbl'=>'خصومات المنصة',        'val'=>number_format($kpis['platDed'],0),   'sub'=>'ريال','color'=>'#dc2626','bg'=>'rgba(220,38,38,.08)'],
        ['icon'=>'↑','lbl'=>'تعويضات المنصة',        'val'=>number_format($kpis['platComp'],0),  'sub'=>'ريال','color'=>'#0284c7','bg'=>'rgba(2,132,199,.1)'],
        ['icon'=>'📋','lbl'=>'مصروفات الشركة',        'val'=>number_format($kpis['compExp'],0),   'sub'=>'ريال','color'=>'#d97706','bg'=>'rgba(217,119,6,.1)'],
        ['icon'=>'📉','lbl'=>'خصومات يدوية',         'val'=>number_format($kpis['hsManDed'],0),  'sub'=>'ريال','color'=>'#991b1b','bg'=>'rgba(153,27,27,.07)'],
        ['icon'=>'✓', 'lbl'=>'ربح هنقرستيشن (صافي)', 'val'=>number_format($kpis['hsProfit'],0),  'sub'=>'Basic Payment − المصروفات','color'=>'#16a34a','bg'=>'rgba(22,163,74,.1)'],
    ];
    @endphp
    <div class="kpi-grid-exec" style="margin-bottom:14px;">
        @foreach($kpiHs as $k)
        <div class="kpi-exec" style="--kpi-color:{{ $k['color'] }};--kpi-bg:{{ $k['bg'] }};">
            <div class="kpi-exec-icon">{{ $k['icon'] }}</div>
            <div class="kpi-exec-num">{{ $k['val'] }}</div>
            <div class="kpi-exec-lbl">{{ $k['lbl'] }}</div>
            <div class="kpi-exec-sub">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Chefz-specific KPIs --}}
    @if($isCz)
    <div class="exc-kpi-section-head" style="color:#15803d;">ذا شيفز — مؤشرات المنصة</div>
    @php
    $kpiCz = [
        ['icon'=>'%', 'lbl'=>'ضريبة القيمة المضافة', 'val'=>number_format($kpis['vatTotal'],0),    'sub'=>'ريال','color'=>'#ea580c','bg'=>'rgba(234,88,12,.1)'],
        ['icon'=>'🏢','lbl'=>'حصة الشركة',            'val'=>number_format($kpis['companyShare'],0),'sub'=>'من التسويات المعتمدة','color'=>'#be185d','bg'=>'rgba(190,24,93,.1)'],
        ['icon'=>'✓', 'lbl'=>'ربح شيفز (صافي)',       'val'=>number_format($kpis['czProfit'],0),    'sub'=>'حصة الشركة من التسويات','color'=>'#16a34a','bg'=>'rgba(22,163,74,.1)'],
    ];
    @endphp
    <div class="kpi-grid-exec" style="margin-bottom:14px;">
        @foreach($kpiCz as $k)
        <div class="kpi-exec" style="--kpi-color:{{ $k['color'] }};--kpi-bg:{{ $k['bg'] }};">
            <div class="kpi-exec-icon">{{ $k['icon'] }}</div>
            <div class="kpi-exec-num">{{ $k['val'] }}</div>
            <div class="kpi-exec-lbl">{{ $k['lbl'] }}</div>
            <div class="kpi-exec-sub">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Combined totals row (only when viewing all platforms) --}}
    @if($isAll)
    <div class="exc-kpi-section-head">الإجمالي المجمع — جميع المنصات</div>
    @php
    $kpiCombined = [
        ['icon'=>'💰','lbl'=>'إجمالي الإيراد (الشركة)',  'val'=>number_format($kpis['netRevenue'],0), 'sub'=>'HS Basic Payment + حصة شيفز','color'=>'#0d9488','bg'=>'rgba(13,148,136,.08)'],
        ['icon'=>$kpis['netProfit']>=0?'📈':'📉',
         'lbl'=>'الربح المجمع (الشركة)',
         'val'=>($kpis['netProfit']>=0?'+':'').number_format($kpis['netProfit'],0),
         'sub'=>'ربح هنقرستيشن + ربح شيفز',
         'color'=>$kpis['netProfit']>=0?'#16a34a':'#dc2626',
         'bg'  =>$kpis['netProfit']>=0?'rgba(22,163,74,.1)':'rgba(220,38,38,.08)'],
        ['icon'=>'👥','lbl'=>'إجمالي رواتب المناديب',   'val'=>number_format($kpis['totalSalaries'],0),'sub'=>'صافي ما يستلمه المناديب','color'=>'#7c3aed','bg'=>'rgba(124,58,237,.1)'],
    ];
    @endphp
    <div class="kpi-grid-exec" style="margin-bottom:20px;">
        @foreach($kpiCombined as $k)
        <div class="kpi-exec" style="--kpi-color:{{ $k['color'] }};--kpi-bg:{{ $k['bg'] }};">
            <div class="kpi-exec-icon">{{ $k['icon'] }}</div>
            <div class="kpi-exec-num">{{ $k['val'] }}</div>
            <div class="kpi-exec-lbl">{{ $k['lbl'] }}</div>
            <div class="kpi-exec-sub">{{ $k['sub'] }}</div>
        </div>
        @endforeach
    </div>
    @endif

    {{-- ══════════ CHART ROW 1: Daily Trend (+ Platform Donut if all) ══════════ --}}
    @if(!empty($dailyData['labels']))
    @if($isAll)
    <div class="exc-row exc-row-8-4">
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#6366f1;border-radius:50%;display:inline-block;"></span>
                الاتجاه اليومي — الطلبات والإيرادات
            </div>
            <div class="exc-card-body" style="padding-top:12px;">
                <canvas id="chartDaily" height="200"></canvas>
            </div>
        </div>
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#0d9488;border-radius:50%;display:inline-block;"></span>
                توزيع المنصات — الطلبات
            </div>
            <div class="exc-card-body">
                <canvas id="chartPlatformDonut" height="220"></canvas>
            </div>
        </div>
    </div>
    @else
    <div class="exc-row exc-row-full">
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#6366f1;border-radius:50%;display:inline-block;"></span>
                الاتجاه اليومي — {{ $platLabel }} · الطلبات والإيرادات
            </div>
            <div class="exc-card-body" style="padding-top:12px;">
                <canvas id="chartDaily" height="120"></canvas>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ══════════ CHART ROW 2: Top Drivers ══════════ --}}
    @if(!empty($topByOrders) || !empty($topBySalary))
    <div class="exc-row exc-row-6-6">
        @if(!empty($topByOrders))
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#6366f1;border-radius:50%;display:inline-block;"></span>
                أعلى المناديب — عدد الطلبات
            </div>
            <div class="exc-card-body" style="padding:12px 16px;">
                <canvas id="chartTopOrders" height="240"></canvas>
            </div>
        </div>
        @endif
        @if(!empty($topBySalary))
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#0d9488;border-radius:50%;display:inline-block;"></span>
                أعلى المناديب — صافي الراتب
            </div>
            <div class="exc-card-body" style="padding:12px 16px;">
                <canvas id="chartTopSalary" height="240"></canvas>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════ CHART ROW 3: Monthly + HS-specific charts ══════════ --}}
    @php
    $chartRow3 = [];
    if (!empty($monthlyTrend['labels']))       $chartRow3[] = 'monthly';
    if ($isHs && $deductionsByType->isNotEmpty()) $chartRow3[] = 'ded';
    if ($isHs && $expensesByCategory->isNotEmpty()) $chartRow3[] = 'exp';
    $chartRow3Count = count($chartRow3);
    @endphp
    @if($chartRow3Count > 0)
    <div class="exc-row {{ $chartRow3Count === 1 ? 'exc-row-full' : ($chartRow3Count === 2 ? 'exc-row-6-6' : 'exc-row-4-4-4') }}">
        @if(!empty($monthlyTrend['labels']))
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#7c3aed;border-radius:50%;display:inline-block;"></span>
                الاتجاه الشهري (آخر 12 شهر)
            </div>
            <div class="exc-card-body" style="padding-top:12px;">
                <canvas id="chartMonthly" height="220"></canvas>
            </div>
        </div>
        @endif
        @if($isHs && $deductionsByType->isNotEmpty())
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#dc2626;border-radius:50%;display:inline-block;"></span>
                الخصومات اليدوية — حسب النوع
            </div>
            <div class="exc-card-body">
                <canvas id="chartDedTypes" height="220"></canvas>
            </div>
        </div>
        @endif
        @if($isHs && $expensesByCategory->isNotEmpty())
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#d97706;border-radius:50%;display:inline-block;"></span>
                مصروفات الشركة — حسب الفئة
            </div>
            <div class="exc-card-body">
                <canvas id="chartExpenses" height="220"></canvas>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════ CHART ROW 4: Platform Compare (all only) + Region (HS only) ══════════ --}}
    @php
    $row4items = 0;
    if ($isAll) $row4items++;
    if ($isHs && $regionData->isNotEmpty()) $row4items++;
    @endphp
    @if($row4items > 0)
    <div class="exc-row {{ $row4items === 1 ? 'exc-row-full' : 'exc-row-6-6' }}">
        @if($isAll)
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#0284c7;border-radius:50%;display:inline-block;"></span>
                مقارنة المنصات — الإيراد والرواتب والطلبات
            </div>
            <div class="exc-card-body" style="padding-top:12px;">
                <canvas id="chartPlatformCompare" height="220"></canvas>
            </div>
        </div>
        @endif
        @if($isHs && $regionData->isNotEmpty())
        <div class="exc-card">
            <div class="exc-card-head">
                <span style="width:8px;height:8px;background:#16a34a;border-radius:50%;display:inline-block;"></span>
                الطلبات والإيرادات حسب المنطقة / الفريق
            </div>
            <div class="exc-card-body" style="padding-top:12px;">
                <canvas id="chartRegion" height="220"></canvas>
            </div>
        </div>
        @endif
    </div>
    @endif

    {{-- ══════════ TOP LISTS ══════════ --}}
    <div class="exc-row exc-row-6-6">
        <div class="exc-card">
            <div class="exc-card-head">🥇 أعلى 10 مناديب — عدد الطلبات</div>
            <div class="exc-card-body-0">
                @if(!empty($topByOrders))
                    @php $maxOrd = max(array_column($topByOrders,'orders')) ?: 1; @endphp
                    @foreach($topByOrders as $i => $d)
                    <div class="top-row">
                        <div class="top-rank top-rank-{{ $i < 3 ? $i+1 : 'n' }}">{{ $i+1 }}</div>
                        @if($isAll)
                        <span class="plat-badge {{ $d->platform === 'hungerstation' ? 'plat-hs' : 'plat-cz' }}">
                            {{ $d->platform === 'hungerstation' ? 'HS' : 'CZ' }}
                        </span>
                        @endif
                        <div class="top-name">{{ $d->name }}</div>
                        <div class="top-bar-wrap">
                            <div class="top-bar-fill"
                                 style="width:{{ min(100,round($d->orders/$maxOrd*100)) }}%;
                                        background:linear-gradient(90deg,#6366f1,#8b5cf6);"></div>
                        </div>
                        <div class="top-val" style="color:#6366f1;">{{ number_format($d->orders) }}</div>
                    </div>
                    @endforeach
                @else
                    <div class="exc-empty"><i class="la la-bar-chart"></i> لا توجد بيانات</div>
                @endif
            </div>
        </div>
        <div class="exc-card">
            <div class="exc-card-head">💵 أعلى 10 مناديب — صافي الراتب</div>
            <div class="exc-card-body-0">
                @if(!empty($topBySalary))
                    @php $maxSal = max(array_column($topBySalary,'salary')) ?: 1; @endphp
                    @foreach($topBySalary as $i => $d)
                    <div class="top-row">
                        <div class="top-rank top-rank-{{ $i < 3 ? $i+1 : 'n' }}">{{ $i+1 }}</div>
                        @if($isAll)
                        <span class="plat-badge {{ $d->platform === 'hungerstation' ? 'plat-hs' : 'plat-cz' }}">
                            {{ $d->platform === 'hungerstation' ? 'HS' : 'CZ' }}
                        </span>
                        @endif
                        <div class="top-name">{{ $d->name }}</div>
                        <div class="top-bar-wrap">
                            <div class="top-bar-fill"
                                 style="width:{{ min(100,round($d->salary/$maxSal*100)) }}%;
                                        background:linear-gradient(90deg,#0d9488,#06b6d4);"></div>
                        </div>
                        <div class="top-val" style="color:#0d9488;">{{ number_format($d->salary,0) }} ر</div>
                    </div>
                    @endforeach
                @else
                    <div class="exc-empty"><i class="la la-user"></i> لا توجد بيانات</div>
                @endif
            </div>
        </div>
    </div>

    {{-- ══════════ DETAIL TABLE ══════════ --}}
    <div class="exc-card" style="margin-bottom:24px;">
        <div class="exc-card-head" style="justify-content:space-between;">
            <span>
                <span style="width:8px;height:8px;background:#475569;border-radius:50%;display:inline-block;margin-left:6px;"></span>
                جدول تفصيلي — التسويات · {{ $platLabel }}
            </span>
            <span style="font-size:11px;font-weight:500;color:#64748b;">{{ $table->count() }} سجل</span>
        </div>
        @if($table->isNotEmpty())
        @php
            $perPage     = 25;
            $currentPage = $filters['page'];
            $totalRows   = $table->count();
            $totalPages  = (int) ceil($totalRows / $perPage);
            $pageRows    = $table->slice(($currentPage - 1) * $perPage, $perPage);
        @endphp
        <div class="exc-table-wrap">
            <table class="exc-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'name','page'=>1]) }}">الاسم</a></th>
                        <th>Driver ID</th>
                        @if($isAll)<th>المنصة</th>@endif
                        <th>الفترة</th>
                        @if($isHs)<th>المنطقة</th>@endif
                        <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'orders_desc','page'=>1]) }}">الطلبات</a></th>
                        <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'fees_desc','page'=>1]) }}">رسوم التوصيل</a></th>
                        @if($isHs)<th>خصومات المنصة</th>@endif
                        @if($isHs)<th>تعويضات</th>@endif
                        @if($isCz)<th>ضريبة</th>@endif
                        @if($isCz)<th>حصة الشركة</th>@endif
                        <th><a href="{{ request()->fullUrlWithQuery(['sort'=>'salary_desc','page'=>1]) }}">صافي الراتب</a></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($pageRows as $i => $row)
                    <tr>
                        <td class="num-muted">{{ ($currentPage-1)*$perPage + $i + 1 }}</td>
                        <td><strong>{{ $row->name }}</strong></td>
                        <td class="num-muted" style="font-family:monospace;font-size:11px;">{{ $row->driver_id ?: '—' }}</td>
                        @if($isAll)
                        <td>
                            <span class="plat-badge {{ $row->platform === 'hungerstation' ? 'plat-hs' : 'plat-cz' }}">
                                {{ $row->platform === 'hungerstation' ? 'هنقرستيشن' : 'شيفز' }}
                            </span>
                        </td>
                        @endif
                        <td>{{ $row->period }}</td>
                        @if($isHs)<td>{{ $row->region ?: '—' }}</td>@endif
                        <td class="num-neu" style="text-align:center;">{{ number_format($row->orders) }}</td>
                        <td>{{ number_format($row->fees,2) }}</td>
                        @if($isHs)
                        <td>
                            @if($row->deductions > 0)
                                <span class="num-neg">{{ number_format($row->deductions,2) }}</span>
                            @else —
                            @endif
                        </td>
                        <td>
                            @if($row->comps > 0)
                                <span class="num-pos">{{ number_format($row->comps,2) }}</span>
                            @else —
                            @endif
                        </td>
                        @endif
                        @if($isCz)
                        <td>
                            @if($row->vat > 0)
                                <span class="num-neg">{{ number_format($row->vat,2) }}</span>
                            @else —
                            @endif
                        </td>
                        <td>
                            @if($row->company_share > 0)
                                <span class="num-pos">{{ number_format($row->company_share,2) }}</span>
                            @else —
                            @endif
                        </td>
                        @endif
                        <td class="{{ $row->salary >= 0 ? 'num-pos' : 'num-neg' }}">
                            {{ number_format($row->salary,2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="{{ 4 + ($isAll?1:0) + ($isHs?1:0) }}">إجمالي الصفحة الحالية</td>
                        <td style="text-align:center;">{{ number_format($pageRows->sum('orders')) }}</td>
                        <td>{{ number_format($pageRows->sum('fees'),2) }}</td>
                        @if($isHs)
                        <td>{{ number_format($pageRows->sum('deductions'),2) }}</td>
                        <td>{{ number_format($pageRows->sum('comps'),2) }}</td>
                        @endif
                        @if($isCz)
                        <td>{{ number_format($pageRows->sum('vat'),2) }}</td>
                        <td>{{ number_format($pageRows->sum('company_share'),2) }}</td>
                        @endif
                        <td>{{ number_format($pageRows->sum('salary'),2) }} ر.س</td>
                    </tr>
                </tfoot>
            </table>
        </div>

        {{-- Pagination --}}
        @if($totalPages > 1)
        <div class="exc-pagination">
            @if($currentPage > 1)
                <a href="{{ request()->fullUrlWithQuery(['page'=>$currentPage-1]) }}">‹</a>
            @endif
            @for($p = max(1,$currentPage-2); $p <= min($totalPages,$currentPage+2); $p++)
                @if($p === $currentPage)
                    <span class="active">{{ $p }}</span>
                @else
                    <a href="{{ request()->fullUrlWithQuery(['page'=>$p]) }}">{{ $p }}</a>
                @endif
            @endfor
            @if($currentPage < $totalPages)
                <a href="{{ request()->fullUrlWithQuery(['page'=>$currentPage+1]) }}">›</a>
            @endif
        </div>
        @endif

        @else
        <div class="exc-empty" style="padding:60px 20px;">
            <i class="la la-table"></i>
            <p style="margin:0;font-size:14px;">لا توجد بيانات لهذه الفترة أو الفلاتر المحددة</p>
            <p style="margin:8px 0 0;font-size:12px;color:#cbd5e1;">جرب تغيير نطاق التواريخ أو المنصة</p>
        </div>
        @endif
    </div>

</div>{{-- .exc-body --}}
</div>{{-- .content-wrapper --}}
</div>{{-- .app-content --}}
@endsection

@section('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script>
(function () {
    Chart.defaults.font.family = "'Arial','Tahoma',sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#64748b';
    Chart.defaults.plugins.legend.labels.usePointStyle = true;

    var MULTI = ['#6366f1','#0d9488','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#16a34a','#ea580c','#ec4899','#64748b','#06b6d4','#84cc16'];
    var isAll = {{ $isAll ? 'true' : 'false' }};
    var isHs  = {{ $isHs  ? 'true' : 'false' }};
    var isCz  = {{ $isCz  ? 'true' : 'false' }};

    function chartOpts(overrides) {
        return Object.assign({
            responsive: true, maintainAspectRatio: true,
            plugins: { legend: { position:'bottom', labels:{ padding:12, font:{size:11} } }, tooltip:{ rtl:true } },
        }, overrides || {});
    }

    /* ── Daily Trend ── */
    @if(!empty($dailyData['labels']))
    (function(){
        var dd = @json($dailyData);
        var datasets = [];
        if (isHs || isAll) {
            datasets.push({ label:'هنقرستيشن — طلبات', data:dd.hsOrders, backgroundColor:'rgba(249,115,22,.65)', borderRadius:3, order:1, yAxisID:'yO' });
        }
        if (isCz || isAll) {
            datasets.push({ label:'شيفز — طلبات', data:dd.czOrders, backgroundColor:'rgba(22,163,74,.65)', borderRadius:3, order:1, yAxisID:'yO' });
        }
        var totalFees = dd.hsFees.map(function(v,i){ return v + dd.czFees[i]; });
        datasets.push({ label:'الإيراد الإجمالي', data:totalFees, type:'line', borderColor:'#f59e0b', backgroundColor:'transparent', pointRadius:0, tension:.4, order:0, yAxisID:'yF' });
        new Chart(document.getElementById('chartDaily'), {
            type:'bar',
            data:{ labels:dd.labels, datasets:datasets },
            options: chartOpts({
                interaction:{mode:'index',intersect:false},
                plugins:{ legend:{ position:'top', labels:{ font:{size:10}, padding:8 } } },
                scales:{
                    x:{ grid:{color:'rgba(0,0,0,.03)'}, ticks:{font:{size:9},maxRotation:45} },
                    yO:{ type:'linear', position:'right', beginAtZero:true, grid:{drawOnChartArea:false}, title:{display:true,text:'طلبات',font:{size:9}} },
                    yF:{ type:'linear', position:'left',  beginAtZero:true, grid:{color:'rgba(0,0,0,.03)'}, title:{display:true,text:'ريال',font:{size:9}} },
                }
            })
        });
    })();

    /* ── Platform Donut (all platforms only) ── */
    @if($isAll)
    (function(){
        var pc = @json($platformCompare);
        new Chart(document.getElementById('chartPlatformDonut'), {
            type:'doughnut',
            data:{
                labels:['هنقرستيشن','شيفز'],
                datasets:[{ data:[pc.hs.orders,pc.cz.orders], backgroundColor:['#f97316','#16a34a'], borderWidth:2, borderColor:'#fff', hoverOffset:4 }]
            },
            options: chartOpts({ cutout:'60%' })
        });
    })();
    @endif
    @endif

    /* ── Top by Orders ── */
    @if(!empty($topByOrders))
    (function(){
        var topOrd = @json(collect($topByOrders)->take(10)->values());
        new Chart(document.getElementById('chartTopOrders'), {
            type:'bar',
            data:{
                labels:topOrd.map(function(d){ return d.name.length>12?d.name.substr(0,12)+'…':d.name; }),
                datasets:[{
                    label:'الطلبات', data:topOrd.map(function(d){ return d.orders; }),
                    backgroundColor:topOrd.map(function(d){ return d.platform==='hungerstation'?'rgba(249,115,22,.7)':'rgba(22,163,74,.7)'; }),
                    borderRadius:4
                }]
            },
            options: chartOpts({
                indexAxis:'y',
                plugins:{ legend:{display:false} },
                scales:{ x:{beginAtZero:true,grid:{color:'rgba(0,0,0,.03)'}}, y:{grid:{display:false},ticks:{font:{size:10}}} }
            })
        });
    })();
    @endif

    /* ── Top by Salary ── */
    @if(!empty($topBySalary))
    (function(){
        var topSal = @json(collect($topBySalary)->take(10)->values());
        new Chart(document.getElementById('chartTopSalary'), {
            type:'bar',
            data:{
                labels:topSal.map(function(d){ return d.name.length>12?d.name.substr(0,12)+'…':d.name; }),
                datasets:[{
                    label:'الراتب', data:topSal.map(function(d){ return d.salary; }),
                    backgroundColor:topSal.map(function(d){ return d.platform==='hungerstation'?'rgba(99,102,241,.7)':'rgba(13,148,136,.7)'; }),
                    borderRadius:4
                }]
            },
            options: chartOpts({
                indexAxis:'y',
                plugins:{ legend:{display:false} },
                scales:{ x:{beginAtZero:true,grid:{color:'rgba(0,0,0,.03)'}}, y:{grid:{display:false},ticks:{font:{size:10}}} }
            })
        });
    })();
    @endif

    /* ── Monthly Trend ── */
    @if(!empty($monthlyTrend['labels']))
    (function(){
        var mt = @json($monthlyTrend);
        new Chart(document.getElementById('chartMonthly'), {
            type:'line',
            data:{
                labels:mt.labels,
                datasets:[
                    { label:'الإيراد',  data:mt.revenue,  borderColor:'#0d9488', backgroundColor:'rgba(13,148,136,.08)', fill:true, tension:.4, pointRadius:3 },
                    { label:'الرواتب',  data:mt.salaries, borderColor:'#6366f1', backgroundColor:'transparent', tension:.4, pointRadius:2 },
                    { label:'الربح',    data:mt.profit,   borderColor:'#f59e0b', backgroundColor:'transparent', tension:.4, borderDash:[4,3], pointRadius:2 },
                ]
            },
            options: chartOpts({
                interaction:{mode:'index',intersect:false},
                plugins:{ legend:{ position:'top', labels:{ font:{size:10}, padding:6 } } },
                scales:{ x:{ grid:{color:'rgba(0,0,0,.03)'}, ticks:{font:{size:9}} }, y:{ beginAtZero:true, grid:{color:'rgba(0,0,0,.03)'} } }
            })
        });
    })();
    @endif

    /* ── Deductions by Type (HS only) ── */
    @if($isHs && $deductionsByType->isNotEmpty())
    (function(){
        var dt = @json($deductionsByType->values());
        new Chart(document.getElementById('chartDedTypes'), {
            type:'doughnut',
            data:{ labels:dt.map(function(d){return d.label;}), datasets:[{ data:dt.map(function(d){return d.value;}), backgroundColor:MULTI, borderWidth:2, borderColor:'#fff' }] },
            options: chartOpts({ cutout:'55%' })
        });
    })();
    @endif

    /* ── Expenses by Category (HS only) ── */
    @if($isHs && $expensesByCategory->isNotEmpty())
    (function(){
        var ec = @json($expensesByCategory->values());
        new Chart(document.getElementById('chartExpenses'), {
            type:'doughnut',
            data:{ labels:ec.map(function(d){return d.label;}), datasets:[{ data:ec.map(function(d){return d.value;}), backgroundColor:['#d97706','#f59e0b','#fcd34d','#fde68a','#84cc16','#64748b'], borderWidth:2, borderColor:'#fff' }] },
            options: chartOpts({ cutout:'55%' })
        });
    })();
    @endif

    /* ── Platform Compare (all platforms only) ── */
    @if($isAll)
    (function(){
        var pc2 = @json($platformCompare);
        new Chart(document.getElementById('chartPlatformCompare'), {
            type:'bar',
            data:{
                labels:['الطلبات','الإيراد (ريال)','الرواتب (ريال)'],
                datasets:[
                    { label:'هنقرستيشن', data:[pc2.hs.orders,pc2.hs.revenue,pc2.hs.salary], backgroundColor:'rgba(249,115,22,.7)', borderRadius:4 },
                    { label:'شيفز',       data:[pc2.cz.orders,pc2.cz.revenue,pc2.cz.salary], backgroundColor:'rgba(22,163,74,.7)',  borderRadius:4 },
                ]
            },
            options: chartOpts({
                interaction:{mode:'index',intersect:false},
                plugins:{ legend:{ position:'top', labels:{ font:{size:10}, padding:8 } } },
                scales:{ x:{ grid:{color:'rgba(0,0,0,.03)'} }, y:{ beginAtZero:true, grid:{color:'rgba(0,0,0,.03)'} } }
            })
        });
    })();
    @endif

    /* ── Region (HS only) ── */
    @if($isHs && $regionData->isNotEmpty())
    (function(){
        var rd = @json($regionData);
        new Chart(document.getElementById('chartRegion'), {
            type:'bar',
            data:{
                labels:rd.map(function(d){return d.region;}),
                datasets:[
                    { label:'الطلبات', data:rd.map(function(d){return d.orders;}),  backgroundColor:'rgba(22,163,74,.65)',  borderRadius:4, yAxisID:'yO' },
                    { label:'الإيراد', data:rd.map(function(d){return d.revenue;}), type:'line', borderColor:'#3b82f6', backgroundColor:'transparent', tension:.4, pointRadius:3, yAxisID:'yR' },
                ]
            },
            options: chartOpts({
                interaction:{mode:'index',intersect:false},
                plugins:{ legend:{ position:'top', labels:{ font:{size:10}, padding:8 } } },
                scales:{
                    x:{ grid:{color:'rgba(0,0,0,.03)'}, ticks:{font:{size:9}} },
                    yO:{ type:'linear', position:'right', beginAtZero:true, grid:{drawOnChartArea:false} },
                    yR:{ type:'linear', position:'left',  beginAtZero:true, grid:{color:'rgba(0,0,0,.03)'} },
                }
            })
        });
    })();
    @endif

})();
</script>
@endsection
