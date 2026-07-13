<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>التقرير التنفيذي الشامل — {{ $filters['date_from'] }} إلى {{ $filters['date_to'] }}</title>
<script src="https://cdn.jsdelivr.net/npm/chart.js@3.9.1/dist/chart.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
<style>
* { box-sizing:border-box; margin:0; padding:0; }
body { font-family:'Arial','Tahoma',sans-serif; background:#fff; direction:rtl; color:#1e293b; font-size:11px; }

/* ── Loading overlay ── */
#pdf-overlay {
    position:fixed; inset:0; background:rgba(30,58,95,.92);
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    z-index:9999; gap:18px;
}
.spinner {
    width:48px; height:48px; border:4px solid rgba(255,255,255,.3);
    border-top-color:#fff; border-radius:50%; animation:spin .9s linear infinite;
}
@keyframes spin { to { transform:rotate(360deg); } }
.overlay-txt { color:#fff; font-size:15px; font-weight:600; text-align:center; }
.overlay-sub { color:rgba(255,255,255,.7); font-size:12px; text-align:center; }
#btn-manual-dl {
    display:none; margin-top:10px; padding:10px 24px;
    background:#fff; color:#1e40af; border:none; border-radius:8px;
    font-size:13px; font-weight:700; cursor:pointer;
}

/* ── Page/section ── */
#pdf-content { width:210mm; margin:0 auto; background:#fff; padding:10mm 12mm 14mm; }

/* ── Header ── */
.pdf-header {
    background:linear-gradient(135deg,#1e3a5f 0%,#1e40af 60%,#312e81 100%);
    border-radius:12px; padding:22px 24px; margin-bottom:16px;
    display:flex; align-items:center; justify-content:space-between;
}
.pdf-logo-box {
    width:52px; height:52px; border-radius:12px;
    background:rgba(255,255,255,.2); display:flex; align-items:center;
    justify-content:center; font-size:26px; flex-shrink:0; margin-left:16px;
}
.pdf-header-text { flex:1; }
.pdf-header-text h1 { color:#fff; font-size:18px; font-weight:700; margin-bottom:4px; }
.pdf-header-text p  { color:rgba(255,255,255,.75); font-size:11px; }
.pdf-header-meta { text-align:left; }
.pdf-meta-chip {
    display:inline-block; background:rgba(255,255,255,.15); color:#fff;
    padding:3px 10px; border-radius:20px; font-size:10px; margin-bottom:4px;
    white-space:nowrap;
}

/* ── KPI strip ── */
.pdf-kpi-strip {
    display:grid; grid-template-columns:repeat(4,1fr); gap:10px; margin-bottom:14px;
}
.pdf-kpi {
    background:#f8fafc; border-right:3px solid var(--c,#6366f1);
    border-radius:8px; padding:10px 12px;
}
.pdf-kpi-num { font-size:15px; font-weight:700; color:var(--c,#6366f1); }
.pdf-kpi-lbl { font-size:9px; color:#64748b; margin-top:2px; }

.pdf-kpi-strip-2 {
    display:grid; grid-template-columns:repeat(5,1fr); gap:8px; margin-bottom:14px;
}
.pdf-kpi-sm { background:#f8fafc; border-radius:8px; padding:8px 10px; text-align:center; border-top:3px solid var(--c,#94a3b8); }
.pdf-kpi-sm-num { font-size:13px; font-weight:700; color:var(--c,#64748b); }
.pdf-kpi-sm-lbl { font-size:8.5px; color:#94a3b8; margin-top:2px; }

/* ── Section head ── */
.pdf-section-head {
    display:flex; align-items:center; gap:8px;
    margin-bottom:10px; margin-top:16px;
    border-bottom:2px solid #f1f5f9; padding-bottom:6px;
}
.pdf-section-head h2 { font-size:13px; font-weight:700; color:#1e3a5f; }
.pdf-section-dot { width:8px; height:8px; border-radius:50%; flex-shrink:0; }

/* ── Chart grid ── */
.pdf-chart-row { display:grid; gap:10px; margin-bottom:14px; }
.pdf-chart-row-2 { grid-template-columns:1fr 1fr; }
.pdf-chart-row-3 { grid-template-columns:1fr 1fr 1fr; }
.pdf-chart-box {
    background:#f8fafc; border-radius:10px; padding:10px 12px;
    page-break-inside:avoid;
}
.pdf-chart-box h4 { font-size:10px; font-weight:700; color:#475569; margin-bottom:8px; }
.pdf-chart-box canvas { display:block; max-height:160px; }

/* ── Top lists ── */
.pdf-top-row {
    display:flex; align-items:center; gap:8px;
    padding:5px 8px; border-bottom:1px solid #f1f5f9;
}
.pdf-top-rank {
    width:18px; height:18px; border-radius:50%; background:#f1f5f9;
    color:#64748b; font-size:9px; font-weight:700;
    display:flex; align-items:center; justify-content:center; flex-shrink:0;
}
.pdf-top-rank-1 { background:#fef3c7; color:#b45309; }
.pdf-top-rank-2 { background:#e5e7eb; color:#374151; }
.pdf-top-rank-3 { background:#fde8d8; color:#c2410c; }
.pdf-top-name { flex:1; font-size:10px; font-weight:600; color:#334155; overflow:hidden; white-space:nowrap; text-overflow:ellipsis; }
.pdf-top-bar { flex:2; height:4px; background:#e2e8f0; border-radius:2px; overflow:hidden; }
.pdf-top-fill { height:100%; border-radius:2px; }
.pdf-top-val { font-size:10px; font-weight:700; min-width:60px; text-align:left; }
.plat-pill { font-size:8px; padding:1px 4px; border-radius:3px; font-weight:600; flex-shrink:0; }
.plat-hs-p { background:#fff7ed; color:#c2410c; }
.plat-cz-p { background:#f0fdf4; color:#15803d; }

/* ── Platform compare table ── */
.pdf-cmp-table { width:100%; border-collapse:collapse; font-size:10px; margin-bottom:14px; }
.pdf-cmp-table thead th {
    background:#1e3a5f; color:#fff; padding:7px 10px; font-weight:700; text-align:center;
}
.pdf-cmp-table tbody td { padding:6px 10px; border-bottom:1px solid #f1f5f9; text-align:center; }
.pdf-cmp-table tbody tr:nth-child(even) { background:#f8fafc; }
.pdf-num-pos { color:#16a34a; font-weight:700; }
.pdf-num-neg { color:#dc2626; font-weight:700; }

/* ── Detail table ── */
.pdf-det-table { width:100%; border-collapse:collapse; font-size:9px; page-break-inside:auto; }
.pdf-det-table thead th {
    background:#1e3a5f; color:#fff; padding:6px 8px; font-weight:700; white-space:nowrap; text-align:right;
}
.pdf-det-table tbody td { padding:5px 8px; border-bottom:1px solid #f1f5f9; white-space:nowrap; }
.pdf-det-table tbody tr:nth-child(even) { background:#f8fafc; }
.pdf-det-table tfoot td {
    background:#1e3a5f; color:#fff; font-weight:700; padding:6px 8px;
}

/* ── Avoid page break inside ── */
.avoid-break { page-break-inside:avoid; }
</style>
</head>
<body>
@php
    $isAll = $filters['platform'] === 'all';
    $isHs  = in_array($filters['platform'], ['all', 'hungerstation']);
    $isCz  = in_array($filters['platform'], ['all', 'the-chefz']);
@endphp

{{-- Overlay --}}
<div id="pdf-overlay">
    <div class="spinner"></div>
    <div class="overlay-txt">جارٍ إنشاء التقرير التنفيذي…</div>
    <div class="overlay-sub">يرجى الانتظار، قد تستغرق العملية بضع ثوانٍ</div>
    <button id="btn-manual-dl">تحميل يدوي</button>
</div>

<div id="pdf-content">

    {{-- ── Header ── --}}
    <div class="pdf-header">
        <div class="pdf-logo-box">📊</div>
        <div class="pdf-header-text">
            <h1>التقرير التنفيذي الشامل</h1>
            <p>منصة إدارة المناديب — شركة التوصيل</p>
            <p style="margin-top:4px;">
                الفترة: <strong style="color:#fff;">{{ $filters['date_from'] }}</strong>
                إلى
                <strong style="color:#fff;">{{ $filters['date_to'] }}</strong>
                @if($filters['platform'] !== 'all')
                    · المنصة: <strong style="color:#fff;">{{ $filters['platform'] === 'hungerstation' ? 'هنقرستيشن' : 'شيفز' }}</strong>
                @endif
            </p>
        </div>
        <div class="pdf-header-meta">
            <div class="pdf-meta-chip">{{ $periodIds->count() }} فترة شهرية</div>
            <br>
            <div class="pdf-meta-chip">{{ now()->format('Y-m-d H:i') }}</div>
        </div>
    </div>

    {{-- ── KPI Strip 1 (main) ── --}}
    <div class="pdf-kpi-strip avoid-break">
        <div class="pdf-kpi" style="--c:#6366f1;">
            <div class="pdf-kpi-num">{{ number_format($kpis['totalOrders']) }}</div>
            <div class="pdf-kpi-lbl">إجمالي الطلبات</div>
        </div>
        <div class="pdf-kpi" style="--c:#0d9488;">
            <div class="pdf-kpi-num">{{ number_format($kpis['grossFees'],0) }}</div>
            <div class="pdf-kpi-lbl">{{ $isHs && !$isCz ? 'الدفعة الأساسية FTR (ريال)' : ($isCz && !$isHs ? 'رسوم التوصيل (ريال)' : 'إجمالي الإيرادات (ريال)') }}</div>
        </div>
        <div class="pdf-kpi" style="--c:#7c3aed;">
            <div class="pdf-kpi-num">{{ number_format($kpis['totalSalaries'],0) }}</div>
            <div class="pdf-kpi-lbl">إجمالي الرواتب (ريال)</div>
        </div>
        <div class="pdf-kpi" style="--c:{{ $kpis['netProfit'] >= 0 ? '#16a34a' : '#dc2626' }};">
            <div class="pdf-kpi-num">{{ ($kpis['netProfit']>=0?'+':'') . number_format($kpis['netProfit'],0) }}</div>
            <div class="pdf-kpi-lbl">صافي الربح (ريال)</div>
        </div>
    </div>

    {{-- ── KPI Strip 2 (per-platform profits + secondary) ── --}}
    <div class="pdf-kpi-strip-2 avoid-break">
        <div class="pdf-kpi-sm" style="--c:#16a34a;">
            <div class="pdf-kpi-sm-num">{{ number_format($kpis['hsProfit'],0) }}</div>
            <div class="pdf-kpi-sm-lbl">ربح هنقرستيشن</div>
        </div>
        <div class="pdf-kpi-sm" style="--c:#15803d;">
            <div class="pdf-kpi-sm-num">{{ number_format($kpis['czProfit'],0) }}</div>
            <div class="pdf-kpi-sm-lbl">ربح شيفز</div>
        </div>
        <div class="pdf-kpi-sm" style="--c:#be185d;">
            <div class="pdf-kpi-sm-num">{{ number_format($kpis['companyShare'],0) }}</div>
            <div class="pdf-kpi-sm-lbl">حصة الشركة (CZ)</div>
        </div>
        <div class="pdf-kpi-sm" style="--c:#ea580c;">
            <div class="pdf-kpi-sm-num">{{ number_format($kpis['vatTotal'],0) }}</div>
            <div class="pdf-kpi-sm-lbl">ضريبة شيفز</div>
        </div>
        <div class="pdf-kpi-sm" style="--c:#d97706;">
            <div class="pdf-kpi-sm-num">{{ number_format($kpis['compExp'],0) }}</div>
            <div class="pdf-kpi-sm-lbl">مصروفات الشركة (HS)</div>
        </div>
    </div>

    {{-- ── Charts: Daily Trend + Platform Donut ── --}}
    @if(!empty($dailyData['labels']))
    <div class="pdf-section-head">
        <div class="pdf-section-dot" style="background:#6366f1;"></div>
        <h2>الاتجاه اليومي وتوزيع المنصات</h2>
    </div>
    <div class="pdf-chart-row pdf-chart-row-2 avoid-break">
        <div class="pdf-chart-box">
            <h4>الاتجاه اليومي — الطلبات والإيرادات</h4>
            <canvas id="chartDaily"></canvas>
        </div>
        <div class="pdf-chart-box">
            <h4>توزيع المنصات — عدد الطلبات</h4>
            <canvas id="chartPlatformDonut"></canvas>
        </div>
    </div>
    @endif

    {{-- ── Charts: Top by Orders + Top by Salary ── --}}
    @if(!empty($topByOrders) || !empty($topBySalary))
    <div class="pdf-section-head">
        <div class="pdf-section-dot" style="background:#f59e0b;"></div>
        <h2>أعلى المناديب أداءً</h2>
    </div>
    <div class="pdf-chart-row pdf-chart-row-2 avoid-break">
        @if(!empty($topByOrders))
        <div class="pdf-chart-box">
            <h4>أعلى 10 مناديب — عدد الطلبات</h4>
            <canvas id="chartTopOrders"></canvas>
        </div>
        @endif
        @if(!empty($topBySalary))
        <div class="pdf-chart-box">
            <h4>أعلى 10 مناديب — صافي الراتب</h4>
            <canvas id="chartTopSalary"></canvas>
        </div>
        @endif
    </div>
    @endif

    {{-- ── Top lists textual ── --}}
    <div class="pdf-chart-row pdf-chart-row-2 avoid-break" style="margin-bottom:14px;">
        <div class="pdf-chart-box" style="background:#fff;border:1px solid #f1f5f9;">
            <h4 style="margin-bottom:6px;">🥇 أعلى 10 — الطلبات</h4>
            @if(!empty($topByOrders))
            @php $maxO = max(array_column($topByOrders,'orders')) ?: 1; @endphp
            @foreach(array_slice($topByOrders, 0, 10) as $i => $d)
            <div class="pdf-top-row">
                <div class="pdf-top-rank pdf-top-rank-{{ $i < 3 ? $i+1 : 'n' }}">{{ $i+1 }}</div>
                <span class="plat-pill {{ $d->platform === 'hungerstation' ? 'plat-hs-p' : 'plat-cz-p' }}">{{ $d->platform==='hungerstation'?'HS':'CZ' }}</span>
                <div class="pdf-top-name">{{ $d->name }}</div>
                <div class="pdf-top-bar"><div class="pdf-top-fill" style="width:{{ min(100,round($d->orders/$maxO*100)) }}%;background:#6366f1;"></div></div>
                <div class="pdf-top-val" style="color:#6366f1;">{{ number_format($d->orders) }}</div>
            </div>
            @endforeach
            @else <p style="font-size:10px;color:#94a3b8;padding:10px;">لا توجد بيانات</p>
            @endif
        </div>
        <div class="pdf-chart-box" style="background:#fff;border:1px solid #f1f5f9;">
            <h4 style="margin-bottom:6px;">💵 أعلى 10 — الراتب</h4>
            @if(!empty($topBySalary))
            @php $maxS = max(array_column($topBySalary,'salary')) ?: 1; @endphp
            @foreach(array_slice($topBySalary, 0, 10) as $i => $d)
            <div class="pdf-top-row">
                <div class="pdf-top-rank pdf-top-rank-{{ $i < 3 ? $i+1 : 'n' }}">{{ $i+1 }}</div>
                <span class="plat-pill {{ $d->platform === 'hungerstation' ? 'plat-hs-p' : 'plat-cz-p' }}">{{ $d->platform==='hungerstation'?'HS':'CZ' }}</span>
                <div class="pdf-top-name">{{ $d->name }}</div>
                <div class="pdf-top-bar"><div class="pdf-top-fill" style="width:{{ min(100,round($d->salary/$maxS*100)) }}%;background:#0d9488;"></div></div>
                <div class="pdf-top-val" style="color:#0d9488;">{{ number_format($d->salary,0) }}</div>
            </div>
            @endforeach
            @else <p style="font-size:10px;color:#94a3b8;padding:10px;">لا توجد بيانات</p>
            @endif
        </div>
    </div>

    {{-- ── Charts: Monthly + Deductions + Expenses ── --}}
    <div class="pdf-section-head">
        <div class="pdf-section-dot" style="background:#7c3aed;"></div>
        <h2>الاتجاه الشهري والتوزيعات</h2>
    </div>
    <div class="pdf-chart-row pdf-chart-row-3 avoid-break">
        @if(!empty($monthlyTrend['labels']))
        <div class="pdf-chart-box">
            <h4>الاتجاه الشهري (آخر 12 شهر)</h4>
            <canvas id="chartMonthly"></canvas>
        </div>
        @endif
        @if($deductionsByType->isNotEmpty())
        <div class="pdf-chart-box">
            <h4>الخصومات اليدوية — حسب النوع</h4>
            <canvas id="chartDedTypes"></canvas>
        </div>
        @endif
        @if($expensesByCategory->isNotEmpty())
        <div class="pdf-chart-box">
            <h4>مصروفات الشركة — حسب الفئة</h4>
            <canvas id="chartExpenses"></canvas>
        </div>
        @endif
    </div>

    {{-- ── Charts: Platform Compare + Region ── --}}
    <div class="pdf-chart-row pdf-chart-row-2 avoid-break">
        <div class="pdf-chart-box">
            <h4>مقارنة المنصات — الإيراد والرواتب</h4>
            <canvas id="chartPlatformCompare"></canvas>
        </div>
        @if($regionData->isNotEmpty())
        <div class="pdf-chart-box">
            <h4>الطلبات حسب المنطقة</h4>
            <canvas id="chartRegion"></canvas>
        </div>
        @else
        <div class="pdf-chart-box">
            <h4>ملخص مقارنة المنصات</h4>
            <table class="pdf-cmp-table" style="margin-top:6px;">
                <thead><tr><th>المنصة</th><th>الطلبات</th><th>الإيراد</th><th>الرواتب</th></tr></thead>
                <tbody>
                    <tr>
                        <td><strong>هنقرستيشن</strong></td>
                        <td>{{ number_format($platformCompare['hs']['orders']) }}</td>
                        <td>{{ number_format($platformCompare['hs']['revenue'],0) }}</td>
                        <td>{{ number_format($platformCompare['hs']['salary'],0) }}</td>
                    </tr>
                    <tr>
                        <td><strong>شيفز</strong></td>
                        <td>{{ number_format($platformCompare['cz']['orders']) }}</td>
                        <td>{{ number_format($platformCompare['cz']['revenue'],0) }}</td>
                        <td>{{ number_format($platformCompare['cz']['salary'],0) }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
        @endif
    </div>

    {{-- ── Detail Table (first 60 rows) ── --}}
    @if($table->isNotEmpty())
    <div class="pdf-section-head" style="margin-top:18px;">
        <div class="pdf-section-dot" style="background:#475569;"></div>
        <h2>الجدول التفصيلي — التسويات (أول 60 سجل)</h2>
    </div>
    <table class="pdf-det-table">
        <thead>
            <tr>
                <th>#</th>
                <th>الاسم</th>
                <th>Driver ID</th>
                <th>المنصة</th>
                <th>الفترة</th>
                <th>الطلبات</th>
                <th>الرسوم</th>
                <th>الخصومات</th>
                <th>التعويضات</th>
                <th>الضريبة</th>
                <th>حصة الشركة</th>
                <th>الراتب</th>
            </tr>
        </thead>
        <tbody>
            @foreach($table->take(60) as $i => $row)
            <tr>
                <td style="color:#94a3b8;">{{ $i+1 }}</td>
                <td><strong>{{ $row->name }}</strong></td>
                <td style="font-family:monospace;color:#64748b;">{{ $row->driver_id ?: '—' }}</td>
                <td>{{ $row->platform === 'hungerstation' ? 'HS' : 'CZ' }}</td>
                <td style="color:#64748b;">{{ $row->period }}</td>
                <td style="text-align:center;color:#0284c7;font-weight:700;">{{ number_format($row->orders) }}</td>
                <td>{{ number_format($row->fees,2) }}</td>
                <td class="{{ $row->deductions > 0 ? 'pdf-num-neg' : '' }}">{{ $row->deductions > 0 ? number_format($row->deductions,2) : '—' }}</td>
                <td class="{{ $row->comps > 0 ? 'pdf-num-pos' : '' }}">{{ $row->comps > 0 ? number_format($row->comps,2) : '—' }}</td>
                <td>{{ $row->vat > 0 ? number_format($row->vat,2) : '—' }}</td>
                <td class="{{ $row->company_share > 0 ? 'pdf-num-pos' : '' }}">{{ $row->company_share > 0 ? number_format($row->company_share,2) : '—' }}</td>
                <td class="{{ $row->salary >= 0 ? 'pdf-num-pos' : 'pdf-num-neg' }}">{{ number_format($row->salary,2) }}</td>
            </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr>
                <td colspan="5">الإجمالي (أول 60 سجل)</td>
                <td style="text-align:center;">{{ number_format($table->take(60)->sum('orders')) }}</td>
                <td>{{ number_format($table->take(60)->sum('fees'),2) }}</td>
                <td>{{ number_format($table->take(60)->sum('deductions'),2) }}</td>
                <td>{{ number_format($table->take(60)->sum('comps'),2) }}</td>
                <td>{{ number_format($table->take(60)->sum('vat'),2) }}</td>
                <td>{{ number_format($table->take(60)->sum('company_share'),2) }}</td>
                <td>{{ number_format($table->take(60)->sum('salary'),2) }}</td>
            </tr>
        </tfoot>
    </table>
    @if($table->count() > 60)
    <p style="font-size:9px;color:#94a3b8;margin-top:6px;text-align:center;">
        * يعرض التقرير أول 60 سجل فقط. إجمالي السجلات: {{ $table->count() }}
    </p>
    @endif
    @endif

    {{-- ── Report footer ── --}}
    <div style="margin-top:20px;padding:12px 16px;background:#1e3a5f;border-radius:10px;color:#fff;text-align:center;font-size:9px;">
        <strong>التقرير التنفيذي الشامل</strong> ·
        الفترة: {{ $filters['date_from'] }} — {{ $filters['date_to'] }} ·
        تاريخ الإنشاء: {{ now()->format('Y-m-d H:i:s') }}
    </div>

</div>{{-- #pdf-content --}}

<script>
Chart.defaults.animation  = false;
Chart.defaults.font.family = "'Arial','Tahoma',sans-serif";
Chart.defaults.font.size   = 9;
Chart.defaults.color       = '#475569';
Chart.defaults.plugins.legend.labels.usePointStyle = true;

var MULTI = ['#6366f1','#0d9488','#f59e0b','#ef4444','#3b82f6','#8b5cf6','#16a34a','#ea580c','#ec4899','#64748b','#06b6d4','#84cc16'];

function chartOpts(extra) {
    return Object.assign({
        responsive:true, maintainAspectRatio:true,
        plugins:{ legend:{ position:'bottom', labels:{padding:8,font:{size:9}} }, tooltip:{enabled:false} }
    }, extra||{});
}

/* ── Daily Trend ── */
@if(!empty($dailyData['labels']))
(function(){
    var dd = @json($dailyData);
    var tot = dd.hsOrders.map(function(v,i){return v+dd.czOrders[i];});
    var fees = dd.hsFees.map(function(v,i){return v+dd.czFees[i];});
    new Chart(document.getElementById('chartDaily'), {
        type:'bar',
        data:{
            labels:dd.labels,
            datasets:[
                {label:'HS طلبات',data:dd.hsOrders,backgroundColor:'rgba(249,115,22,.6)',borderRadius:2,order:1,yAxisID:'yO'},
                {label:'CZ طلبات',data:dd.czOrders,backgroundColor:'rgba(22,163,74,.6)',borderRadius:2,order:1,yAxisID:'yO'},
                {label:'الإيراد',data:fees,type:'line',borderColor:'#f59e0b',backgroundColor:'transparent',pointRadius:0,tension:.4,order:0,yAxisID:'yF'},
            ]
        },
        options: chartOpts({
            scales:{
                x:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:7},maxRotation:60,maxTicksLimit:15}},
                yO:{type:'linear',position:'right',beginAtZero:true,grid:{drawOnChartArea:false},ticks:{font:{size:7}}},
                yF:{type:'linear',position:'left',beginAtZero:true,grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:7}}}
            }
        })
    });
})();

/* ── Platform Donut ── */
(function(){
    var pc = @json($platformCompare);
    new Chart(document.getElementById('chartPlatformDonut'), {
        type:'doughnut',
        data:{labels:['هنقرستيشن','شيفز'],datasets:[{data:[pc.hs.orders,pc.cz.orders],backgroundColor:['#f97316','#16a34a'],borderWidth:2,borderColor:'#fff',hoverOffset:0}]},
        options: chartOpts({cutout:'58%'})
    });
})();
@endif

/* ── Top by Orders ── */
@if(!empty($topByOrders))
(function(){
    var td = @json(collect($topByOrders)->take(10)->values());
    new Chart(document.getElementById('chartTopOrders'), {
        type:'bar',
        data:{
            labels:td.map(function(d){return d.name.length>10?d.name.substr(0,10)+'…':d.name;}),
            datasets:[{label:'طلبات',data:td.map(function(d){return d.orders;}),backgroundColor:td.map(function(d){return d.platform==='hungerstation'?'rgba(249,115,22,.7)':'rgba(22,163,74,.7)';}),borderRadius:3}]
        },
        options:chartOpts({indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{font:{size:7}}},y:{ticks:{font:{size:8}},grid:{display:false}}}})
    });
})();
@endif

/* ── Top by Salary ── */
@if(!empty($topBySalary))
(function(){
    var sd = @json(collect($topBySalary)->take(10)->values());
    new Chart(document.getElementById('chartTopSalary'), {
        type:'bar',
        data:{
            labels:sd.map(function(d){return d.name.length>10?d.name.substr(0,10)+'…':d.name;}),
            datasets:[{label:'راتب',data:sd.map(function(d){return d.salary;}),backgroundColor:sd.map(function(d){return d.platform==='hungerstation'?'rgba(99,102,241,.7)':'rgba(13,148,136,.7)';}),borderRadius:3}]
        },
        options:chartOpts({indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,ticks:{font:{size:7}}},y:{ticks:{font:{size:8}},grid:{display:false}}}})
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
                {label:'الإيراد',data:mt.revenue,borderColor:'#0d9488',backgroundColor:'rgba(13,148,136,.08)',fill:true,tension:.4,pointRadius:2},
                {label:'الرواتب',data:mt.salaries,borderColor:'#6366f1',backgroundColor:'transparent',tension:.4,pointRadius:0},
                {label:'الربح',data:mt.profit,borderColor:'#f59e0b',backgroundColor:'transparent',tension:.4,borderDash:[4,3],pointRadius:0}
            ]
        },
        options:chartOpts({scales:{x:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:7}}},y:{beginAtZero:true,grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:7}}}}})
    });
})();
@endif

/* ── Deductions by Type ── */
@if($deductionsByType->isNotEmpty())
(function(){
    var dt = @json($deductionsByType->values());
    new Chart(document.getElementById('chartDedTypes'), {
        type:'doughnut',
        data:{labels:dt.map(function(d){return d.label;}),datasets:[{data:dt.map(function(d){return d.value;}),backgroundColor:MULTI,borderWidth:1,borderColor:'#fff',hoverOffset:0}]},
        options:chartOpts({cutout:'52%'})
    });
})();
@endif

/* ── Expenses by Category ── */
@if($expensesByCategory->isNotEmpty())
(function(){
    var ec = @json($expensesByCategory->values());
    new Chart(document.getElementById('chartExpenses'), {
        type:'doughnut',
        data:{labels:ec.map(function(d){return d.label;}),datasets:[{data:ec.map(function(d){return d.value;}),backgroundColor:['#d97706','#f59e0b','#fcd34d','#84cc16','#64748b','#0284c7'],borderWidth:1,borderColor:'#fff',hoverOffset:0}]},
        options:chartOpts({cutout:'52%'})
    });
})();
@endif

/* ── Platform Compare ── */
(function(){
    var pc = @json($platformCompare);
    new Chart(document.getElementById('chartPlatformCompare'), {
        type:'bar',
        data:{
            labels:['الطلبات','الإيراد','الرواتب'],
            datasets:[
                {label:'هنقرستيشن',data:[pc.hs.orders,pc.hs.revenue,pc.hs.salary],backgroundColor:'rgba(249,115,22,.7)',borderRadius:3},
                {label:'شيفز',data:[pc.cz.orders,pc.cz.revenue,pc.cz.salary],backgroundColor:'rgba(22,163,74,.7)',borderRadius:3}
            ]
        },
        options:chartOpts({scales:{x:{grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:8}}},y:{beginAtZero:true,grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:7}}}}})
    });
})();

/* ── Region ── */
@if($regionData->isNotEmpty())
(function(){
    var rd = @json($regionData);
    new Chart(document.getElementById('chartRegion'), {
        type:'bar',
        data:{
            labels:rd.map(function(d){return d.region;}),
            datasets:[{label:'طلبات',data:rd.map(function(d){return d.orders;}),backgroundColor:'rgba(22,163,74,.65)',borderRadius:3}]
        },
        options:chartOpts({indexAxis:'y',plugins:{legend:{display:false}},scales:{x:{beginAtZero:true,grid:{color:'rgba(0,0,0,.04)'},ticks:{font:{size:7}}},y:{ticks:{font:{size:8}},grid:{display:false}}}})
    });
})();
@endif

/* ── PDF pipeline ── */
function convertCanvasesToImages() {
    document.querySelectorAll('#pdf-content canvas').forEach(function(canvas) {
        var img = new Image();
        img.src = canvas.toDataURL('image/png', 1.0);
        img.style.width  = '100%';
        img.style.height = 'auto';
        img.style.display = 'block';
        canvas.parentNode.replaceChild(img, canvas);
    });
}

function generatePDF() {
    var d  = @json($filters);
    var fn = 'executive-report-' + d.date_from + '-to-' + d.date_to + '.pdf';

    var opt = {
        margin: [8, 6, 12, 6],
        filename: fn,
        image: { type: 'jpeg', quality: 0.96 },
        html2canvas: { scale: 1.5, useCORS: true, logging: false },
        jsPDF: { unit:'mm', format:'a4', orientation:'portrait', compress:true },
        pagebreak: { mode: ['avoid-all','css'], avoid: ['.avoid-break','.pdf-chart-box','.pdf-kpi'] },
    };

    html2pdf().set(opt).from(document.getElementById('pdf-content')).toPdf()
        .get('pdf').then(function(pdf) {
            var pw = pdf.internal.pageSize.getWidth();
            var ph = pdf.internal.pageSize.getHeight();
            var total = pdf.internal.getNumberOfPages();
            for (var i = 1; i <= total; i++) {
                pdf.setPage(i);
                pdf.setFillColor(30,58,95);
                pdf.rect(0, ph - 9, pw, 9, 'F');
                pdf.setTextColor(255,255,255);
                pdf.setFontSize(7.5);
                pdf.text('التقرير التنفيذي الشامل', 8, ph - 3);
                pdf.text('صفحة ' + i + ' من ' + total, pw / 2, ph - 3, { align: 'center' });
                pdf.text('{{ now()->format("Y-m-d") }}', pw - 8, ph - 3, { align: 'right' });
            }
        })
        .save()
        .then(function() {
            document.getElementById('pdf-overlay').style.display = 'none';
        });
}

document.getElementById('btn-manual-dl').addEventListener('click', function() {
    convertCanvasesToImages();
    setTimeout(generatePDF, 300);
});

window.addEventListener('load', function() {
    setTimeout(function() {
        document.getElementById('btn-manual-dl').style.display = 'inline-block';
    }, 12000);
    setTimeout(function() {
        convertCanvasesToImages();
        setTimeout(generatePDF, 500);
    }, 800);
});
</script>
</body>
</html>
