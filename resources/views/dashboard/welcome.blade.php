@extends('layouts.dashboard.app')
@section('title', 'لوحة التحكم')

@section('content')
<div class="app-content content" dir="rtl">
<div class="content-wrapper">

<style>
/* ── Base ── */
.wel-body { font-family:'Arial','Tahoma',sans-serif; padding:4px 0 28px; }

/* ── Welcome strip ── */
.wel-strip {
    margin-bottom:22px; padding-bottom:14px; border-bottom:1px solid #f1f5f9;
}
.wel-greeting { font-size:20px; font-weight:800; color:#0f172a; margin-bottom:4px; line-height:1.3; }
/* +1px, line-height added — readable subtitle beneath the title */
.wel-subtitle { font-size:13px; color:#94a3b8; font-weight:600; line-height:1.5; }

/* ── KPI grid — 4 per row, 3 rows ── */
.kpi-grid {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:16px;
    margin-bottom:24px;
}
@media(max-width:900px) { .kpi-grid { grid-template-columns:repeat(3,1fr); } }
@media(max-width:600px) { .kpi-grid { grid-template-columns:repeat(2,1fr); } }

.kpi-card {
    background:#fff;
    border-radius:10px;
    padding:20px 18px 16px;
    box-shadow:0 1px 3px rgba(0,0,0,.06),0 2px 8px rgba(0,0,0,.04);
    border-top:3px solid var(--kc,#6366f1);
    display:flex; flex-direction:column; gap:6px;
}
.kpi-icon  { font-size:22px; line-height:1; margin-bottom:4px; }
.kpi-val   {
    font-size:30px; font-weight:900; color:#000;
    font-variant-numeric:tabular-nums; line-height:1.1;
}
.kpi-val-sm { font-size:26px; }
.kpi-label { font-size:13px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.4px; line-height:1.4; }
.kpi-sub   { font-size:13px; color:#64748b; font-weight:600; margin-top:1px; line-height:1.4; }

/* ── Main lower grid ── */
.wel-main {
    display:grid;
    grid-template-columns:1fr 1.1fr 1fr;
    gap:14px;
    margin-bottom:14px;
    align-items:start;
}
@media(max-width:900px){ .wel-main { grid-template-columns:1fr 1fr; } }
@media(max-width:600px){ .wel-main { grid-template-columns:1fr; } }

/* ── Generic card ── */
.wel-card {
    background:#fff;
    border-radius:10px;
    box-shadow:0 1px 3px rgba(0,0,0,.06),0 2px 8px rgba(0,0,0,.04);
    overflow:hidden;
}
/* +2px padding on header for breathing room */
.wel-card-head {
    padding:13px 16px;
    border-bottom:1px solid #f1f5f9;
    display:flex; align-items:center; gap:8px;
}
.wel-card-title { font-size:14px; font-weight:800; color:#1e293b; flex:1; }
.wel-card-badge {
    font-size:12px; font-weight:800; padding:3px 11px;
    border-radius:10px; color:#fff;
}
.badge-hs { background:#c2410c; }
.badge-cz { background:#15803d; }
.badge-all{ background:#1e40af; }
.wel-card-body { padding:14px 16px; }

/* ── Ranking table ── */
.rank-table { width:100%; border-collapse:collapse; }
.rank-table th {
    font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase;
    letter-spacing:.4px; padding:6px 8px 7px; border-bottom:1px solid #e2e8f0;
    text-align:right; line-height:1.3;
}
.rank-table td {
    font-size:14px; padding:9px 8px; border-bottom:1px solid #f1f5f9;
    color:#111; font-weight:600; vertical-align:middle; line-height:1.4;
}
.rank-table tr:last-child td { border-bottom:none; }
.rank-num {
    width:24px; height:24px; border-radius:50%; background:#f1f5f9;
    display:inline-flex; align-items:center; justify-content:center;
    font-size:12px; font-weight:800; color:#475569; flex-shrink:0;
    margin-left:4px;
}
.rank-num-1 { background:#fbbf24; color:#78350f; }
.rank-num-2 { background:#94a3b8; color:#fff; }
.rank-num-3 { background:#d97706; color:#fff; }
.rank-name  { font-size:14px; font-weight:700; color:#111; }
.rank-val   {
    font-size:14px; font-weight:800; font-variant-numeric:tabular-nums;
    text-align:left; white-space:nowrap;
}
.rank-val-hs { color:#c2410c; }
.rank-val-cz { color:#15803d; }

/* ── Expense list ── */
.exp-list { list-style:none; padding:0; margin:0; }
.exp-group-head {
    font-size:12px; font-weight:800; color:#64748b; text-transform:uppercase;
    letter-spacing:.5px; padding:11px 2px 6px; margin-top:4px;
    border-bottom:1px solid #e2e8f0; line-height:1.3;
}
.exp-group-head:first-child { margin-top:0; padding-top:3px; }
.exp-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:9px 2px; border-bottom:1px solid #f1f5f9; gap:8px;
}
.exp-row:last-child { border-bottom:none; }
.exp-icon  { font-size:15px; flex-shrink:0; }
.exp-label { font-size:14px; font-weight:700; color:#1e293b; flex:1; line-height:1.4; }
.exp-amount {
    font-size:14px; font-weight:800; font-variant-numeric:tabular-nums;
    color:#000; text-align:left; white-space:nowrap;
}
.exp-amount.zero { color:#cbd5e1; }
.exp-total-row {
    display:flex; align-items:center; justify-content:space-between;
    padding:12px 2px 3px; margin-top:5px; border-top:2px solid #e2e8f0;
}
.exp-total-label  { font-size:14px; font-weight:800; color:#1e293b; }
.exp-total-amount { font-size:16px; font-weight:900; color:#1e40af; font-variant-numeric:tabular-nums; }

/* ── Vehicle stat rows ── */
.veh-grid { display:grid; grid-template-columns:1fr 1fr; gap:1px; background:#f1f5f9; }
.veh-stat {
    background:#fff; padding:16px 18px; display:flex; flex-direction:column; gap:4px;
}
.veh-stat-val  { font-size:22px; font-weight:900; color:#000; font-variant-numeric:tabular-nums; line-height:1.2; }
.veh-stat-lbl  { font-size:13px; font-weight:700; color:#475569; text-transform:uppercase; letter-spacing:.4px; line-height:1.4; margin-top:2px; }
.veh-tag {
    display:inline-block; font-size:12px; font-weight:800;
    padding:3px 10px; border-radius:6px; margin-top:4px;
}
.tag-warn { background:#fef3c7; color:#92400e; }
.tag-ok   { background:#dcfce7; color:#15803d; }

/* ── Bottom vehicle card ── */
.wel-bottom { margin-bottom:0; }
</style>

    {{-- Breadcrumb --}}
    <div class="content-header row">
        <div class="mb-2 content-header-left col-12 breadcrumb-new">
            <ol class="breadcrumb">
                <li class="breadcrumb-item active">الرئيسية</li>
            </ol>
        </div>
    </div>

    <div class="content-body">
<div class="wel-body">

{{-- ── Welcome strip ────────────────────────────────────────────── --}}
<div class="wel-strip">
    <div class="wel-greeting">لوحة التحكم التنفيذية</div>
    <div class="wel-subtitle">ملخص تنفيذي لأداء الشركة</div>
</div>

{{-- ══════════════════════════════════════════════
     KPI CARDS
     ══════════════════════════════════════════════ --}}
<div class="kpi-grid">

    <div class="kpi-card" style="--kc:#3b82f6;">
        <div class="kpi-icon">👥</div>
        <div class="kpi-val">{{ number_format($totalDelegates) }}</div>
        <div class="kpi-label">عدد المناديب</div>
        <div class="kpi-sub">{{ $activeDriverCount }} لديهم تسويات</div>
    </div>

    <div class="kpi-card" style="--kc:#475569;">
        <div class="kpi-icon">🚗</div>
        <div class="kpi-val">{{ number_format($totalVehicles) }}</div>
        <div class="kpi-label">عدد المركبات</div>
        <div class="kpi-sub">المسجلة في النظام</div>
    </div>

    <div class="kpi-card" style="--kc:#6366f1;">
        <div class="kpi-icon">📦</div>
        <div class="kpi-val">{{ number_format($totalOrders) }}</div>
        <div class="kpi-label">إجمالي الطلبات</div>
        <div class="kpi-sub">HS: {{ number_format($hsOrders) }} &nbsp;|&nbsp; CZ: {{ number_format($czOrders) }}</div>
    </div>

    <div class="kpi-card" style="--kc:#c2410c;">
        <div class="kpi-icon">🟠</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($hsProfit, 0) }}</div>
        <div class="kpi-label">ربح هنقرستيشن (ر.س)</div>
        <div class="kpi-sub">إيراد: {{ number_format($hsProfit + $expCompany, 0) }}</div>
    </div>

    <div class="kpi-card" style="--kc:#15803d;">
        <div class="kpi-icon">🟢</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($czProfit, 0) }}</div>
        <div class="kpi-label">ربح شيفز (ر.س)</div>
        <div class="kpi-sub">{{ number_format($czOrders) }} طلب</div>
    </div>

    <div class="kpi-card" style="--kc:#059669;">
        <div class="kpi-icon">📈</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($totalProfit, 0) }}</div>
        <div class="kpi-label">إجمالي الربح (ر.س)</div>
        <div class="kpi-sub">HS + CZ مجتمعَين</div>
    </div>

    <div class="kpi-card" style="--kc:#7c3aed;">
        <div class="kpi-icon">💡</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($avgProfitPerDriver, 0) }}</div>
        <div class="kpi-label">متوسط الربح/مندوب (ر.س)</div>
        <div class="kpi-sub">{{ $activeDriverCount }} مندوب نشط</div>
    </div>

    <div class="kpi-card" style="--kc:#dc2626;">
        <div class="kpi-icon">⚠️</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($totalViolationsAmt, 0) }}</div>
        <div class="kpi-label">إجمالي المخالفات (ر.س)</div>
        <div class="kpi-sub">مركبات + مناديب</div>
    </div>

    <div class="kpi-card" style="--kc:#d97706;">
        <div class="kpi-icon">🔧</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($totalMaintenanceAmt, 0) }}</div>
        <div class="kpi-label">إجمالي الصيانة (ر.س)</div>
        <div class="kpi-sub">تكاليف صيانة المركبات</div>
    </div>

    <div class="kpi-card" style="--kc:#0284c7;">
        <div class="kpi-icon">⛽</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($totalFuelAmt, 0) }}</div>
        <div class="kpi-label">إجمالي الوقود (ر.س)</div>
        <div class="kpi-sub">قيد الإضافات</div>
    </div>

    <div class="kpi-card" style="--kc:#6d28d9;">
        <div class="kpi-icon">💵</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($expSalaries, 0) }}</div>
        <div class="kpi-label">إجمالي الرواتب (ر.س)</div>
        <div class="kpi-sub">HS + CZ معاً</div>
    </div>

    <div class="kpi-card" style="--kc:#0891b2;">
        <div class="kpi-icon">💳</div>
        <div class="kpi-val kpi-val-sm">{{ number_format($expAdvances, 0) }}</div>
        <div class="kpi-label">إجمالي السلف (ر.س)</div>
        <div class="kpi-sub">سلف المناديب</div>
    </div>

</div>{{-- .kpi-grid --}}

{{-- ══════════════════════════════════════════════
     LOWER: Rankings | Expenses | Rankings
     ══════════════════════════════════════════════ --}}
<div class="wel-main">

    {{-- Top HS Drivers --}}
    <div class="wel-card">
        <div class="wel-card-head">
            <span style="font-size:15px;">🟠</span>
            <span class="wel-card-title">أفضل 5 مناديب — هنقرستيشن</span>
            <span class="wel-card-badge badge-hs">HS</span>
        </div>
        <div class="wel-card-body" style="padding:8px 14px 12px;">
            @if($topHs->isEmpty())
            <p style="font-size:12px;color:#94a3b8;text-align:center;padding:16px 0;margin:0;">لا توجد بيانات</p>
            @else
            <table class="rank-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th style="text-align:left;">الطلبات</th>
                        <th style="text-align:left;">الإيراد (ر.س)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topHs as $i => $row)
                    <tr>
                        <td><span class="rank-num rank-num-{{ $i+1 <= 3 ? $i+1 : 0 }}">{{ $i+1 }}</span></td>
                        <td class="rank-name">{{ $row->name }}</td>
                        <td class="rank-val">{{ number_format($row->orders) }}</td>
                        <td class="rank-val rank-val-hs">{{ number_format($row->revenue, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Expense Summary --}}
    <div class="wel-card">
        <div class="wel-card-head">
            <span style="font-size:15px;">📊</span>
            <span class="wel-card-title">ملخص المصروفات</span>
            <span class="wel-card-badge badge-all">إجمالي</span>
        </div>
        <div class="wel-card-body">
            @php
            $grp1 = [
                ['icon'=>'⛽','label'=>'الوقود',         'val'=>$expFuel],
                ['icon'=>'🔧','label'=>'الصيانة',        'val'=>$expMaintenance],
                ['icon'=>'⚠️','label'=>'المخالفات',      'val'=>$expViolations],
                ['icon'=>'🏢','label'=>'مصروفات الشركة', 'val'=>$expCompany],
            ];
            $grp2 = [
                ['icon'=>'💵','label'=>'الرواتب',        'val'=>$expSalaries],
                ['icon'=>'💳','label'=>'السلف',           'val'=>$expAdvances],
                ['icon'=>'🏠','label'=>'بدل السكن',      'val'=>$expHousing],
                ['icon'=>'🎁','label'=>'المنح والمزايا', 'val'=>$expBenefits],
            ];
            @endphp
            <ul class="exp-list">
                <li class="exp-group-head">مصروفات تخص الشركة</li>
                @foreach($grp1 as $e)
                <li class="exp-row">
                    <span class="exp-icon">{{ $e['icon'] }}</span>
                    <span class="exp-label">{{ $e['label'] }}</span>
                    <span class="exp-amount {{ $e['val'] == 0 ? 'zero' : '' }}">
                        {{ $e['val'] == 0 ? '—' : number_format($e['val'], 2) . ' ر.س' }}
                    </span>
                </li>
                @endforeach
                <li class="exp-group-head">مستحقات المناديب</li>
                @foreach($grp2 as $e)
                <li class="exp-row">
                    <span class="exp-icon">{{ $e['icon'] }}</span>
                    <span class="exp-label">{{ $e['label'] }}</span>
                    <span class="exp-amount {{ $e['val'] == 0 ? 'zero' : '' }}">
                        {{ $e['val'] == 0 ? '—' : number_format($e['val'], 2) . ' ر.س' }}
                    </span>
                </li>
                @endforeach
            </ul>
            <div class="exp-total-row">
                <span class="exp-total-label">الإجمالي الكلي</span>
                <span class="exp-total-amount">{{ number_format($totalExpenses, 2) }} ر.س</span>
            </div>
        </div>
    </div>

    {{-- Top Chefz Drivers --}}
    <div class="wel-card">
        <div class="wel-card-head">
            <span style="font-size:15px;">🟢</span>
            <span class="wel-card-title">أفضل 5 مناديب — شيفز</span>
            <span class="wel-card-badge badge-cz">CZ</span>
        </div>
        <div class="wel-card-body" style="padding:8px 14px 12px;">
            @if($topCz->isEmpty())
            <p style="font-size:12px;color:#94a3b8;text-align:center;padding:16px 0;margin:0;">لا توجد بيانات</p>
            @else
            <table class="rank-table">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الاسم</th>
                        <th style="text-align:left;">الطلبات</th>
                        <th style="text-align:left;">الربح (ر.س)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($topCz as $i => $row)
                    <tr>
                        <td><span class="rank-num rank-num-{{ $i+1 <= 3 ? $i+1 : 0 }}">{{ $i+1 }}</span></td>
                        <td class="rank-name">{{ $row->name }}</td>
                        <td class="rank-val">{{ number_format($row->orders) }}</td>
                        <td class="rank-val rank-val-cz">{{ number_format($row->profit, 0) }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

</div>{{-- .wel-main --}}

{{-- ══════════════════════════════════════════════
     VEHICLE SUMMARY
     ══════════════════════════════════════════════ --}}
<div class="wel-card wel-bottom">
    <div class="wel-card-head">
        <span style="font-size:15px;">🚗</span>
        <span class="wel-card-title">ملخص المركبات</span>
    </div>
    <div class="veh-grid">
        <div class="veh-stat">
            <div class="veh-stat-val">{{ number_format($vehiclesActive) }}</div>
            <div class="veh-stat-lbl">عدد المركبات النشطة</div>
        </div>
        <div class="veh-stat">
            <div class="veh-stat-val">{{ number_format($vehiclesWithViolations) }}</div>
            <div class="veh-stat-lbl">مركبات بمخالفات</div>
            @if($vehiclesWithViolations > 0)
            <span class="veh-tag tag-warn">تحتاج مراجعة</span>
            @else
            <span class="veh-tag tag-ok">لا مخالفات</span>
            @endif
        </div>
        <div class="veh-stat">
            <div class="veh-stat-val">{{ number_format($totalVehicleViolAmt, 0) }} <small style="font-size:12px;font-weight:600;color:#94a3b8;">ر.س</small></div>
            <div class="veh-stat-lbl">إجمالي قيمة المخالفات</div>
        </div>
        <div class="veh-stat">
            <div class="veh-stat-val" style="font-size:13px;">
                {{ $topViolationType ? $topViolationType->name : '—' }}
            </div>
            <div class="veh-stat-lbl">أكثر نوع مخالفة تكراراً</div>
            @if($topViolationType)
            <span class="veh-tag tag-warn">{{ $topViolationType->cnt }} مرة</span>
            @endif
        </div>
    </div>
</div>

</div>{{-- .wel-body --}}
    </div>{{-- content-body --}}
</div>{{-- content-wrapper --}}
</div>{{-- app-content --}}
@endsection
