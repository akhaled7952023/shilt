@extends('layouts.dashboard.app')

@section('title') اللوحة المالية — {{ $period->platform?->name }} — {{ $period->getDisplayLabel() }} @endsection

@section('content')

{{-- ════════════════════════════════════════════════════════
     PREMIUM FINANCIAL DASHBOARD — INLINE STYLES
     ════════════════════════════════════════════════════════ --}}
<style>
/* ── Base ── */
.fin-db { font-family: 'Quicksand', 'Open Sans', sans-serif; }

/* ── KPI Cards ── */
.kpi-card {
    border-radius: 16px;
    padding: 22px 20px;
    color: #fff;
    position: relative;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,.12);
    margin-bottom: 20px;
    transition: transform .2s, box-shadow .2s;
}
.kpi-card:hover { transform: translateY(-3px); box-shadow: 0 16px 40px rgba(0,0,0,.18); }
.kpi-card::after {
    content: '';
    position: absolute;
    top: -30px; right: -30px;
    width: 120px; height: 120px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.kpi-icon {
    width: 48px; height: 48px;
    background: rgba(255,255,255,.2);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 14px;
    font-size: 22px;
}
.kpi-value { font-size: 28px; font-weight: 700; line-height: 1.1; margin-bottom: 4px; }
.kpi-label { font-size: 13px; opacity: .85; font-weight: 500; }
.kpi-sub   { font-size: 11px; opacity: .7; margin-top: 6px; }

/* ── Gradient presets ── */
.g-purple  { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
.g-teal    { background: linear-gradient(135deg, #0d9488 0%, #0ea5e9 100%); }
.g-red     { background: linear-gradient(135deg, #ef4444 0%, #f97316 100%); }
.g-blue    { background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%); }
.g-amber   { background: linear-gradient(135deg, #f59e0b 0%, #84cc16 100%); }
.g-pink    { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); }
.g-green   { background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%); }
.g-navy    { background: linear-gradient(135deg, #1e3a5f 0%, #3b82f6 100%); }
.g-orange  { background: linear-gradient(135deg, #f97316 0%, #fbbf24 100%); }
.g-rose    { background: linear-gradient(135deg, #be123c 0%, #f43f5e 100%); }
.g-slate   { background: linear-gradient(135deg, #475569 0%, #64748b 100%); }
.g-cyan    { background: linear-gradient(135deg, #0891b2 0%, #06b6d4 100%); }
.g-profit  { background: linear-gradient(135deg, #166534 0%, #16a34a 100%); }
.g-loss    { background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%); }

/* ── Section cards ── */
.dash-card {
    background: #fff;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.06);
    margin-bottom: 20px;
    overflow: hidden;
}
.dash-card-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    display: flex;
    align-items: center;
    justify-content: space-between;
}
.dash-card-title {
    font-size: 15px;
    font-weight: 700;
    color: #1e293b;
    margin: 0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.dash-card-title .title-dot {
    width: 8px; height: 8px;
    border-radius: 50%;
    display: inline-block;
}
.dash-card-body { padding: 20px; }
.dash-card-body-0 { padding: 0; }

/* ── Chart containers ── */
.chart-wrap { position: relative; min-height: 280px; }
.chart-wrap canvas { max-width: 100%; }

/* ── Driver bar (horizontal progress bars) ── */
.driver-bar-row { padding: 10px 20px; border-bottom: 1px solid #f8fafc; }
.driver-bar-row:last-child { border-bottom: none; }
.driver-bar-name { font-size: 13px; font-weight: 600; color: #334155; margin-bottom: 5px; display: flex; justify-content: space-between; }
.driver-progress { height: 8px; border-radius: 4px; background: #f1f5f9; overflow: hidden; }
.driver-progress-fill { height: 100%; border-radius: 4px; transition: width .6s ease; }

/* ── Period banner ── */
.period-banner {
    background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 100%);
    border-radius: 16px;
    padding: 20px 24px;
    color: #fff;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    flex-wrap: wrap;
    gap: 12px;
}
.period-banner-title { font-size: 20px; font-weight: 700; }
.period-banner-meta  { font-size: 13px; opacity: .8; margin-top: 4px; }
.period-banner-actions { display: flex; gap: 8px; }
.period-badge {
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 4px 14px;
    font-size: 12px;
    font-weight: 600;
}

/* ── Tables ── */
.premium-table { width: 100%; border-collapse: collapse; font-size: 13px; }
.premium-table thead th {
    background: #f8fafc;
    padding: 10px 14px;
    font-weight: 700;
    color: #64748b;
    border-bottom: 2px solid #e2e8f0;
    white-space: nowrap;
}
.premium-table tbody td {
    padding: 10px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}
.premium-table tbody tr:hover { background: #f8fafc; }
.premium-table tfoot td {
    padding: 10px 14px;
    background: #f1f5f9;
    font-weight: 700;
    color: #1e293b;
}
.rank-badge {
    display: inline-flex; align-items: center; justify-content: center;
    width: 22px; height: 22px;
    border-radius: 50%;
    font-size: 11px; font-weight: 700;
}
.rank-1 { background: #fef3c7; color: #92400e; }
.rank-2 { background: #e2e8f0; color: #475569; }
.rank-3 { background: #fce7f3; color: #9d174d; }
.rank-n { background: #f1f5f9; color: #64748b; }

/* ── Summary stat row ── */
.stat-inline { display: flex; gap: 24px; flex-wrap: wrap; padding: 14px 20px; background: #f8fafc; border-top: 1px solid #e2e8f0; }
.stat-inline-item { text-align: center; }
.stat-inline-val  { font-size: 18px; font-weight: 700; color: #1e293b; }
.stat-inline-lbl  { font-size: 11px; color: #64748b; margin-top: 2px; }

/* ── Empty state ── */
.empty-state { padding: 48px 24px; text-align: center; color: #94a3b8; }
.empty-state i { font-size: 48px; margin-bottom: 12px; display: block; }

/* ── Print ── */
@media print {
    .no-print, .main-menu, .header-navbar, #customizer,
    .content-header, .breadcrumb-new, .period-banner-actions { display: none !important; }
    .print-header { display: block !important; }
    .dash-card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; break-inside: avoid; }
    .kpi-card { box-shadow: none !important; border: 1px solid #e2e8f0 !important; }
    body { font-size: 11px !important; }
    .app-content { padding: 0 !important; margin: 0 !important; }
    .col-print-3 { width: 25% !important; float: right !important; }
    canvas { max-height: 200px !important; }
}
.print-header { display: none; }
</style>

<div class="app-content content fin-db">
<div class="content-wrapper">

    {{-- Breadcrumb (no-print) --}}
    <div class="content-header row no-print">
        <div class="mb-2 content-header-left col-12 breadcrumb-new">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.index') }}">الفترات الشهرية</a></li>
                <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.show', $period) }}">{{ $period->getDisplayLabel() }}</a></li>
                <li class="breadcrumb-item active">اللوحة المالية</li>
            </ol>
        </div>
    </div>

    <div class="content-body">

        {{-- Print-only header --}}
        <div class="print-header" style="text-align:center;margin-bottom:20px;">
            <h2 style="margin:0;">اللوحة المالية الشهرية</h2>
            <p style="color:#64748b;margin:4px 0 0;">{{ $period->platform?->name }} — {{ $period->getDisplayLabel() }}</p>
            <hr style="margin:12px 0;">
        </div>

        {{-- Period banner --}}
        <div class="period-banner no-print">
            <div>
                <div class="period-banner-title">
                    {{ $period->platform?->name }} — {{ $period->getDisplayLabel() }}
                </div>
                <div class="period-banner-meta">
                    اللوحة المالية الشهرية الشاملة
                    @if($period->isClosed())
                        · أُغلقت في {{ $period->closed_at?->format('Y-m-d') }}
                        @if($period->closedBy) بواسطة {{ $period->closedBy->name }} @endif
                    @endif
                </div>
            </div>
            <div class="period-banner-actions">
                @if($period->isOpen())
                    <span class="period-badge"><i class="la la-unlock-alt"></i> مفتوح</span>
                @else
                    <span class="period-badge"><i class="la la-lock"></i> مغلق</span>
                @endif
                <a href="{{ route('dashboard.monthly.periods.financial-dashboard.pdf', $period) }}"
                   target="_blank"
                   class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;">
                    <i class="la la-file-pdf-o"></i> تصدير PDF
                </a>
                <a href="{{ route('dashboard.monthly.periods.show', $period) }}"
                   class="btn btn-sm" style="background:rgba(255,255,255,.15);color:#fff;border:1px solid rgba(255,255,255,.3);border-radius:8px;">
                    <i class="la la-arrow-right"></i> العودة
                </a>
            </div>
        </div>

        @if($settlements->isEmpty())
            <div class="dash-card">
                <div class="empty-state">
                    <i class="la la-chart-bar"></i>
                    <h5>لا توجد بيانات لهذه الفترة</h5>
                    <p>قم باستيراد بيانات {{ $period->platform?->name }} أولاً لعرض اللوحة المالية.</p>
                    <a href="{{ route('dashboard.monthly.periods.show', $period) }}" class="btn btn-primary btn-sm mt-2">
                        <i class="la la-upload"></i> الاستيراد
                    </a>
                </div>
            </div>
        @else

        {{-- ═══ CHEFZ: Payout filter tabs ═══ --}}
        @if($platformCode === 'the-chefz')
        @if(!$isMonthComplete)
            <div style="background:#fffbeb;border:1px solid #fbbf24;border-radius:10px;padding:10px 16px;margin-bottom:16px;display:flex;align-items:center;gap:10px;">
                <i class="la la-exclamation-triangle" style="color:#d97706;font-size:18px;"></i>
                <span style="font-size:13px;color:#92400e;">
                    <strong>الشهر غير مكتمل:</strong>
                    البيانات المعروضة تشمل
                    {{ $payout1Batch && !$payout2Batch ? 'الدفعة الأولى فقط' : 'الدفعة الثانية فقط' }}.
                </span>
            </div>
        @endif
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;" class="no-print">
            @php
                $dashUrl = route('dashboard.monthly.periods.financial-dashboard', $period);
            @endphp
            <a href="{{ $dashUrl }}"
               style="padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;transition:all .15s;
                      {{ $payoutFilter === 0 ? 'background:#7e22ce;color:white;box-shadow:0 2px 8px rgba(126,34,206,.3);' : 'background:white;color:#64748b;border:1.5px solid #e2e8f0;' }}">
                إجمالي الشهر
                @if($isMonthComplete) <i class="la la-check" style="font-size:12px;margin-right:3px;"></i> @endif
            </a>
            <a href="{{ $dashUrl . '?payout=1' }}"
               style="padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;transition:all .15s;
                      {{ $payoutFilter === 1 ? 'background:#0284c7;color:white;box-shadow:0 2px 8px rgba(2,132,199,.3);' : 'background:white;color:#64748b;border:1.5px solid #e2e8f0;' }}">
                الدفعة الأولى
                @if($payout1Batch) <i class="la la-check" style="font-size:12px;margin-right:3px;color:{{ $payoutFilter===1?'white':'#16a34a' }};"></i> @endif
            </a>
            <a href="{{ $dashUrl . '?payout=2' }}"
               style="padding:7px 18px;border-radius:20px;font-size:13px;font-weight:600;text-decoration:none;transition:all .15s;
                      {{ $payoutFilter === 2 ? 'background:#7e22ce;color:white;box-shadow:0 2px 8px rgba(126,34,206,.3);' : 'background:white;color:#64748b;border:1.5px solid #e2e8f0;' }}">
                الدفعة الثانية
                @if($payout2Batch) <i class="la la-check" style="font-size:12px;margin-right:3px;color:{{ $payoutFilter===2?'white':'#16a34a' }};"></i> @endif
            </a>
        </div>
        @endif

        {{-- ════════════════════════════════════════
             KPI CARDS
             ════════════════════════════════════════ --}}
        <div class="row">

            {{-- Total Orders --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-purple">
                    <div class="kpi-icon"><i class="la la-shopping-cart"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['totalOrders']) }}</div>
                    <div class="kpi-label">إجمالي الطلبات</div>
                    <div class="kpi-sub">متوسط {{ $kpis['avgOrders'] }} / مندوب</div>
                </div>
            </div>

            {{-- Gross Delivery Fees --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-teal">
                    <div class="kpi-icon"><i class="la la-money"></i></div>
                    <div class="kpi-value">{{ number_format($platformCode === 'hungerstation' ? $kpis['basicPayment'] : $kpis['grossFees'], 0) }}</div>
                    <div class="kpi-label">{{ $platformCode === 'hungerstation' ? 'Basic Payment (هنقرستيشن)' : 'رسوم التوصيل الإجمالية' }}</div>
                    <div class="kpi-sub">ريال سعودي</div>
                </div>
            </div>

            @if($platformCode === 'hungerstation')
            {{-- FTR: Platform Penalties --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-red">
                    <div class="kpi-icon"><i class="la la-arrow-down"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['penaltiesTotal'], 0) }}</div>
                    <div class="kpi-label">غرامات المنصة</div>
                    <div class="kpi-sub">من مناديب RLVL</div>
                </div>
            </div>

            {{-- FTR: Stacking Deduction (company absorbed) --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-amber">
                    <div class="kpi-icon"><i class="la la-shield"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['stackingTotal'], 0) }}</div>
                    <div class="kpi-label">Stacking (تمتصه الشركة)</div>
                    <div class="kpi-sub">مستبعد من راتب المندوب</div>
                </div>
            </div>

            {{-- FTR: Company Expenses --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-amber">
                    <div class="kpi-icon"><i class="la la-building"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['companyExpenses'], 0) }}</div>
                    <div class="kpi-label">مصروفات الشركة</div>
                    <div class="kpi-sub">{{ $expenses->count() }} بند</div>
                </div>
            </div>
            @endif

            @if($platformCode === 'the-chefz')
            {{-- VAT --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-red">
                    <div class="kpi-icon"><i class="la la-percent"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['vatTotal'], 0) }}</div>
                    <div class="kpi-label">ضريبة القيمة المضافة</div>
                    <div class="kpi-sub">{{ isset($settlements->first()->chefz_tax_rate) ? number_format($settlements->first()->chefz_tax_rate * 100, 0) . '%' : '—' }}</div>
                </div>
            </div>

            {{-- Company Share --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-pink">
                    <div class="kpi-icon"><i class="la la-building"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['commissionTotal'], 0) }}</div>
                    <div class="kpi-label">حصة الشركة</div>
                    <div class="kpi-sub">{{ isset($settlements->first()->company_share_rate) ? number_format($settlements->first()->company_share_rate * 100, 0) . '%' : '—' }}</div>
                </div>
            </div>

            {{-- Platform Deductions --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-rose">
                    <div class="kpi-icon"><i class="la la-arrow-down"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['platformDeductions'], 0) }}</div>
                    <div class="kpi-label">خصومات المنصة</div>
                    <div class="kpi-sub">من ملفات شيفز</div>
                </div>
            </div>

            {{-- Platform Compensations --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-teal">
                    <div class="kpi-icon"><i class="la la-gift"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['platformCompensations'], 0) }}</div>
                    <div class="kpi-label">تعويضات المنصة</div>
                    <div class="kpi-sub">من ملفات شيفز</div>
                </div>
            </div>

            {{-- Positive Bonus --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-amber">
                    <div class="kpi-icon"><i class="la la-star"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['positiveBonus'], 0) }}</div>
                    <div class="kpi-label">المكافآت الصالحة</div>
                    <div class="kpi-sub">bonus &gt; 0 فقط</div>
                </div>
            </div>
            @endif

            {{-- Net Revenue --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-green">
                    <div class="kpi-icon"><i class="la la-line-chart"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['netRevenue'], 0) }}</div>
                    <div class="kpi-label">الإيراد الصافي</div>
                    <div class="kpi-sub">بعد الخصومات</div>
                </div>
            </div>

            {{-- Net Salaries --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-navy">
                    <div class="kpi-icon"><i class="la la-users"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['netSalaries'], 0) }}</div>
                    <div class="kpi-label">صافي رواتب المناديب</div>
                    <div class="kpi-sub">متوسط {{ number_format($kpis['avgSalary'], 0) }} / مندوب</div>
                </div>
            </div>

            {{-- Net Profit --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card {{ $kpis['netProfit'] >= 0 ? 'g-profit' : 'g-loss' }}">
                    <div class="kpi-icon"><i class="la la-{{ $kpis['netProfit'] >= 0 ? 'thumbs-up' : 'thumbs-down' }}"></i></div>
                    <div class="kpi-value">{{ number_format(abs($kpis['netProfit']), 0) }}</div>
                    <div class="kpi-label">{{ $kpis['netProfit'] >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}</div>
                    <div class="kpi-sub">بعد الرواتب والمصروفات</div>
                </div>
            </div>

            {{-- Drivers count --}}
            <div class="col-xl-2 col-md-4 col-sm-6">
                <div class="kpi-card g-slate">
                    <div class="kpi-icon"><i class="la la-id-card"></i></div>
                    <div class="kpi-value">{{ number_format($kpis['totalDrivers']) }}</div>
                    <div class="kpi-label">عدد المناديب</div>
                    <div class="kpi-sub">هذه الفترة</div>
                </div>
            </div>

        </div>
        {{-- end KPI row --}}

        {{-- ════════════════════════════════════════
             DAILY TRENDS — Charts Row 1
             ════════════════════════════════════════ --}}
        @if($dailyData->isNotEmpty())
        <div class="row">

            {{-- Daily Orders + Revenue Line Chart --}}
            <div class="col-md-8">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#6366f1;"></span>
                            الاتجاه اليومي — الطلبات والإيرادات
                        </h6>
                        <small class="text-muted">{{ $dailyData->count() }} يوم</small>
                    </div>
                    <div class="dash-card-body">
                        <div class="chart-wrap">
                            <canvas id="chartDailyTrend"></canvas>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Revenue Distribution Donut --}}
            <div class="col-md-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#10b981;"></span>
                            توزيع الإيرادات
                        </h6>
                    </div>
                    <div class="dash-card-body">
                        <div id="donutRevenue" style="min-height:240px;"></div>
                    </div>
                </div>
            </div>

        </div>

        {{-- Daily Net / Deductions trend (HS only) --}}
        @if($platformCode === 'hungerstation' && $dailyData->isNotEmpty())
        <div class="row">
            <div class="col-12">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#f59e0b;"></span>
                            الاتجاه اليومي — الخصومات والتعويضات والصافي
                        </h6>
                    </div>
                    <div class="dash-card-body">
                        <div class="chart-wrap" style="min-height:220px;">
                            <canvas id="chartDailyDeductions"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif
        @endif

        {{-- ════════════════════════════════════════
             TOP DRIVERS
             ════════════════════════════════════════ --}}
        <div class="row">

            {{-- Top 10 by Net Salary --}}
            @if($topBySalary->isNotEmpty())
            <div class="col-md-6">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#10b981;"></span>
                            أعلى مناديب — صافي الراتب
                        </h6>
                        <small class="text-muted">أعلى 10</small>
                    </div>
                    <div class="dash-card-body-0">
                        @foreach($topBySalary as $i => $s)
                        <div class="driver-bar-row">
                            <div class="driver-bar-name">
                                <span>
                                    <span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-n')) }}">{{ $i+1 }}</span>
                                    &nbsp;{{ $s->delegate?->name ?? '—' }}
                                </span>
                                <strong class="text-success">{{ number_format($s->net_salary, 0) }} ر</strong>
                            </div>
                            <div class="driver-progress">
                                <div class="driver-progress-fill"
                                     style="width: {{ $maxSalary > 0 ? min(100, max(0, round((float)$s->net_salary / (float)$maxSalary * 100))) : 0 }}%;
                                            background: linear-gradient(90deg, #10b981, #06b6d4);">
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="stat-inline">
                            <div class="stat-inline-item">
                                <div class="stat-inline-val text-success">{{ number_format($kpis['netSalaries'], 0) }}</div>
                                <div class="stat-inline-lbl">إجمالي الرواتب</div>
                            </div>
                            <div class="stat-inline-item">
                                <div class="stat-inline-val">{{ number_format($kpis['avgSalary'], 0) }}</div>
                                <div class="stat-inline-lbl">متوسط الراتب</div>
                            </div>
                            <div class="stat-inline-item">
                                <div class="stat-inline-val">{{ number_format($topBySalary->first()?->net_salary ?? 0, 0) }}</div>
                                <div class="stat-inline-lbl">أعلى راتب</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

            {{-- Top 10 by Orders --}}
            @if($topByOrders->isNotEmpty())
            <div class="col-md-6">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#6366f1;"></span>
                            أعلى مناديب — عدد الطلبات
                        </h6>
                        <small class="text-muted">أعلى 10</small>
                    </div>
                    <div class="dash-card-body-0">
                        @foreach($topByOrders as $i => $s)
                        <div class="driver-bar-row">
                            <div class="driver-bar-name">
                                <span>
                                    <span class="rank-badge {{ $i===0?'rank-1':($i===1?'rank-2':($i===2?'rank-3':'rank-n')) }}">{{ $i+1 }}</span>
                                    &nbsp;{{ $s->delegate?->name ?? '—' }}
                                </span>
                                <strong class="text-primary">{{ number_format($s->total_orders) }} طلب</strong>
                            </div>
                            <div class="driver-progress">
                                <div class="driver-progress-fill"
                                     style="width: {{ $maxOrders > 0 ? min(100, max(0, round((float)$s->total_orders / (float)$maxOrders * 100))) : 0 }}%;
                                            background: linear-gradient(90deg, #6366f1, #8b5cf6);">
                                </div>
                            </div>
                        </div>
                        @endforeach
                        <div class="stat-inline">
                            <div class="stat-inline-item">
                                <div class="stat-inline-val text-primary">{{ number_format($kpis['totalOrders']) }}</div>
                                <div class="stat-inline-lbl">إجمالي الطلبات</div>
                            </div>
                            <div class="stat-inline-item">
                                <div class="stat-inline-val">{{ number_format($kpis['avgOrders'], 1) }}</div>
                                <div class="stat-inline-lbl">متوسط / مندوب</div>
                            </div>
                            <div class="stat-inline-item">
                                <div class="stat-inline-val">{{ number_format($topByOrders->first()?->total_orders ?? 0) }}</div>
                                <div class="stat-inline-lbl">أعلى مندوب</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- ════════════════════════════════════════
             PLATFORM-SPECIFIC CHARTS
             ════════════════════════════════════════ --}}

        @if($platformCode === 'hungerstation')

        {{-- HS: Top by Deductions + Compensations --}}
        @if($topByDeductions->isNotEmpty() || $topByComp->isNotEmpty())
        <div class="row">
            @if($topByDeductions->isNotEmpty())
            <div class="col-md-6">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#ef4444;"></span>
                            أعلى مناديب — خصومات المنصة
                        </h6>
                    </div>
                    <div class="dash-card-body-0">
                        @php $maxDed = (float)($topByDeductions->max('total_platform_penalties') ?: 1); @endphp
                        @foreach($topByDeductions->take(8) as $i => $s)
                        <div class="driver-bar-row">
                            <div class="driver-bar-name">
                                <span>
                                    <span class="rank-badge rank-n">{{ $i+1 }}</span>
                                    &nbsp;{{ $s->delegate?->name ?? '—' }}
                                </span>
                                <strong class="text-danger">{{ number_format($s->total_platform_penalties, 0) }}</strong>
                            </div>
                            <div class="driver-progress">
                                <div class="driver-progress-fill"
                                     style="width: {{ min(100, round((float)$s->total_platform_penalties / $maxDed * 100)) }}%;
                                            background: linear-gradient(90deg, #ef4444, #f97316);">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif
        </div>
        @endif

        {{-- HS: Deductions by Type + Compensation Notes + Expenses by Category --}}
        <div class="row">

            @if($deductionsByType->isNotEmpty())
            <div class="col-md-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#ef4444;"></span>
                            الخصومات اليدوية — حسب النوع
                        </h6>
                        <strong class="text-danger" style="font-size:12px;">
                            {{ number_format($deductionsByType->sum('value'), 2) }} ر.س
                        </strong>
                    </div>
                    <div class="dash-card-body" style="padding:12px 14px;">
                        <canvas id="chartDeductionTypes" height="130"></canvas>
                    </div>
                </div>
            </div>
            @endif

            @if($deductionNotes->isNotEmpty())
            <div class="col-md-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#f97316;"></span>
                            خصومات المنصة — حسب السبب
                        </h6>
                    </div>
                    <div class="dash-card-body-0">
                        @php $maxDN = $deductionNotes->max('total') ?: 1; @endphp
                        @foreach($deductionNotes as $dn)
                        <div class="driver-bar-row">
                            <div class="driver-bar-name">
                                <span style="font-size:12px;">{{ $dn->note ?: 'غير محدد' }}</span>
                                <strong class="text-danger" style="font-size:12px;">{{ number_format($dn->total, 0) }}</strong>
                            </div>
                            <div class="driver-progress">
                                <div class="driver-progress-fill"
                                     style="width: {{ min(100, round($dn->total / $maxDN * 100)) }}%;
                                            background: linear-gradient(90deg, #f97316, #fbbf24);">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

            @if($compensationNotes->isNotEmpty())
            <div class="col-md-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#0d9488;"></span>
                            تعويضات المنصة — حسب السبب
                        </h6>
                    </div>
                    <div class="dash-card-body-0">
                        @php $maxCN = $compensationNotes->max('total') ?: 1; @endphp
                        @foreach($compensationNotes as $cn)
                        <div class="driver-bar-row">
                            <div class="driver-bar-name">
                                <span style="font-size:12px;">{{ $cn->note ?: 'غير محدد' }}</span>
                                <strong class="text-info" style="font-size:12px;">{{ number_format($cn->total, 0) }}</strong>
                            </div>
                            <div class="driver-progress">
                                <div class="driver-progress-fill"
                                     style="width: {{ min(100, round($cn->total / $maxCN * 100)) }}%;
                                            background: linear-gradient(90deg, #0d9488, #0ea5e9);">
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
            @endif

        </div>

        {{-- HS: Expenses by Category + Donut --}}
        @if($expenses->isNotEmpty())
        <div class="row">
            <div class="col-md-5">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#f59e0b;"></span>
                            مصروفات الشركة — حسب الفئة
                        </h6>
                    </div>
                    <div class="dash-card-body">
                        <div id="donutExpenses" style="min-height:200px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-7">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#f59e0b;"></span>
                            جدول المصروفات
                        </h6>
                        <strong class="text-danger">{{ number_format($expenses->sum('amount'), 2) }} ريال</strong>
                    </div>
                    <div class="dash-card-body-0">
                        <div class="table-responsive">
                            <table class="premium-table">
                                <thead>
                                    <tr>
                                        <th>الفئة</th>
                                        <th class="text-left">المبلغ</th>
                                        <th>ملاحظات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($expenses as $exp)
                                    <tr>
                                        <td><strong>{{ $exp->category }}</strong></td>
                                        <td class="text-left text-danger font-weight-bold">{{ number_format($exp->amount, 2) }}</td>
                                        <td class="text-muted">{{ $exp->notes ?? '—' }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr>
                                        <td>الإجمالي</td>
                                        <td class="text-left text-danger">{{ number_format($expenses->sum('amount'), 2) }} ريال</td>
                                        <td></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        @endif
        {{-- end HS-specific --}}

        @if($platformCode === 'the-chefz')
        {{-- Chefz: formula breakdown summary --}}
        <div class="row">
            <div class="col-md-4">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#ec4899;"></span>
                            توزيع الإيراد — شيفز
                        </h6>
                    </div>
                    <div class="dash-card-body">
                        <div id="donutChefzBreakdown" style="min-height:200px;"></div>
                    </div>
                </div>
            </div>
            <div class="col-md-8">
                <div class="dash-card">
                    <div class="dash-card-header">
                        <h6 class="dash-card-title">
                            <span class="title-dot" style="background:#ec4899;"></span>
                            ملخص الحسابات — شيفز
                            @if($payoutFilter > 0)
                                <span style="font-size:11px;font-weight:600;padding:2px 10px;border-radius:10px;margin-right:6px;
                                      background:{{ $payoutFilter===1?'#dbeafe':'#f3e8ff' }};
                                      color:{{ $payoutFilter===1?'#1d4ed8':'#7e22ce' }};">
                                    {{ $payoutFilter===1 ? 'الدفعة الأولى' : 'الدفعة الثانية' }}
                                </span>
                            @endif
                        </h6>
                    </div>
                    <div class="dash-card-body">
                        <div class="row text-center">
                            <div class="col-4">
                                <div style="background:#fdf2f8;border-radius:12px;padding:12px;">
                                    <div style="font-size:18px;font-weight:700;color:#ec4899;">{{ number_format($kpis['vatTotal'], 2) }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">ضريبة القيمة المضافة</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div style="background:#faf5ff;border-radius:12px;padding:12px;">
                                    <div style="font-size:18px;font-weight:700;color:#8b5cf6;">{{ number_format($kpis['commissionTotal'], 2) }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">حصة الشركة</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div style="background:#fff1f2;border-radius:12px;padding:12px;">
                                    <div style="font-size:18px;font-weight:700;color:#be123c;">{{ number_format($kpis['platformDeductions'], 2) }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">خصومات المنصة</div>
                                </div>
                            </div>
                        </div>
                        <div class="row text-center mt-2">
                            <div class="col-4">
                                <div style="background:#f0fdfa;border-radius:12px;padding:12px;">
                                    <div style="font-size:18px;font-weight:700;color:#0d9488;">{{ number_format($kpis['platformCompensations'], 2) }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">تعويضات المنصة</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div style="background:#fffbeb;border-radius:12px;padding:12px;">
                                    <div style="font-size:18px;font-weight:700;color:#d97706;">{{ number_format($kpis['positiveBonus'], 2) }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">مكافآت صالحة</div>
                                </div>
                            </div>
                            <div class="col-4">
                                <div style="background:#f0fdf4;border-radius:12px;padding:12px;">
                                    <div style="font-size:18px;font-weight:700;color:#16a34a;">{{ number_format($kpis['netSalaries'], 2) }}</div>
                                    <div style="font-size:11px;color:#64748b;margin-top:3px;">صافي الرواتب</div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        @endif

        {{-- ════════════════════════════════════════
             FULL SETTLEMENTS TABLE
             ════════════════════════════════════════ --}}
        <div class="dash-card">
            <div class="dash-card-header">
                <h6 class="dash-card-title">
                    <span class="title-dot" style="background:#64748b;"></span>
                    ملخص تسويات جميع المناديب
                </h6>
                <span style="font-size:13px;color:#64748b;">{{ $settlements->count() }} مندوب</span>
            </div>
            <div class="dash-card-body-0">
                <div class="table-responsive">
                    <table class="premium-table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>المندوب</th>
                                @if($platformCode === 'the-chefz')<th class="text-center">الدفعة</th>@endif
                                <th class="text-center">الطلبات</th>
                                <th class="text-left">{{ $platformCode === 'hungerstation' ? 'Basic Payment' : 'رسوم التوصيل' }}</th>
                                @if($platformCode === 'hungerstation')
                                    <th class="text-left">Distance Pay</th>
                                    <th class="text-left">غرامات المنصة</th>
                                @else
                                    <th class="text-left">ضريبة القيمة المضافة</th>
                                    <th class="text-left">حصة الشركة</th>
                                @endif
                                <th class="text-left">صافي الراتب</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($settlements as $i => $s)
                            <tr>
                                <td><span class="rank-badge rank-n" style="font-size:10px;">{{ $i+1 }}</span></td>
                                <td><strong>{{ $s->delegate?->name ?? '—' }}</strong></td>
                                @if($platformCode === 'the-chefz')
                                    <td class="text-center">
                                        @php $pn = $s->payout_number ?? 0; @endphp
                                        @if($pn === 1)
                                            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:8px;background:#dbeafe;color:#1d4ed8;">د١</span>
                                        @elseif($pn === 2)
                                            <span style="font-size:11px;font-weight:700;padding:2px 8px;border-radius:8px;background:#f3e8ff;color:#7e22ce;">د٢</span>
                                        @else
                                            <span style="font-size:11px;color:#64748b;">إجمالي</span>
                                        @endif
                                    </td>
                                @endif
                                <td class="text-center">{{ number_format($s->total_orders) }}</td>
                                <td class="text-left">{{ number_format($platformCode === 'hungerstation' ? $s->basic_payment : $s->gross_delivery_fees, 2) }}</td>
                                @if($platformCode === 'hungerstation')
                                    <td class="text-left text-success">{{ number_format($s->distance_payment, 2) }}</td>
                                    <td class="text-left text-danger">{{ number_format($s->total_platform_penalties, 2) }}</td>
                                @else
                                    <td class="text-left text-danger">{{ number_format($s->chefz_tax_amount, 2) }}</td>
                                    <td class="text-left" style="color:#8b5cf6;">{{ number_format($s->company_share_amount, 2) }}</td>
                                @endif
                                <td class="text-left font-weight-bold {{ $s->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                    {{ number_format($s->net_salary, 2) }}
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="{{ $platformCode === 'the-chefz' ? 3 : 2 }}">الإجمالي</td>
                                <td class="text-center">{{ number_format($kpis['totalOrders']) }}</td>
                                <td class="text-left">{{ number_format($platformCode === 'hungerstation' ? $kpis['basicPayment'] : $kpis['grossFees'], 2) }}</td>
                                @if($platformCode === 'hungerstation')
                                    <td class="text-left text-success">{{ number_format($kpis['distancePayment'], 2) }}</td>
                                    <td class="text-left text-danger">{{ number_format($kpis['penaltiesTotal'], 2) }}</td>
                                @else
                                    <td class="text-left text-danger">{{ number_format($kpis['vatTotal'], 2) }}</td>
                                    <td class="text-left" style="color:#8b5cf6;">{{ number_format($kpis['commissionTotal'], 2) }}</td>
                                @endif
                                <td class="text-left text-success font-weight-bold">{{ number_format($kpis['netSalaries'], 2) }} ريال</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>

        @endif
        {{-- end if settlements not empty --}}

    </div>{{-- content-body --}}
</div>{{-- content-wrapper --}}
</div>{{-- app-content --}}
@endsection

@section('scripts')
{{-- Chart.js --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>

<script>
(function () {
    'use strict';

    var platformCode = '{{ $platformCode }}';

    // Palette
    var COLORS = {
        purple:  '#6366f1',
        teal:    '#0d9488',
        red:     '#ef4444',
        blue:    '#3b82f6',
        amber:   '#f59e0b',
        pink:    '#ec4899',
        green:   '#10b981',
        orange:  '#f97316',
        cyan:    '#06b6d4',
        slate:   '#64748b',
    };

    var COLOR_ARRAY = Object.values(COLORS);

    Chart.defaults.font.family = "'Quicksand', 'Open Sans', sans-serif";
    Chart.defaults.color       = '#64748b';

    // ── Daily trends data ──
    var dailyData = @json($dailyData);

    if (dailyData.length > 0) {
        var dailyLabels  = dailyData.map(function(d) { return d.date; });
        var dailyOrders  = dailyData.map(function(d) { return parseInt(d.orders); });
        var dailyRevenue = dailyData.map(function(d) { return parseFloat(d.revenue); });

        // Daily Orders + Revenue — dual-axis line chart
        var ctx1 = document.getElementById('chartDailyTrend');
        if (ctx1) {
            new Chart(ctx1, {
                type: 'line',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        {
                            label: 'الطلبات',
                            data: dailyOrders,
                            borderColor: COLORS.purple,
                            backgroundColor: 'rgba(99,102,241,.08)',
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            yAxisID: 'yOrders',
                        },
                        {
                            label: 'رسوم التوصيل',
                            data: dailyRevenue,
                            borderColor: COLORS.teal,
                            backgroundColor: 'rgba(13,148,136,.08)',
                            fill: false,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 7,
                            yAxisID: 'yRevenue',
                        },
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: {
                        legend: { position: 'top' },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    return ctx.dataset.label + ': ' + ctx.parsed.y.toLocaleString('ar-SA');
                                }
                            }
                        }
                    },
                    scales: {
                        x: {
                            grid: { color: 'rgba(0,0,0,.04)' },
                            ticks: { maxRotation: 45 }
                        },
                        yOrders: {
                            type: 'linear',
                            position: 'right',
                            beginAtZero: true,
                            grid: { drawOnChartArea: false },
                            title: { display: true, text: 'طلبات' }
                        },
                        yRevenue: {
                            type: 'linear',
                            position: 'left',
                            beginAtZero: true,
                            grid: { color: 'rgba(0,0,0,.04)' },
                            title: { display: true, text: 'ريال' }
                        }
                    }
                }
            });
        }

        // HS: Daily deductions / compensations / net chart
        @if($platformCode === 'hungerstation')
        var dailyDed   = dailyData.map(function(d) { return parseFloat(d.deductions); });
        var dailyComp  = dailyData.map(function(d) { return parseFloat(d.compensations); });
        var dailyNet   = dailyData.map(function(d) { return parseFloat(d.net); });

        var ctx2 = document.getElementById('chartDailyDeductions');
        if (ctx2) {
            new Chart(ctx2, {
                type: 'bar',
                data: {
                    labels: dailyLabels,
                    datasets: [
                        {
                            label: 'الخصومات',
                            data: dailyDed,
                            backgroundColor: 'rgba(239,68,68,.6)',
                            borderRadius: 4,
                            order: 1,
                        },
                        {
                            label: 'التعويضات',
                            data: dailyComp,
                            backgroundColor: 'rgba(6,182,212,.6)',
                            borderRadius: 4,
                            order: 1,
                        },
                        {
                            label: 'الصافي اليومي',
                            data: dailyNet,
                            type: 'line',
                            borderColor: COLORS.green,
                            backgroundColor: 'rgba(16,185,129,.08)',
                            fill: false,
                            tension: 0.4,
                            pointRadius: 3,
                            order: 0,
                        }
                    ]
                },
                options: {
                    responsive: true,
                    interaction: { mode: 'index', intersect: false },
                    plugins: { legend: { position: 'top' } },
                    scales: {
                        x: { stacked: false, grid: { color: 'rgba(0,0,0,.04)' } },
                        y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } }
                    }
                }
            });
        }
        @endif
    }

    // ── Revenue Distribution Donut (Morris) ──
    @if($platformCode === 'hungerstation')
    var revenueDonutData = [
        { label: 'Basic Payment',  value: {{ round($kpis['basicPayment'], 2) }} },
        { label: 'غرامات المنصة', value: {{ round($kpis['penaltiesTotal'], 2) }} },
        { label: 'Stacking (شركة)', value: {{ round($kpis['stackingTotal'], 2) }} },
        { label: 'صافي الرواتب',  value: {{ round($kpis['netSalaries'], 2) }} },
        { label: 'مصروفات الشركة', value: {{ round($kpis['companyExpenses'], 2) }} },
    ].filter(function(d) { return d.value > 0; });
    @else
    var revenueDonutData = [
        { label: 'رسوم التوصيل الإجمالية', value: {{ round($kpis['grossFees'], 2) }} },
        { label: 'ضريبة القيمة المضافة',   value: {{ round($kpis['vatTotal'], 2) }} },
        { label: 'حصة الشركة',             value: {{ round($kpis['commissionTotal'], 2) }} },
        { label: 'صافي الرواتب',           value: {{ round($kpis['netSalaries'], 2) }} },
    ].filter(function(d) { return d.value > 0; });
    @endif

    if (revenueDonutData.length > 0 && document.getElementById('donutRevenue')) {
        Morris.Donut({
            element: 'donutRevenue',
            data:    revenueDonutData,
            colors:  [COLORS.teal, COLORS.red, COLORS.blue, COLORS.purple, COLORS.amber],
            resize:  true,
        });
    }

    // ── Chefz breakdown donut ──
    @if($platformCode === 'the-chefz')
    if (document.getElementById('donutChefzBreakdown')) {
        Morris.Donut({
            element: 'donutChefzBreakdown',
            data: [
                { label: 'صافي الرواتب',           value: {{ round($kpis['netSalaries'], 2) }} },
                { label: 'ضريبة القيمة المضافة',   value: {{ round($kpis['vatTotal'], 2) }} },
                { label: 'حصة الشركة',             value: {{ round($kpis['commissionTotal'], 2) }} },
            ].filter(function(d) { return d.value > 0; }),
            colors:  [COLORS.purple, COLORS.pink, COLORS.amber],
            resize:  true,
        });
    }
    @endif

    // ── HS Deductions by Type — Compact Horizontal Bar ──
    @if($platformCode === 'hungerstation' && $deductionsByType->isNotEmpty())
    (function(){
        var el = document.getElementById('chartDeductionTypes');
        if (!el) return;
        var data = @json($deductionsByType->values());
        var labels = data.map(function(d){ return d.label; });
        var values = data.map(function(d){ return d.value; });
        var palette = ['rgba(220,38,38,.75)','rgba(234,88,12,.75)','rgba(217,119,6,.75)',
                       'rgba(190,24,93,.75)','rgba(99,102,241,.75)','rgba(2,132,199,.75)',
                       'rgba(22,163,74,.75)','rgba(71,85,105,.75)','rgba(153,27,27,.75)'];
        new Chart(el.getContext('2d'), {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [{
                    label: 'المبلغ (ر.س)',
                    data: values,
                    backgroundColor: palette.slice(0, values.length),
                    borderRadius: 4,
                    borderSkipped: false
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: function(c){ return ' ' + c.parsed.x.toLocaleString('en',{minimumFractionDigits:2}) + ' ر.س'; }
                        }
                    }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } },
                    y: { grid: { display: false }, ticks: { font: { size: 11 } } }
                }
            }
        });
    })();
    @endif

    // ── HS Expenses by Category Donut ──
    @if($platformCode === 'hungerstation' && $expensesByCategory->isNotEmpty())
    var expCatData = @json($expensesByCategory->values());
    if (expCatData.length > 0 && document.getElementById('donutExpenses')) {
        Morris.Donut({
            element: 'donutExpenses',
            data:    expCatData,
            colors:  [COLORS.amber, COLORS.orange, COLORS.red, COLORS.pink, COLORS.purple, COLORS.blue],
            resize:  true,
        });
    }
    @endif

})();
</script>
@endsection
