<!DOCTYPE html>
<html dir="rtl" lang="ar">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>التقرير المالي — {{ $period->platform?->name }} — {{ $period->getDisplayLabel() }}</title>
<style>
/* ═══════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Arial', 'Tahoma', sans-serif;
    font-size: 13px;
    color: #1e293b;
    background: #f1f5f9;
    direction: rtl;
}

/* ═══════════════════════════════════════════
   A4 CONTENT WRAPPER
═══════════════════════════════════════════ */
#pdf-content {
    width: 794px;
    margin: 24px auto;
    background: #ffffff;
    border-radius: 8px;
    box-shadow: 0 4px 40px rgba(0,0,0,.12);
    overflow: hidden;
}

/* ═══════════════════════════════════════════
   REPORT HEADER
═══════════════════════════════════════════ */
.rpt-header {
    background: linear-gradient(135deg, #1e3a5f 0%, #1e40af 50%, #312e81 100%);
    padding: 28px 36px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    color: #fff;
}
.rpt-header-brand {
    display: flex;
    align-items: center;
    gap: 14px;
}
.rpt-logo-box {
    width: 54px; height: 54px;
    background: rgba(255,255,255,.15);
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 26px;
    flex-shrink: 0;
}
.rpt-brand-text h1 {
    font-size: 18px;
    font-weight: 700;
    margin-bottom: 3px;
    letter-spacing: -.2px;
}
.rpt-brand-text p {
    font-size: 13px;
    opacity: .8;
    margin: 0;
}
.rpt-header-meta {
    text-align: left;
    font-size: 12px;
    opacity: .9;
    line-height: 1.8;
}
.rpt-header-meta strong {
    display: block;
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 4px;
    opacity: 1;
}
.rpt-period-badge {
    display: inline-block;
    background: rgba(255,255,255,.2);
    border-radius: 20px;
    padding: 3px 12px;
    font-size: 11px;
    font-weight: 600;
    margin-top: 4px;
}

/* ═══════════════════════════════════════════
   SECTION
═══════════════════════════════════════════ */
.pdf-section {
    padding: 24px 36px;
    border-bottom: 1px solid #e2e8f0;
}
.pdf-section:last-child { border-bottom: none; }

.section-title {
    font-size: 14px;
    font-weight: 700;
    color: #1e293b;
    margin-bottom: 16px;
    display: flex;
    align-items: center;
    gap: 8px;
    padding-bottom: 8px;
    border-bottom: 2px solid #e2e8f0;
}
.section-title-dot {
    width: 10px; height: 10px;
    border-radius: 50%;
    display: inline-block;
    flex-shrink: 0;
}

/* ═══════════════════════════════════════════
   KPI CARDS
═══════════════════════════════════════════ */
.kpi-grid {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 12px;
}
.kpi-card {
    border-radius: 14px;
    padding: 16px 14px;
    color: #fff;
    position: relative;
    overflow: hidden;
    page-break-inside: avoid;
    break-inside: avoid;
}
.kpi-card::after {
    content: '';
    position: absolute;
    top: -20px; left: -20px;
    width: 80px; height: 80px;
    border-radius: 50%;
    background: rgba(255,255,255,.08);
}
.kpi-icon-wrap {
    width: 36px; height: 36px;
    background: rgba(255,255,255,.2);
    border-radius: 8px;
    display: flex; align-items: center; justify-content: center;
    margin-bottom: 10px;
    font-size: 18px;
}
.kpi-num  { font-size: 20px; font-weight: 700; line-height: 1.1; letter-spacing: -.5px; }
.kpi-lbl  { font-size: 10.5px; opacity: .85; margin-top: 4px; font-weight: 500; }
.kpi-sub  { font-size: 10px; opacity: .7; margin-top: 3px; }

/* ── gradient presets ── */
.g-purple  { background: linear-gradient(135deg, #6366f1 0%, #8b5cf6 100%); }
.g-teal    { background: linear-gradient(135deg, #0d9488 0%, #0ea5e9 100%); }
.g-red     { background: linear-gradient(135deg, #ef4444 0%, #f97316 100%); }
.g-blue    { background: linear-gradient(135deg, #3b82f6 0%, #06b6d4 100%); }
.g-amber   { background: linear-gradient(135deg, #f59e0b 0%, #84cc16 100%); }
.g-pink    { background: linear-gradient(135deg, #ec4899 0%, #8b5cf6 100%); }
.g-green   { background: linear-gradient(135deg, #10b981 0%, #06b6d4 100%); }
.g-navy    { background: linear-gradient(135deg, #1e3a5f 0%, #3b82f6 100%); }
.g-slate   { background: linear-gradient(135deg, #475569 0%, #64748b 100%); }
.g-profit  { background: linear-gradient(135deg, #166534 0%, #16a34a 100%); }
.g-loss    { background: linear-gradient(135deg, #991b1b 0%, #dc2626 100%); }

/* ═══════════════════════════════════════════
   FINANCIAL SUMMARY STRIP
═══════════════════════════════════════════ */
.fin-strip {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 0;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    margin-bottom: 0;
}
.fin-strip-item {
    padding: 14px 16px;
    text-align: center;
    border-left: 1px solid #e2e8f0;
}
.fin-strip-item:first-child { border-left: none; }
.fin-strip-val { font-size: 18px; font-weight: 700; color: #1e293b; }
.fin-strip-lbl { font-size: 11px; color: #64748b; margin-top: 3px; }

/* ═══════════════════════════════════════════
   CHARTS
═══════════════════════════════════════════ */
.chart-row { display: grid; gap: 16px; margin-bottom: 0; }
.chart-row-2 { grid-template-columns: 1fr 1fr; }
.chart-row-3 { grid-template-columns: 1fr 1fr 1fr; }
.chart-row-8-4 { grid-template-columns: 8fr 4fr; }

.chart-box {
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    page-break-inside: avoid;
    break-inside: avoid;
    background: #fff;
}
.chart-box-head {
    padding: 10px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    font-size: 12px;
    font-weight: 700;
    color: #334155;
    display: flex;
    align-items: center;
    gap: 6px;
}
.chart-box-body { padding: 14px; }
.chart-canvas-wrap { position: relative; }

/* ═══════════════════════════════════════════
   DRIVER BARS
═══════════════════════════════════════════ */
.driver-row {
    padding: 7px 14px;
    border-bottom: 1px solid #f8fafc;
    display: flex;
    align-items: center;
    gap: 10px;
}
.driver-row:last-child { border-bottom: none; }
.driver-rank {
    width: 20px; height: 20px;
    border-radius: 50%;
    background: #f1f5f9;
    color: #64748b;
    font-size: 10px;
    font-weight: 700;
    display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.driver-name { font-size: 12px; font-weight: 600; color: #334155; flex: 1; }
.driver-bar-bg {
    flex: 2;
    height: 6px;
    background: #f1f5f9;
    border-radius: 3px;
    overflow: hidden;
}
.driver-bar-fill { height: 100%; border-radius: 3px; }
.driver-val { font-size: 11px; font-weight: 700; min-width: 70px; text-align: left; }

/* ═══════════════════════════════════════════
   NOTE BARS (deduction/compensation notes)
═══════════════════════════════════════════ */
.note-row {
    padding: 6px 14px;
    border-bottom: 1px solid #f8fafc;
    display: flex;
    align-items: center;
    gap: 8px;
}
.note-row:last-child { border-bottom: none; }
.note-label { font-size: 11px; color: #475569; flex: 1; }
.note-bar-bg { flex: 2; height: 5px; background: #f1f5f9; border-radius: 3px; overflow: hidden; }
.note-bar-fill { height: 100%; border-radius: 3px; }
.note-val { font-size: 11px; font-weight: 700; min-width: 60px; text-align: left; }

/* ═══════════════════════════════════════════
   TABLES
═══════════════════════════════════════════ */
.pdf-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 12px;
}
.pdf-table thead th {
    background: #1e3a5f;
    color: #fff;
    padding: 9px 12px;
    font-weight: 700;
    white-space: nowrap;
    text-align: right;
}
.pdf-table thead th:last-child { text-align: left; }
.pdf-table tbody td {
    padding: 8px 12px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}
.pdf-table tbody tr:nth-child(even) { background: #f8fafc; }
.pdf-table tbody tr:hover { background: #eff6ff; }
.pdf-table tfoot td {
    padding: 9px 12px;
    background: #1e3a5f;
    color: #fff;
    font-weight: 700;
}
.text-success-p { color: #16a34a; font-weight: 700; }
.text-danger-p  { color: #dc2626; font-weight: 700; }
.text-info-p    { color: #0284c7; font-weight: 700; }
.text-purple-p  { color: #7c3aed; font-weight: 700; }
.text-center    { text-align: center; }
.text-left      { text-align: left; }

/* ═══════════════════════════════════════════
   CHEFZ SUMMARY TILES
═══════════════════════════════════════════ */
.tile-row { display: grid; grid-template-columns: repeat(3, 1fr); gap: 12px; }
.tile {
    border-radius: 12px;
    padding: 16px;
    text-align: center;
    border: 1px solid #e2e8f0;
}
.tile-val { font-size: 20px; font-weight: 700; }
.tile-lbl { font-size: 11px; color: #64748b; margin-top: 5px; }

/* ═══════════════════════════════════════════
   PAGE BREAK CONTROLS
═══════════════════════════════════════════ */
.page-break { page-break-before: always; break-before: always; }
.avoid-break { page-break-inside: avoid; break-inside: avoid; }

/* ═══════════════════════════════════════════
   LOADING OVERLAY
═══════════════════════════════════════════ */
#pdf-overlay {
    position: fixed;
    inset: 0;
    background: linear-gradient(135deg, #1e3a5f, #1e40af);
    color: #fff;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    z-index: 9999;
    font-family: 'Arial', sans-serif;
}
.spinner {
    width: 48px; height: 48px;
    border: 4px solid rgba(255,255,255,.3);
    border-top-color: #fff;
    border-radius: 50%;
    animation: spin 1s linear infinite;
    margin-bottom: 20px;
}
@keyframes spin { to { transform: rotate(360deg); } }
#pdf-overlay h3 { font-size: 20px; margin-bottom: 8px; }
#pdf-overlay p  { font-size: 13px; opacity: .8; }
#btn-manual-dl  { margin-top: 20px; padding: 10px 24px; background: rgba(255,255,255,.2); border: 1px solid rgba(255,255,255,.5); border-radius: 8px; color: #fff; cursor: pointer; font-size: 14px; display: none; }
</style>
</head>
<body>

{{-- ── Loading overlay ── --}}
<div id="pdf-overlay">
    <div class="spinner"></div>
    <h3>جاري إنشاء التقرير PDF</h3>
    <p>يُرجى الانتظار — يتم تحويل المخططات وضغط التقرير...</p>
    <button id="btn-manual-dl" onclick="triggerDownload()">تحميل التقرير يدوياً</button>
</div>

{{-- ════════════════════════════════════════════════════
     PDF CONTENT
════════════════════════════════════════════════════ --}}
<div id="pdf-content">

    {{-- ── Report Header ── --}}
    <div class="rpt-header">
        <div class="rpt-header-brand">
            <div class="rpt-logo-box">📊</div>
            <div class="rpt-brand-text">
                <h1>التقرير المالي الشهري</h1>
                <p>{{ $period->platform?->name }} — نظام إدارة المناديب</p>
            </div>
        </div>
        <div class="rpt-header-meta">
            <strong>{{ $period->getDisplayLabel() }}</strong>
            <div>تاريخ التقرير: {{ now()->format('Y-m-d') }}</div>
            <div>وقت الإنشاء: {{ now()->format('H:i:s') }}</div>
            <div style="margin-top:6px;">
                <span class="rpt-period-badge">
                    {{ $period->isClosed() ? 'مغلقة' : 'مفتوحة' }}
                </span>
            </div>
        </div>
    </div>

    {{-- ── Financial Summary Strip ── --}}
    <div class="pdf-section avoid-break">
        <div class="fin-strip">
            <div class="fin-strip-item">
                <div class="fin-strip-val">{{ number_format($kpis['totalOrders']) }}</div>
                <div class="fin-strip-lbl">إجمالي الطلبات</div>
            </div>
            <div class="fin-strip-item">
                <div class="fin-strip-val">{{ number_format($platformCode === 'hungerstation' ? $kpis['basicPayment'] : $kpis['grossFees'], 2) }}</div>
                <div class="fin-strip-lbl">{{ $platformCode === 'hungerstation' ? 'Basic Payment (ريال)' : 'رسوم التوصيل الإجمالية (ريال)' }}</div>
            </div>
            <div class="fin-strip-item">
                <div class="fin-strip-val {{ $kpis['netProfit'] >= 0 ? 'text-success-p' : 'text-danger-p' }}">
                    {{ ($kpis['netProfit'] >= 0 ? '+' : '') . number_format($kpis['netProfit'], 2) }}
                </div>
                <div class="fin-strip-lbl">الصافي النهائي (ريال)</div>
            </div>
        </div>
    </div>

    {{-- ════════════════════════════════════════
         KPI CARDS
    ════════════════════════════════════════ --}}
    <div class="pdf-section avoid-break">
        <div class="section-title">
            <span class="section-title-dot" style="background:#6366f1;"></span>
            مؤشرات الأداء الرئيسية
        </div>
        <div class="kpi-grid">

            <div class="kpi-card g-purple avoid-break">
                <div class="kpi-icon-wrap">🛒</div>
                <div class="kpi-num">{{ number_format($kpis['totalOrders']) }}</div>
                <div class="kpi-lbl">إجمالي الطلبات</div>
                <div class="kpi-sub">متوسط {{ $kpis['avgOrders'] }}/مندوب</div>
            </div>

            <div class="kpi-card g-teal avoid-break">
                <div class="kpi-icon-wrap">💰</div>
                <div class="kpi-num">{{ number_format($platformCode === 'hungerstation' ? $kpis['basicPayment'] : $kpis['grossFees'], 0) }}</div>
                <div class="kpi-lbl">{{ $platformCode === 'hungerstation' ? 'Basic Payment (هنقرستيشن)' : 'رسوم التوصيل الإجمالية' }}</div>
                <div class="kpi-sub">ريال سعودي</div>
            </div>

            @if($platformCode === 'hungerstation')
            <div class="kpi-card g-red avoid-break">
                <div class="kpi-icon-wrap">↓</div>
                <div class="kpi-num">{{ number_format($kpis['penaltiesTotal'], 0) }}</div>
                <div class="kpi-lbl">غرامات المنصة (هنقرستيشن)</div>
                <div class="kpi-sub">من RLVL</div>
            </div>

            <div class="kpi-card g-amber avoid-break">
                <div class="kpi-icon-wrap">🛡</div>
                <div class="kpi-num">{{ number_format($kpis['stackingTotal'], 0) }}</div>
                <div class="kpi-lbl">Stacking (تمتصه الشركة)</div>
                <div class="kpi-sub">مستبعد من راتب المندوب</div>
            </div>

            <div class="kpi-card g-amber avoid-break">
                <div class="kpi-icon-wrap">🏢</div>
                <div class="kpi-num">{{ number_format($kpis['companyExpenses'], 0) }}</div>
                <div class="kpi-lbl">مصروفات الشركة</div>
                <div class="kpi-sub">{{ $expenses->count() }} بند</div>
            </div>
            @endif

            @if($platformCode === 'the-chefz')
            <div class="kpi-card g-red avoid-break">
                <div class="kpi-icon-wrap">%</div>
                <div class="kpi-num">{{ number_format($kpis['vatTotal'], 0) }}</div>
                <div class="kpi-lbl">ضريبة القيمة المضافة</div>
                <div class="kpi-sub">15%</div>
            </div>

            <div class="kpi-card g-pink avoid-break">
                <div class="kpi-icon-wrap">🏢</div>
                <div class="kpi-num">{{ number_format($kpis['commissionTotal'], 0) }}</div>
                <div class="kpi-lbl">حصة الشركة</div>
                <div class="kpi-sub">12%</div>
            </div>
            @endif

            <div class="kpi-card g-green avoid-break">
                <div class="kpi-icon-wrap">📈</div>
                <div class="kpi-num">{{ number_format($kpis['netRevenue'], 0) }}</div>
                <div class="kpi-lbl">الإيراد الصافي</div>
                <div class="kpi-sub">بعد الخصومات</div>
            </div>

            <div class="kpi-card g-navy avoid-break">
                <div class="kpi-icon-wrap">👥</div>
                <div class="kpi-num">{{ number_format($kpis['netSalaries'], 0) }}</div>
                <div class="kpi-lbl">صافي رواتب المناديب</div>
                <div class="kpi-sub">متوسط {{ number_format($kpis['avgSalary'], 0) }} ر/مندوب</div>
            </div>

            <div class="kpi-card {{ $kpis['netProfit'] >= 0 ? 'g-profit' : 'g-loss' }} avoid-break">
                <div class="kpi-icon-wrap">{{ $kpis['netProfit'] >= 0 ? '✓' : '✗' }}</div>
                <div class="kpi-num">{{ number_format(abs($kpis['netProfit']), 0) }}</div>
                <div class="kpi-lbl">{{ $kpis['netProfit'] >= 0 ? 'صافي الربح' : 'صافي الخسارة' }}</div>
                <div class="kpi-sub">بعد الرواتب والمصروفات</div>
            </div>

            <div class="kpi-card g-slate avoid-break">
                <div class="kpi-icon-wrap">🪪</div>
                <div class="kpi-num">{{ number_format($kpis['totalDrivers']) }}</div>
                <div class="kpi-lbl">عدد المناديب</div>
                <div class="kpi-sub">هذه الفترة</div>
            </div>

        </div>
    </div>

    {{-- ════════════════════════════════════════
         DAILY TREND CHARTS
    ════════════════════════════════════════ --}}
    @if($dailyData->isNotEmpty())
    <div class="pdf-section avoid-break">
        <div class="section-title">
            <span class="section-title-dot" style="background:#0d9488;"></span>
            الاتجاه اليومي للطلبات والإيرادات
        </div>
        <div class="chart-box avoid-break">
            <div class="chart-box-head">
                <span style="width:10px;height:10px;background:#6366f1;border-radius:50%;display:inline-block;"></span>
                الطلبات اليومية ورسوم التوصيل — {{ $dailyData->count() }} يوم
            </div>
            <div class="chart-box-body">
                <div class="chart-canvas-wrap">
                    <canvas id="chartDailyMain" height="200"></canvas>
                </div>
            </div>
        </div>
    </div>

    @if($platformCode === 'hungerstation')
    <div class="pdf-section avoid-break">
        <div class="section-title">
            <span class="section-title-dot" style="background:#f59e0b;"></span>
            الاتجاه اليومي للخصومات والتعويضات والصافي
        </div>
        <div class="chart-box avoid-break">
            <div class="chart-box-head">
                <span style="width:10px;height:10px;background:#f59e0b;border-radius:50%;display:inline-block;"></span>
                الخصومات · التعويضات · الصافي اليومي
            </div>
            <div class="chart-box-body">
                <div class="chart-canvas-wrap">
                    <canvas id="chartDailyDeductions" height="180"></canvas>
                </div>
            </div>
        </div>
    </div>
    @endif
    @endif

    {{-- ════════════════════════════════════════
         DISTRIBUTION CHARTS
    ════════════════════════════════════════ --}}
    <div class="pdf-section avoid-break">
        <div class="section-title">
            <span class="section-title-dot" style="background:#ec4899;"></span>
            توزيع الإيرادات والمصروفات
        </div>
        <div class="chart-row chart-row-8-4">
            <div class="chart-box avoid-break">
                <div class="chart-box-head">
                    <span style="width:10px;height:10px;background:#6366f1;border-radius:50%;display:inline-block;"></span>
                    توزيع الإيرادات — ملخص مالي
                </div>
                <div class="chart-box-body">
                    <div class="chart-canvas-wrap">
                        <canvas id="chartRevenueDonut" height="210"></canvas>
                    </div>
                </div>
            </div>
            @if($platformCode === 'the-chefz')
            <div class="chart-box avoid-break">
                <div class="chart-box-head">
                    <span style="width:10px;height:10px;background:#ec4899;border-radius:50%;display:inline-block;"></span>
                    توزيع شيفز
                </div>
                <div class="chart-box-body">
                    <div class="chart-canvas-wrap">
                        <canvas id="chartChefzDonut" height="210"></canvas>
                    </div>
                </div>
            </div>
            @elseif($deductionsByType->isNotEmpty())
            <div class="chart-box avoid-break">
                <div class="chart-box-head">
                    <span style="width:10px;height:10px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
                    الخصومات اليدوية — حسب النوع
                </div>
                <div class="chart-box-body">
                    <div class="chart-canvas-wrap">
                        <canvas id="chartDeductionTypes" height="210"></canvas>
                    </div>
                </div>
            </div>
            @else
            <div></div>
            @endif
        </div>
    </div>

    {{-- ════════════════════════════════════════
         TOP DRIVERS
    ════════════════════════════════════════ --}}
    @if($topBySalary->isNotEmpty() || $topByOrders->isNotEmpty())
    <div class="pdf-section">
        <div class="section-title">
            <span class="section-title-dot" style="background:#10b981;"></span>
            أعلى المناديب أداءً
        </div>
        <div class="chart-row chart-row-2">

            @if($topBySalary->isNotEmpty())
            <div class="chart-box avoid-break">
                <div class="chart-box-head">
                    <span style="width:10px;height:10px;background:#10b981;border-radius:50%;display:inline-block;"></span>
                    أعلى 10 مناديب — صافي الراتب
                </div>
                @foreach($topBySalary as $i => $s)
                <div class="driver-row">
                    <div class="driver-rank">{{ $i+1 }}</div>
                    <div class="driver-name">{{ Str::limit($s->delegate?->name ?? '—', 18) }}</div>
                    <div class="driver-bar-bg">
                        <div class="driver-bar-fill"
                             style="width: {{ $maxSalary > 0 ? min(100, round((float)$s->net_salary / $maxSalary * 100)) : 0 }}%;
                                    background: linear-gradient(90deg, #10b981, #06b6d4);">
                        </div>
                    </div>
                    <div class="driver-val text-success-p">{{ number_format($s->net_salary, 0) }}</div>
                </div>
                @endforeach
            </div>
            @endif

            @if($topByOrders->isNotEmpty())
            <div class="chart-box avoid-break">
                <div class="chart-box-head">
                    <span style="width:10px;height:10px;background:#6366f1;border-radius:50%;display:inline-block;"></span>
                    أعلى 10 مناديب — عدد الطلبات
                </div>
                @foreach($topByOrders as $i => $s)
                <div class="driver-row">
                    <div class="driver-rank">{{ $i+1 }}</div>
                    <div class="driver-name">{{ Str::limit($s->delegate?->name ?? '—', 18) }}</div>
                    <div class="driver-bar-bg">
                        <div class="driver-bar-fill"
                             style="width: {{ $maxOrders > 0 ? min(100, round((int)$s->total_orders / $maxOrders * 100)) : 0 }}%;
                                    background: linear-gradient(90deg, #6366f1, #8b5cf6);">
                        </div>
                    </div>
                    <div class="driver-val text-purple-p">{{ number_format($s->total_orders) }}</div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         HS: DEDUCTIONS + COMPENSATIONS NOTES
    ════════════════════════════════════════ --}}
    @if($platformCode === 'hungerstation' && ($deductionNotes->isNotEmpty() || $compensationNotes->isNotEmpty()))
    <div class="pdf-section">
        <div class="section-title">
            <span class="section-title-dot" style="background:#f97316;"></span>
            تحليل أسباب الخصومات والتعويضات
        </div>
        <div class="chart-row chart-row-2">

            @if($deductionNotes->isNotEmpty())
            <div class="chart-box avoid-break">
                <div class="chart-box-head">
                    <span style="width:10px;height:10px;background:#ef4444;border-radius:50%;display:inline-block;"></span>
                    أسباب خصومات المنصة
                </div>
                @php $maxDN = $deductionNotes->max('total') ?: 1; @endphp
                @foreach($deductionNotes as $dn)
                <div class="note-row">
                    <div class="note-label">{{ $dn->note ?: 'غير محدد' }}</div>
                    <div class="note-bar-bg">
                        <div class="note-bar-fill"
                             style="width: {{ min(100, round($dn->total / $maxDN * 100)) }}%;
                                    background: linear-gradient(90deg, #ef4444, #f97316);">
                        </div>
                    </div>
                    <div class="note-val text-danger-p">{{ number_format($dn->total, 0) }}</div>
                </div>
                @endforeach
            </div>
            @endif

            @if($compensationNotes->isNotEmpty())
            <div class="chart-box avoid-break">
                <div class="chart-box-head">
                    <span style="width:10px;height:10px;background:#0d9488;border-radius:50%;display:inline-block;"></span>
                    أسباب تعويضات المنصة
                </div>
                @php $maxCN = $compensationNotes->max('total') ?: 1; @endphp
                @foreach($compensationNotes as $cn)
                <div class="note-row">
                    <div class="note-label">{{ $cn->note ?: 'غير محدد' }}</div>
                    <div class="note-bar-bg">
                        <div class="note-bar-fill"
                             style="width: {{ min(100, round($cn->total / $maxCN * 100)) }}%;
                                    background: linear-gradient(90deg, #0d9488, #0ea5e9);">
                        </div>
                    </div>
                    <div class="note-val text-info-p">{{ number_format($cn->total, 0) }}</div>
                </div>
                @endforeach
            </div>
            @endif

        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         HS: EXPENSES TABLE
    ════════════════════════════════════════ --}}
    @if($platformCode === 'hungerstation' && $expenses->isNotEmpty())
    <div class="pdf-section avoid-break">
        <div class="section-title">
            <span class="section-title-dot" style="background:#f59e0b;"></span>
            مصروفات الشركة
            <span style="margin-right:auto;font-size:12px;color:#dc2626;font-weight:700;">
                الإجمالي: {{ number_format($expenses->sum('amount'), 2) }} ريال
            </span>
        </div>
        <div class="chart-box">
            <table class="pdf-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>الفئة</th>
                        <th class="text-left">المبلغ (ريال)</th>
                        <th>ملاحظات</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($expenses as $i => $exp)
                    <tr>
                        <td class="text-center" style="color:#64748b;font-size:11px;">{{ $i+1 }}</td>
                        <td><strong>{{ $exp->category }}</strong></td>
                        <td class="text-left text-danger-p">{{ number_format($exp->amount, 2) }}</td>
                        <td style="color:#64748b;font-size:11px;">{{ $exp->notes ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">الإجمالي</td>
                        <td class="text-left">{{ number_format($expenses->sum('amount'), 2) }} ريال</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ════════════════════════════════════════
         FULL SETTLEMENTS TABLE
    ════════════════════════════════════════ --}}
    @if($settlements->isNotEmpty())
    <div class="pdf-section">
        <div class="section-title">
            <span class="section-title-dot" style="background:#64748b;"></span>
            جدول تسويات المناديب الكامل
            <span style="margin-right:auto;font-size:12px;color:#64748b;">{{ $settlements->count() }} مندوب</span>
        </div>
        <div class="chart-box">
            <table class="pdf-table" style="width:100%;">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>المندوب</th>
                        <th class="text-center">الطلبات</th>
                        <th class="text-left">رسوم التوصيل</th>
                        @if($platformCode === 'hungerstation')
                            <th class="text-left">خصم المنصة</th>
                            <th class="text-left">تعويض المنصة</th>
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
                        <td class="text-center" style="color:#64748b;font-size:11px;">{{ $i+1 }}</td>
                        <td><strong style="font-size:12px;">{{ $s->delegate?->name ?? '—' }}</strong></td>
                        <td class="text-center">{{ number_format($s->total_orders) }}</td>
                        <td class="text-left">{{ number_format($platformCode === 'hungerstation' ? $s->basic_payment : $s->gross_delivery_fees, 2) }}</td>
                        @if($platformCode === 'hungerstation')
                            <td class="text-left text-success-p">{{ number_format($s->distance_payment, 2) }}</td>
                            <td class="text-left text-danger-p">{{ number_format($s->total_platform_penalties, 2) }}</td>
                        @else
                            <td class="text-left text-danger-p">{{ number_format($s->chefz_tax_amount, 2) }}</td>
                            <td class="text-left text-purple-p">{{ number_format($s->company_share_amount, 2) }}</td>
                        @endif
                        <td class="text-left {{ $s->net_salary >= 0 ? 'text-success-p' : 'text-danger-p' }}">
                            {{ number_format($s->net_salary, 2) }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
                <tfoot>
                    <tr>
                        <td colspan="2">الإجمالي</td>
                        <td class="text-center">{{ number_format($kpis['totalOrders']) }}</td>
                        <td class="text-left">{{ number_format($platformCode === 'hungerstation' ? $kpis['basicPayment'] : $kpis['grossFees'], 2) }}</td>
                        @if($platformCode === 'hungerstation')
                            <td class="text-left">{{ number_format($kpis['distancePayment'], 2) }}</td>
                            <td class="text-left">{{ number_format($kpis['penaltiesTotal'], 2) }}</td>
                        @else
                            <td class="text-left">{{ number_format($kpis['vatTotal'], 2) }}</td>
                            <td class="text-left">{{ number_format($kpis['commissionTotal'], 2) }}</td>
                        @endif
                        <td class="text-left">{{ number_format($kpis['netSalaries'], 2) }} ريال</td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
    @endif

    {{-- ── Report Footer ── --}}
    <div style="background:#1e3a5f;padding:14px 36px;display:flex;align-items:center;justify-content:space-between;color:rgba(255,255,255,.7);font-size:11px;">
        <span>{{ $period->platform?->name }} — {{ $period->getDisplayLabel() }}</span>
        <span>التقرير المالي الشهري · تم الإنشاء في {{ now()->format('Y-m-d H:i') }}</span>
        <span id="page-info">صفحة 1</span>
    </div>

</div>{{-- #pdf-content --}}

{{-- ════════════════════════════════════════
     SCRIPTS
════════════════════════════════════════ --}}
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>

<script>
(function () {
    'use strict';

    // Disable all chart animations for immediate render
    Chart.defaults.animation = false;
    Chart.defaults.font.family = "'Arial', 'Tahoma', sans-serif";
    Chart.defaults.font.size   = 11;
    Chart.defaults.color       = '#475569';

    var COLORS = ['#6366f1','#0d9488','#ef4444','#3b82f6','#f59e0b','#ec4899','#10b981','#f97316','#64748b','#8b5cf6'];

    // ── Data from PHP ──
    var dailyData = @json($dailyData);
    var platformCode = '{{ $platformCode }}';

    // ── Chart instances (tracked for canvas conversion) ──
    var chartInstances = [];

    function makeChart(id, config) {
        var el = document.getElementById(id);
        if (!el) return null;
        var c = new Chart(el, config);
        chartInstances.push(c);
        return c;
    }

    // ── Daily Trends ──
    if (dailyData.length > 0) {
        var labels  = dailyData.map(function(d) { return d.date; });
        var orders  = dailyData.map(function(d) { return parseInt(d.orders); });
        var revenue = dailyData.map(function(d) { return parseFloat(d.revenue); });

        makeChart('chartDailyMain', {
            type: 'line',
            data: {
                labels: labels,
                datasets: [
                    {
                        label: 'الطلبات',
                        data: orders,
                        borderColor: '#6366f1',
                        backgroundColor: 'rgba(99,102,241,.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        yAxisID: 'yOrders',
                    },
                    {
                        label: 'رسوم التوصيل (ريال)',
                        data: revenue,
                        borderColor: '#0d9488',
                        backgroundColor: 'rgba(13,148,136,.08)',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 3,
                        yAxisID: 'yRevenue',
                    }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { maxRotation: 45, font: { size: 10 } } },
                    yOrders: {
                        type: 'linear', position: 'right', beginAtZero: true,
                        grid: { drawOnChartArea: false },
                        title: { display: true, text: 'طلبات', font: { size: 10 } }
                    },
                    yRevenue: {
                        type: 'linear', position: 'left', beginAtZero: true,
                        grid: { color: 'rgba(0,0,0,.04)' },
                        title: { display: true, text: 'ريال', font: { size: 10 } }
                    }
                }
            }
        });

        @if($platformCode === 'hungerstation')
        var deductions    = dailyData.map(function(d) { return parseFloat(d.deductions); });
        var compensations = dailyData.map(function(d) { return parseFloat(d.compensations); });
        var net           = dailyData.map(function(d) { return parseFloat(d.net); });

        makeChart('chartDailyDeductions', {
            type: 'bar',
            data: {
                labels: labels,
                datasets: [
                    { label: 'الخصومات', data: deductions, backgroundColor: 'rgba(239,68,68,.65)', borderRadius: 3, order: 1 },
                    { label: 'التعويضات', data: compensations, backgroundColor: 'rgba(6,182,212,.65)', borderRadius: 3, order: 1 },
                    { label: 'الصافي اليومي', data: net, type: 'line', borderColor: '#10b981', backgroundColor: 'transparent', fill: false, tension: 0.4, pointRadius: 2, order: 0 }
                ]
            },
            options: {
                responsive: true,
                interaction: { mode: 'index', intersect: false },
                plugins: { legend: { position: 'top' } },
                scales: {
                    x: { grid: { color: 'rgba(0,0,0,.04)' }, ticks: { maxRotation: 45, font: { size: 10 } } },
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,.04)' } }
                }
            }
        });
        @endif
    }

    // ── Revenue Distribution Donut ──
    @if($platformCode === 'hungerstation')
    var donutData = [
        { label: 'Basic Payment', value: {{ round($kpis['basicPayment'], 2) }} },
        { label: 'غرامات المنصة', value: {{ round($kpis['penaltiesTotal'], 2) }} },
        { label: 'Stacking (شركة)', value: {{ round($kpis['stackingTotal'], 2) }} },
        { label: 'صافي الرواتب', value: {{ round($kpis['netSalaries'], 2) }} },
        { label: 'مصروفات الشركة', value: {{ round($kpis['companyExpenses'], 2) }} },
    ].filter(function(d){ return d.value > 0; });
    @else
    var donutData = [
        { label: 'رسوم التوصيل', value: {{ round($kpis['grossFees'], 2) }} },
        { label: 'ضريبة القيمة المضافة', value: {{ round($kpis['vatTotal'], 2) }} },
        { label: 'حصة الشركة', value: {{ round($kpis['commissionTotal'], 2) }} },
        { label: 'صافي الرواتب', value: {{ round($kpis['netSalaries'], 2) }} },
    ].filter(function(d){ return d.value > 0; });
    @endif

    makeChart('chartRevenueDonut', {
        type: 'doughnut',
        data: {
            labels: donutData.map(function(d){ return d.label; }),
            datasets: [{
                data: donutData.map(function(d){ return d.value; }),
                backgroundColor: COLORS,
                borderWidth: 2,
                borderColor: '#fff',
                hoverOffset: 4,
            }]
        },
        options: {
            responsive: true,
            cutout: '55%',
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { font: { size: 10 }, padding: 8, usePointStyle: true }
                },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            return ' ' + ctx.label + ': ' + ctx.parsed.toLocaleString('ar-SA', {minimumFractionDigits: 2}) + ' ر';
                        }
                    }
                }
            }
        }
    });

    @if($platformCode === 'the-chefz')
    makeChart('chartChefzDonut', {
        type: 'doughnut',
        data: {
            labels: ['صافي الرواتب', 'ضريبة القيمة المضافة', 'حصة الشركة'],
            datasets: [{
                data: [{{ round($kpis['netSalaries'], 2) }}, {{ round($kpis['vatTotal'], 2) }}, {{ round($kpis['commissionTotal'], 2) }}],
                backgroundColor: ['#6366f1', '#ec4899', '#f59e0b'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 8, usePointStyle: true } }
            }
        }
    });
    @endif

    @if($platformCode === 'hungerstation' && $deductionsByType->isNotEmpty())
    var dedTypes = @json($deductionsByType->values());
    makeChart('chartDeductionTypes', {
        type: 'doughnut',
        data: {
            labels: dedTypes.map(function(d){ return d.label; }),
            datasets: [{
                data: dedTypes.map(function(d){ return d.value; }),
                backgroundColor: ['#ef4444','#f97316','#f59e0b','#ec4899','#8b5cf6','#6366f1','#0d9488','#3b82f6','#64748b'],
                borderWidth: 2,
                borderColor: '#fff',
            }]
        },
        options: {
            responsive: true,
            cutout: '55%',
            plugins: {
                legend: { position: 'bottom', labels: { font: { size: 10 }, padding: 6, usePointStyle: true } }
            }
        }
    });
    @endif

    // ════════════════════════════════════════
    // CANVAS → IMAGE → PDF pipeline
    // ════════════════════════════════════════

    function convertCanvasesToImages() {
        document.querySelectorAll('#pdf-content canvas').forEach(function(canvas) {
            var dataURL = canvas.toDataURL('image/png', 1.0);
            var img = new Image();
            img.src = dataURL;
            img.style.width  = '100%';
            img.style.height = 'auto';
            img.style.display = 'block';
            img.style.borderRadius = '4px';
            canvas.parentNode.replaceChild(img, canvas);
        });
    }

    var pdfWorker = null;

    function triggerDownload() {
        document.getElementById('btn-manual-dl').style.display = 'none';
        if (pdfWorker) {
            pdfWorker.save();
        }
    }

    function generatePDF() {
        var filename = 'تقرير-مالي-{{ $period->platform?->name }}-{{ $period->getDisplayLabel() }}.pdf'
                        .replace(/\s+/g, '-');

        var opt = {
            margin:      [8, 6, 12, 6],
            filename:    filename,
            image:       { type: 'jpeg', quality: 0.96 },
            html2canvas: {
                scale:       1.6,
                useCORS:     true,
                logging:     false,
                allowTaint:  false,
                letterRendering: true,
            },
            jsPDF: {
                unit:        'mm',
                format:      'a4',
                orientation: 'portrait',
                compress:    true,
            },
            pagebreak: {
                mode: ['avoid-all', 'css'],
                before: '.page-break',
                avoid:  ['.avoid-break', '.kpi-card', '.chart-box'],
            },
        };

        pdfWorker = html2pdf().set(opt).from(document.getElementById('pdf-content'));

        pdfWorker
            .toPdf()
            .get('pdf')
            .then(function(pdf) {
                var totalPages = pdf.internal.getNumberOfPages();
                var pageW = pdf.internal.pageSize.getWidth();
                var pageH = pdf.internal.pageSize.getHeight();

                for (var i = 1; i <= totalPages; i++) {
                    pdf.setPage(i);

                    // Footer bar
                    pdf.setFillColor(30, 58, 95);
                    pdf.rect(0, pageH - 10, pageW, 10, 'F');

                    // Page number text
                    pdf.setTextColor(255, 255, 255);
                    pdf.setFontSize(8);
                    pdf.text(
                        'صفحة ' + i + ' من ' + totalPages,
                        pageW / 2,
                        pageH - 3.5,
                        { align: 'center' }
                    );

                    // Platform + period on left
                    pdf.text(
                        '{{ $period->platform?->name }} — {{ $period->getDisplayLabel() }}',
                        pageW - 6,
                        pageH - 3.5,
                        { align: 'right' }
                    );

                    // Date on right (left side in RTL)
                    pdf.text(
                        '{{ now()->format("Y-m-d") }}',
                        6,
                        pageH - 3.5,
                        { align: 'left' }
                    );
                }
            })
            .save()
            .then(function() {
                // Hide overlay after download triggers
                setTimeout(function() {
                    document.getElementById('pdf-overlay').style.display = 'none';
                }, 1000);
            })
            .catch(function(err) {
                console.error('PDF error:', err);
                document.getElementById('btn-manual-dl').style.display = 'inline-block';
            });
    }

    // ── Boot sequence ──
    window.addEventListener('load', function() {
        // Show manual download button as fallback after 12s
        setTimeout(function() {
            document.getElementById('btn-manual-dl').style.display = 'inline-block';
        }, 12000);

        // Wait for chart animations to settle (animation=false, so 600ms is plenty)
        setTimeout(function() {
            convertCanvasesToImages();
            // Small pause to let image replacements settle in DOM
            setTimeout(generatePDF, 400);
        }, 700);
    });

})();
</script>

</body>
</html>
