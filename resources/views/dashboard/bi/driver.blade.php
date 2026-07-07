@extends('layouts.dashboard.app')
@section('title') {{ $delegate->name }} — ملف المندوب @endsection

@section('content')
<div class="bi-wrap" dir="rtl">

<style>
.bi-wrap { background:#EEF2F7; min-height:100vh; font-family:'Arial','Tahoma',sans-serif; }

.drv-header {
    background:linear-gradient(135deg,#0f2444 0%,#1e3a8a 60%,#1e1b4b 100%);
    padding:24px 32px; color:#fff; display:flex; align-items:center; gap:20px; flex-wrap:wrap;
}
.drv-avatar {
    width:64px; height:64px; border-radius:50%; background:rgba(255,255,255,.15);
    display:flex; align-items:center; justify-content:center; font-size:30px; flex-shrink:0;
}
.drv-info h1 { font-size:22px; font-weight:800; margin:0 0 4px; }
.drv-info p  { margin:0; font-size:12px; opacity:.65; }
.drv-badges  { display:flex; gap:8px; flex-wrap:wrap; margin-top:8px; }
.drv-badge   { background:rgba(255,255,255,.15); padding:4px 12px; border-radius:10px; font-size:11px; font-weight:700; }

.bi-body { padding:22px 28px; }

.bi-card {
    background:#fff; border-radius:14px;
    box-shadow:0 1px 3px rgba(0,0,0,.06),0 4px 16px rgba(0,0,0,.04);
    overflow:hidden; margin-bottom:20px;
}
.bi-card-head {
    padding:13px 18px; display:flex; align-items:center; justify-content:space-between;
    border-bottom:1px solid #f1f5f9; font-size:14px; font-weight:700; color:#1e293b;
}
.bi-card-body { padding:18px; }

.bi-kpi-grid { display:grid; grid-template-columns:repeat(auto-fill,minmax(175px,1fr)); gap:14px; margin-bottom:22px; }
.bi-kpi { background:#fff; border-radius:14px; padding:18px 16px; box-shadow:0 1px 3px rgba(0,0,0,.06); border-top:4px solid var(--kpi-accent,#6366f1); }
.bi-kpi-val  { font-size:24px; font-weight:800; color:#1e293b; line-height:1; margin-bottom:4px; font-variant-numeric:tabular-nums; }
.bi-kpi-lbl  { font-size:12px; font-weight:600; color:#64748b; }
.bi-kpi-sub  { font-size:11px; color:#94a3b8; margin-top:3px; }

.bi-table { width:100%; border-collapse:collapse; font-size:13px; }
.bi-table th { background:#f8fafc; padding:9px 12px; text-align:right; font-weight:700; color:#475569; font-size:11px; text-transform:uppercase; letter-spacing:.5px; border-bottom:1px solid #e2e8f0; }
.bi-table td { padding:9px 12px; border-bottom:1px solid #f1f5f9; color:#334155; }
.bi-table tr:last-child td { border-bottom:none; }
.bi-table tr:hover td { background:#f8fafc; }
.num { font-variant-numeric:tabular-nums; text-align:left; font-weight:600; }

.bi-empty { text-align:center; padding:36px 24px; color:#94a3b8; }
.bi-empty-icon { font-size:36px; margin-bottom:10px; }
.bi-empty p { font-size:13px; margin:0; }

.two-col { display:grid; grid-template-columns:1fr 1fr; gap:16px; }
@media(max-width:768px){ .two-col { grid-template-columns:1fr; } }

.back-link {
    display:inline-flex; align-items:center; gap:6px;
    color:rgba(255,255,255,.7); font-size:13px; font-weight:600;
    text-decoration:none; margin-left:auto;
}
.back-link:hover { color:#fff; }

.plat-badge { display:inline-block; padding:2px 8px; border-radius:10px; font-size:11px; font-weight:700; }
.plat-hs { background:#fff7ed; color:#c2410c; }
.plat-cz { background:#f0fdf4; color:#15803d; }
</style>

{{-- Header --}}
<div class="drv-header">
    <div class="drv-avatar">👤</div>
    <div class="drv-info">
        <h1>{{ $delegate->name }}</h1>
        <p>{{ $delegate->delegate_code ?? 'بدون كود' }} &nbsp;|&nbsp; {{ $delegate->phone ?? '' }}</p>
        <div class="drv-badges">
            @if($hsSettlements->isNotEmpty())
                <span class="drv-badge" style="background:rgba(194,65,12,.3);">🟠 هنقرستيشن</span>
            @endif
            @if($czSettlements->isNotEmpty())
                <span class="drv-badge" style="background:rgba(21,128,61,.3);">🟢 شيفز</span>
            @endif
        </div>
    </div>
    <a href="{{ route('dashboard.reports.bi') }}" class="back-link">← العودة للأعمال</a>
</div>

<div class="bi-body">

{{-- Summary KPIs --}}
@php
$hsTotalOrders  = $hsSettlements->sum('total_orders');
$hsTotalRevenue = $hsSettlements->sum('basic_payment');
$hsTotalSalary  = $hsSettlements->sum('net_salary');
$czTotalOrders  = $czSettlements->sum('total_orders');
$czTotalSalary  = $czSettlements->sum('net_salary');
$czTotalShare   = $czSettlements->sum('company_share_amount');
$totalOrders    = $hsTotalOrders + $czTotalOrders;
$totalSalary    = $hsTotalSalary + $czTotalSalary;
$totalHsDeds    = $hsDeductions->sum('amount');
$totalCzDeds    = $czDeductions->sum('amount');
$totalFuel      = $fuelEntries->sum('amount');
$totalViol      = $violEntries->sum('amount');
@endphp
<div class="bi-kpi-grid">
    <div class="bi-kpi" style="--kpi-accent:#6366f1;">
        <div class="bi-kpi-val">{{ number_format($totalOrders) }}</div>
        <div class="bi-kpi-lbl">إجمالي الطلبات</div>
        <div class="bi-kpi-sub">جميع المنصات</div>
    </div>
    <div class="bi-kpi" style="--kpi-accent:#16a34a;">
        <div class="bi-kpi-val">{{ number_format($totalSalary, 2) }}</div>
        <div class="bi-kpi-lbl">إجمالي الراتب</div>
        <div class="bi-kpi-sub">ريال — صافي</div>
    </div>
    @if($hsTotalRevenue > 0)
    <div class="bi-kpi" style="--kpi-accent:#c2410c;">
        <div class="bi-kpi-val">{{ number_format($hsTotalRevenue, 2) }}</div>
        <div class="bi-kpi-lbl">إيراد HS</div>
        <div class="bi-kpi-sub">Basic Payment</div>
    </div>
    @endif
    @if($czTotalShare > 0)
    <div class="bi-kpi" style="--kpi-accent:#15803d;">
        <div class="bi-kpi-val">{{ number_format($czTotalShare, 2) }}</div>
        <div class="bi-kpi-lbl">حصة شيفز</div>
        <div class="bi-kpi-sub">ريال — Company Share</div>
    </div>
    @endif
    @if($totalHsDeds + $totalCzDeds > 0)
    <div class="bi-kpi" style="--kpi-accent:#dc2626;">
        <div class="bi-kpi-val">{{ number_format($totalHsDeds + $totalCzDeds, 2) }}</div>
        <div class="bi-kpi-lbl">إجمالي الخصومات</div>
        <div class="bi-kpi-sub">ريال</div>
    </div>
    @endif
    @if($totalFuel > 0)
    <div class="bi-kpi" style="--kpi-accent:#0284c7;">
        <div class="bi-kpi-val">{{ number_format($totalFuel, 2) }}</div>
        <div class="bi-kpi-lbl">بدل الوقود</div>
        <div class="bi-kpi-sub">ريال</div>
    </div>
    @endif
    @if($totalViol > 0)
    <div class="bi-kpi" style="--kpi-accent:#ea580c;">
        <div class="bi-kpi-val">{{ number_format($totalViol, 2) }}</div>
        <div class="bi-kpi-lbl">المخالفات</div>
        <div class="bi-kpi-sub">ريال</div>
    </div>
    @endif
</div>

{{-- HS settlements --}}
@if($hsSettlements->isNotEmpty())
<div class="bi-card">
    <div class="bi-card-head">
        <span>🟠 تسويات هنقرستيشن</span>
        <span class="plat-badge plat-hs">{{ $hsSettlements->count() }} فترة</span>
    </div>
    <div class="bi-card-body" style="padding:0;overflow-x:auto;">
        <table class="bi-table">
            <thead><tr>
                <th>الفترة</th>
                <th>الطلبات</th>
                <th>Basic Payment</th>
                <th>Distance Pay</th>
                <th>صافي الراتب</th>
                <th>خصومات المنصة</th>
                <th>السكن</th>
                <th>مزايا الشركة</th>
            </tr></thead>
            <tbody>
            @foreach($hsSettlements as $s)
            <tr>
                <td><strong>{{ $s->label }}</strong></td>
                <td class="num">{{ number_format($s->total_orders) }}</td>
                <td class="num">{{ number_format($s->basic_payment, 2) }}</td>
                <td class="num">{{ number_format($s->distance_payment, 2) }}</td>
                <td class="num" style="color:#16a34a;font-weight:800;">{{ number_format($s->net_salary, 2) }}</td>
                <td class="num" style="color:#dc2626;">{{ number_format($s->total_platform_penalties, 2) }}</td>
                <td class="num">{{ number_format($s->housing_allowance, 2) }}</td>
                <td class="num">{{ number_format($s->company_benefits_total, 2) }}</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot style="background:#fff7ed;">
                <tr>
                    <td><strong>الإجمالي</strong></td>
                    <td class="num"><strong>{{ number_format($hsSettlements->sum('total_orders')) }}</strong></td>
                    <td class="num"><strong>{{ number_format($hsSettlements->sum('basic_payment'), 2) }}</strong></td>
                    <td class="num"><strong>{{ number_format($hsSettlements->sum('distance_payment'), 2) }}</strong></td>
                    <td class="num" style="color:#16a34a;"><strong>{{ number_format($hsSettlements->sum('net_salary'), 2) }}</strong></td>
                    <td class="num" style="color:#dc2626;"><strong>{{ number_format($hsSettlements->sum('total_platform_penalties'), 2) }}</strong></td>
                    <td class="num"><strong>{{ number_format($hsSettlements->sum('housing_allowance'), 2) }}</strong></td>
                    <td class="num"><strong>{{ number_format($hsSettlements->sum('company_benefits_total'), 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Chefz settlements --}}
@if($czSettlements->isNotEmpty())
<div class="bi-card">
    <div class="bi-card-head">
        <span>🟢 تسويات شيفز</span>
        <span class="plat-badge plat-cz">{{ $czSettlements->count() }} دفعة</span>
    </div>
    <div class="bi-card-body" style="padding:0;overflow-x:auto;">
        <table class="bi-table">
            <thead><tr>
                <th>الفترة</th>
                <th>دفعة</th>
                <th>الطلبات</th>
                <th>الإجمالي</th>
                <th>ضريبة</th>
                <th>تعويضات</th>
                <th>مكافآت</th>
                <th>حصة الشركة</th>
                <th>صافي الراتب</th>
            </tr></thead>
            <tbody>
            @foreach($czSettlements as $s)
            <tr>
                <td><strong>{{ $s->label }}</strong></td>
                <td class="num">{{ $s->payout_number }}</td>
                <td class="num">{{ number_format($s->total_orders) }}</td>
                <td class="num">{{ number_format($s->gross_delivery_fees, 2) }}</td>
                <td class="num" style="color:#ea580c;">{{ number_format($s->chefz_tax_amount, 2) }}</td>
                <td class="num">{{ number_format($s->platform_compensations, 2) }}</td>
                <td class="num">{{ number_format($s->positive_bonus, 2) }}</td>
                <td class="num" style="color:#be185d;">{{ number_format($s->company_share_amount, 2) }}</td>
                <td class="num" style="color:#16a34a;font-weight:800;">{{ number_format($s->net_salary, 2) }}</td>
            </tr>
            @endforeach
            </tbody>
            <tfoot style="background:#f0fdf4;">
                <tr>
                    <td><strong>الإجمالي</strong></td>
                    <td></td>
                    <td class="num"><strong>{{ number_format($czSettlements->sum('total_orders')) }}</strong></td>
                    <td class="num"><strong>{{ number_format($czSettlements->sum('gross_delivery_fees'), 2) }}</strong></td>
                    <td class="num" style="color:#ea580c;"><strong>{{ number_format($czSettlements->sum('chefz_tax_amount'), 2) }}</strong></td>
                    <td class="num"><strong>{{ number_format($czSettlements->sum('platform_compensations'), 2) }}</strong></td>
                    <td class="num"><strong>{{ number_format($czSettlements->sum('positive_bonus'), 2) }}</strong></td>
                    <td class="num" style="color:#be185d;"><strong>{{ number_format($czSettlements->sum('company_share_amount'), 2) }}</strong></td>
                    <td class="num" style="color:#16a34a;"><strong>{{ number_format($czSettlements->sum('net_salary'), 2) }}</strong></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>
@endif

{{-- Deductions + Fuel + Violations --}}
<div class="two-col">
    {{-- HS Manual deductions --}}
    <div class="bi-card">
        <div class="bi-card-head">
            ⚠️ الخصومات اليدوية — HS
            @if($hsDeductions->isNotEmpty())
                <strong style="color:#dc2626;">{{ number_format($totalHsDeds, 2) }} ر.س</strong>
            @endif
        </div>
        <div class="bi-card-body" style="padding:0;">
            @if($hsDeductions->isEmpty())
                <div class="bi-empty"><div class="bi-empty-icon">✅</div><p>لا توجد خصومات</p></div>
            @else
            <table class="bi-table">
                <thead><tr><th>الفترة</th><th>النوع</th><th>المبلغ</th><th>ملاحظات</th></tr></thead>
                <tbody>
                @foreach($hsDeductions as $d)
                <tr>
                    <td>{{ $d->label }}</td>
                    <td>
                        @if($d->is_benefit)
                            <span style="color:#16a34a;font-size:11px;">مزية — </span>
                        @endif
                        {{ $typeLabels[$d->deduction_type] ?? $d->deduction_type }}
                    </td>
                    <td class="num" style="color:{{ $d->is_benefit ? '#16a34a' : '#dc2626' }};">
                        {{ $d->is_benefit ? '+' : '-' }}{{ number_format($d->amount, 2) }}
                    </td>
                    <td style="font-size:11px;color:#94a3b8;">{{ $d->notes ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Chefz deductions --}}
    <div class="bi-card">
        <div class="bi-card-head">
            ⚠️ الخصومات اليدوية — شيفز
            @if($czDeductions->isNotEmpty())
                <strong style="color:#dc2626;">{{ number_format($totalCzDeds, 2) }} ر.س</strong>
            @endif
        </div>
        <div class="bi-card-body" style="padding:0;">
            @if($czDeductions->isEmpty())
                <div class="bi-empty"><div class="bi-empty-icon">✅</div><p>لا توجد خصومات</p></div>
            @else
            <table class="bi-table">
                <thead><tr><th>النوع</th><th>المبلغ</th><th>ملاحظات</th></tr></thead>
                <tbody>
                @foreach($czDeductions as $d)
                <tr>
                    <td>{{ $d->deduction_type }}</td>
                    <td class="num" style="color:#dc2626;">{{ number_format($d->amount, 2) }}</td>
                    <td style="font-size:11px;color:#94a3b8;">{{ $d->notes ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

<div class="two-col">
    {{-- Fuel --}}
    <div class="bi-card">
        <div class="bi-card-head">
            ⛽ بدل الوقود
            @if($fuelEntries->isNotEmpty())
                <strong style="color:#0284c7;">{{ number_format($totalFuel, 2) }} ر.س</strong>
            @endif
        </div>
        <div class="bi-card-body" style="padding:0;">
            @if($fuelEntries->isEmpty())
                <div class="bi-empty"><div class="bi-empty-icon">⛽</div><p>لا توجد سجلات وقود</p></div>
            @else
            <table class="bi-table">
                <thead><tr><th>الفترة</th><th>المبلغ</th><th>ملاحظات</th></tr></thead>
                <tbody>
                @foreach($fuelEntries as $f)
                <tr>
                    <td>{{ $f->label }}</td>
                    <td class="num" style="color:#0284c7;">{{ number_format($f->amount, 2) }}</td>
                    <td style="font-size:11px;color:#94a3b8;">{{ $f->notes ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>

    {{-- Violations --}}
    <div class="bi-card">
        <div class="bi-card-head">
            🚦 المخالفات
            @if($violEntries->isNotEmpty())
                <strong style="color:#dc2626;">{{ number_format($totalViol, 2) }} ر.س</strong>
            @endif
        </div>
        <div class="bi-card-body" style="padding:0;">
            @if($violEntries->isEmpty())
                <div class="bi-empty"><div class="bi-empty-icon">✅</div><p>لا توجد مخالفات</p></div>
            @else
            <table class="bi-table">
                <thead><tr><th>الفترة</th><th>المبلغ</th><th>ملاحظات</th></tr></thead>
                <tbody>
                @foreach($violEntries as $v)
                <tr>
                    <td>{{ $v->label }}</td>
                    <td class="num" style="color:#dc2626;">{{ number_format($v->amount, 2) }}</td>
                    <td style="font-size:11px;color:#94a3b8;">{{ $v->notes ?? '—' }}</td>
                </tr>
                @endforeach
                </tbody>
            </table>
            @endif
        </div>
    </div>
</div>

{{-- Performance chart (if data available) --}}
@if($hsSettlements->isNotEmpty() || $czSettlements->isNotEmpty())
<div class="bi-card">
    <div class="bi-card-head">الأداء عبر الفترات</div>
    <div class="bi-card-body">
        <canvas id="chartDriverPerf" height="120"></canvas>
    </div>
</div>
<script src="{{ asset('asset/dashboard') }}/vendors/js/charts/chart.min.js"></script>
<script>
(function(){
    @php
    $chartLabels = [];
    $chartOrders = [];
    $chartSalary = [];
    foreach($hsSettlements as $s) {
        $chartLabels[] = $s->label . ' (HS)';
        $chartOrders[] = (int)$s->total_orders;
        $chartSalary[] = round((float)$s->net_salary, 2);
    }
    foreach($czSettlements as $s) {
        $chartLabels[] = $s->label . ' (CZ-' . $s->payout_number . ')';
        $chartOrders[] = (int)$s->total_orders;
        $chartSalary[] = round((float)$s->net_salary, 2);
    }
    @endphp
    var ctx = document.getElementById('chartDriverPerf').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: @json($chartLabels),
            datasets: [
                {
                    label: 'صافي الراتب',
                    data: @json($chartSalary),
                    backgroundColor: 'rgba(22,163,74,.7)',
                    borderRadius: 5,
                    yAxisID: 'y'
                },
                {
                    label: 'الطلبات',
                    data: @json($chartOrders),
                    type: 'line',
                    borderColor: '#6366f1',
                    backgroundColor: 'transparent',
                    pointRadius: 4,
                    yAxisID: 'y1'
                }
            ]
        },
        options: {
            responsive: true,
            plugins: { legend: { position: 'top' } },
            scales: {
                y:  { beginAtZero: true, position: 'right', title: { display: true, text: 'الراتب' } },
                y1: { beginAtZero: true, position: 'left',  title: { display: true, text: 'الطلبات' } }
            }
        }
    });
})();
</script>
@endif

</div>{{-- .bi-body --}}
</div>{{-- .bi-wrap --}}
@endsection
