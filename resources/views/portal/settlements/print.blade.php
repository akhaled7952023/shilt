@php
    $isRtl  = app()->getLocale() === 'ar';
    $locale = app()->getLocale();
    $dir    = $isRtl ? 'rtl' : 'ltr';
    $thStart = $isRtl ? 'right' : 'left';
    $thEnd   = $isRtl ? 'left'  : 'right';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('portal.print_doc_type_hs') }} — {{ $period->getDisplayLabel() }} — {{ $delegate->name }}</title>
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Tajawal:wght@400;500;700;800&display=swap">
<style>
* { box-sizing: border-box; margin: 0; padding: 0; }

body {
    font-family: 'Tajawal', 'Arial', sans-serif;
    direction: {{ $dir }};
    color: #0f172a;
    font-size: 14px;
    line-height: 1.5;
    background: #f1f5f9;
}

.print-wrapper {
    max-width: 800px;
    margin: 0 auto;
    background: white;
    min-height: 100vh;
}

/* ── Print action bar (hidden on print) ── */
.print-bar {
    background: #1e40af;
    padding: 10px 24px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 12px;
}

.print-bar-text { color: rgba(255,255,255,.8); font-size: 13px; }

.print-btn {
    background: white;
    color: #1e40af;
    border: none;
    border-radius: 8px;
    padding: 9px 20px;
    font-family: 'Tajawal', sans-serif;
    font-size: 14px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 7px;
}

.back-link {
    color: rgba(255,255,255,.8);
    text-decoration: none;
    font-size: 13px;
    display: flex;
    align-items: center;
    gap: 5px;
}

.back-link:hover { color: white; }

/* ── Document ── */
.doc {
    padding: 36px 40px 48px;
}

/* ── Header ── */
.doc-header {
    display: flex;
    align-items: flex-start;
    justify-content: space-between;
    gap: 20px;
    margin-bottom: 28px;
    padding-bottom: 20px;
    border-bottom: 2px solid #1e40af;
}

.doc-company-side {
    display: flex;
    align-items: flex-start;
    gap: 14px;
    flex: 1;
}

.doc-logo {
    width: 60px;
    height: 60px;
    object-fit: contain;
    border-radius: 8px;
    flex-shrink: 0;
}

.doc-company-name {
    font-size: 21px;
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 2px;
    line-height: 1.2;
}

.doc-company-name-en {
    font-size: 12px;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 6px;
}

.doc-company-meta {
    font-size: 11px;
    color: #64748b;
    line-height: 1.8;
}

.doc-right-side {
    text-align: {{ $thEnd }};
    flex-shrink: 0;
}

.doc-doc-type {
    font-size: 13px;
    font-weight: 700;
    color: #0f172a;
    margin-bottom: 4px;
}

.doc-period-label {
    font-size: 20px;
    font-weight: 800;
    color: #1e40af;
    margin-bottom: 4px;
}

.doc-period-sub {
    font-size: 12px;
    color: #64748b;
    margin-bottom: 2px;
}

.status-pill {
    display: inline-block;
    padding: 2px 10px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 600;
    margin-top: 4px;
}

.status-published { background: #dcfce7; color: #15803d; }
.status-closed    { background: #dbeafe; color: #1d4ed8; }

/* ── Info grid ── */
.info-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 0;
    border: 1px solid #e2e8f0;
    border-radius: 8px;
    overflow: hidden;
    margin-bottom: 28px;
}

.info-cell {
    padding: 10px 16px;
    border-{{ $isRtl ? 'left' : 'right' }}: 1px solid #e2e8f0;
    border-bottom: 1px solid #e2e8f0;
}

.info-cell:last-child, .info-cell:nth-last-child(2):not(:nth-last-child(1)) { border-bottom: none; }

.info-cell.full-width {
    grid-column: 1 / -1;
    border-{{ $isRtl ? 'left' : 'right' }}: none;
    border-bottom: none;
}

.info-key {
    font-size: 10px;
    font-weight: 700;
    color: #94a3b8;
    text-transform: uppercase;
    letter-spacing: .3px;
    margin-bottom: 2px;
}

.info-val {
    font-size: 14px;
    font-weight: 700;
    color: #0f172a;
}

/* ── Section title ── */
.sec-title {
    font-size: 11px;
    font-weight: 700;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: .5px;
    margin-bottom: 8px;
    margin-top: 22px;
}

/* ── Financial table ── */
.fin-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 13.5px;
}

.fin-table th {
    background: #1e40af;
    color: white;
    padding: 9px 14px;
    font-weight: 700;
    font-size: 12px;
    text-align: {{ $thStart }};
}

.fin-table th:last-child { text-align: {{ $thEnd }}; }

.fin-table td {
    padding: 9px 14px;
    border-bottom: 1px solid #f1f5f9;
    color: #334155;
}

.fin-table tr:last-child td { border-bottom: none; }
.fin-table tr:nth-child(even) td { background: #f8fafc; }

.fin-table td:last-child {
    font-weight: 700;
    text-align: {{ $thEnd }};
    font-variant-numeric: tabular-nums;
}

.cred-row td:last-child { color: #15803d; }
.ded-row  td:last-child { color: #dc2626; }

.subtotal-row td {
    background: #e0f2fe !important;
    color: #0369a1;
    font-weight: 700;
    border-top: 1px solid #bae6fd;
}

.total-row td {
    background: #1e40af !important;
    color: white !important;
    font-weight: 700;
    font-size: 14px;
    border-top: 2px solid #1d4ed8;
}

.net-row td {
    background: #15803d !important;
    color: white !important;
    font-weight: 800;
    font-size: 16px;
    border-top: 2px solid #16a34a;
}

/* ── Summary box ── */
.summary-box {
    border: 2px solid #15803d;
    border-radius: 10px;
    padding: 20px 24px;
    margin-top: 28px;
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 16px;
    text-align: center;
}

.summary-item-label { font-size: 11px; color: #64748b; font-weight: 600; margin-bottom: 4px; }
.summary-item-val   { font-size: 18px; font-weight: 800; font-variant-numeric: tabular-nums; }
.summary-net { color: #15803d; font-size: 24px; }

/* ── Signatures ── */
.signatures {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 24px;
    margin-top: 48px;
}

.sig-box {
    border-top: 1.5px solid #e2e8f0;
    padding-top: 10px;
    text-align: center;
}

.sig-label { font-size: 12px; color: #64748b; }

/* ── Footer ── */
.doc-footer {
    margin-top: 36px;
    padding-top: 16px;
    border-top: 1px solid #e2e8f0;
    text-align: center;
    font-size: 11px;
    color: #94a3b8;
    line-height: 1.8;
}

/* ── @media print ── */
@media print {
    body { background: white; }
    .print-bar { display: none !important; }
    .print-wrapper { max-width: none; }
    .doc { padding: 20px 28px 32px; }

    @page {
        size: A4;
        margin: 1.5cm 1.8cm;
    }

    .fin-table, .info-grid, .summary-box, .signatures {
        page-break-inside: avoid;
    }

    .fin-table th,
    .total-row td,
    .net-row td,
    .subtotal-row td,
    .doc-header { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

@php
    $isHs      = $platformCode === 'hungerstation';
    $platLabel = $isHs ? __('portal.platform_hungerstation') : __('portal.platform_chefz');

    $stLabel = match($period->status->value) {
        'closed'    => __('portal.status_paid'),
        'published' => __('portal.status_approved'),
        default     => __('portal.status_under_review'),
    };
    $stClass = match($period->status->value) {
        'closed'    => 'status-closed',
        'published' => 'status-published',
        default     => '',
    };

    // Helper: treat '—' and empty as "not configured"
    $hasLogo    = !empty($companyLogo);
    $hasCr      = !empty($companyCr)      && $companyCr      !== '—';
    $hasAddress = !empty($companyAddress) && $companyAddress !== '—';
    $hasPhone   = !empty($companyPhone)   && $companyPhone   !== '—';
    $hasNameEn  = !empty($companyNameEn);

    $logoUrl = $hasLogo
        ? \Illuminate\Support\Facades\Storage::url($companyLogo)
        : null;
@endphp

<div class="print-wrapper">

    {{-- ── Action bar (screen only) ── --}}
    <div class="print-bar">
        <a href="{{ route('portal.settlements.show', $period->id) }}" class="back-link">
            {{ __('portal.print_back_settlement') }}
        </a>
        <span class="print-bar-text">{{ __('portal.print_save_hint') }}</span>
        <button class="print-btn" onclick="window.print()">
            {{ __('portal.print_btn_label') }}
        </button>
    </div>

    {{-- ── Document ── --}}
    <div class="doc">

        {{-- Header --}}
        <div class="doc-header">
            <div class="doc-company-side">
                @if($logoUrl)
                    <img src="{{ $logoUrl }}" alt="{{ $companyName }}" class="doc-logo">
                @endif
                <div>
                    <div class="doc-company-name">{{ $companyName }}</div>
                    @if($hasNameEn)
                        <div class="doc-company-name-en">{{ $companyNameEn }}</div>
                    @endif
                    <div class="doc-company-meta">
                        @if($hasCr)
                            <span>{{ __('portal.print_cr_prefix') }} {{ $companyCr }}</span>
                        @endif
                        @if($hasCr && ($hasAddress || $hasPhone))
                            <span style="margin: 0 6px;">·</span>
                        @endif
                        @if($hasAddress)
                            <span>{{ $companyAddress }}</span>
                        @endif
                        @if($hasPhone)
                            @if($hasAddress || $hasCr)<br>@endif
                            <span>{{ __('portal.print_phone_prefix') }} {{ $companyPhone }}</span>
                        @endif
                    </div>
                </div>
            </div>

            <div class="doc-right-side">
                <div class="doc-doc-type">{{ __('portal.print_doc_type_hs') }}</div>
                <div class="doc-period-label">{{ $period->getDisplayLabel() }}</div>
                <div class="doc-period-sub">{{ __('portal.print_platform_label', ['platform' => $platLabel]) }}</div>
                @if($period->published_at)
                    <div class="doc-period-sub">
                        {{ __('portal.print_published_date', ['date' => \Carbon\Carbon::parse($period->published_at)->format('Y/m/d')]) }}
                    </div>
                @endif
                <span class="status-pill {{ $stClass }}">{{ $stLabel }}</span>
            </div>
        </div>

        {{-- Delegate Info --}}
        <div class="info-grid">
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_name') }}</div>
                <div class="info-val">{{ $delegate->name }}</div>
            </div>
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_code') }}</div>
                <div class="info-val" style="font-family:monospace;">{{ $delegate->delegate_code }}</div>
            </div>
            @if($delegate->national_id)
                <div class="info-cell">
                    <div class="info-key">{{ __('portal.print_info_national') }}</div>
                    <div class="info-val" style="font-family:monospace;">{{ $delegate->national_id }}</div>
                </div>
            @endif
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_platform') }}</div>
                <div class="info-val">{{ $platLabel }}</div>
            </div>
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_region') }}</div>
                <div class="info-val">{{ $delegate->city?->name ?? '—' }}</div>
            </div>
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_period') }}</div>
                <div class="info-val">{{ $period->getDisplayLabel() }}</div>
            </div>
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_issue_date') }}</div>
                <div class="info-val">{{ now()->format('Y/m/d') }}</div>
            </div>
        </div>

        @if($isHs)
            @php
                $benefitEntries   = $settlement->deductions->where('is_benefit', true);
                $deductionEntries = $settlement->deductions->where('is_benefit', false);
                $totalEarnings    = (float)$settlement->distance_payment + (float)$settlement->company_benefits_total;
                $totalDeductions  = (float)$settlement->total_platform_penalties
                                  + (float)$settlement->rider_balance
                                  + (float)$settlement->company_deductions_total;
            @endphp

            {{-- ── HungerStation FTR: Earnings ── --}}
            <div class="sec-title">{{ __('portal.print_entitlements_sec') }}</div>
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>{{ __('portal.print_table_item') }}</th>
                        <th>{{ __('portal.print_table_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('portal.print_orders_row') }}</td>
                        <td>{{ number_format($settlement->total_orders) }} {{ __('portal.print_orders_unit') }}</td>
                    </tr>
                    @if($settlement->working_days !== null)
                    <tr>
                        <td>{{ __('portal.print_working_days_row') }}</td>
                        <td>{{ $settlement->working_days }} {{ __('portal.print_working_days_unit') }}</td>
                    </tr>
                    @endif
                    <tr class="cred-row">
                        <td>{{ __('portal.base_salary_distance') }}</td>
                        <td>{{ number_format((float)$settlement->distance_payment, 2) }}</td>
                    </tr>
                    @foreach($benefitEntries as $ben)
                        <tr class="cred-row">
                            <td>{{ $ben->getTypeLabel() }}</td>
                            <td>{{ number_format((float)$ben->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="subtotal-row">
                        <td>{{ __('portal.print_total_entitlements') }}</td>
                        <td>{{ number_format($totalEarnings, 2) }}</td>
                    </tr>
                </tbody>
            </table>

            {{-- ── HungerStation FTR: Deductions ── --}}
            <div class="sec-title">{{ __('portal.print_deductions_sec') }}</div>
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>{{ __('portal.print_table_item') }}</th>
                        <th>{{ __('portal.print_table_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @if((float)$settlement->total_platform_penalties > 0)
                        <tr class="ded-row">
                            <td>{{ __('portal.platform_penalties') }}</td>
                            <td>{{ number_format((float)$settlement->total_platform_penalties, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->rider_balance > 0)
                        <tr class="ded-row">
                            <td>{{ __('portal.wallet_recovery') }}</td>
                            <td>{{ number_format((float)$settlement->rider_balance, 2) }}</td>
                        </tr>
                    @endif
                    @foreach($deductionEntries as $ded)
                        <tr class="ded-row">
                            <td>{{ $ded->getTypeLabel() }}</td>
                            <td>{{ number_format((float)$ded->amount, 2) }}</td>
                        </tr>
                        @if(!empty($ded->notes))
                            <tr>
                                <td colspan="2" style="font-size:10px;color:#64748b;direction:rtl;text-align:right;padding-top:0;padding-bottom:6px;padding-right:8px;">
                                    <span style="font-weight:600;">سبب الخصم:</span> {{ $ded->notes }}
                                </td>
                            </tr>
                        @endif
                    @endforeach
                    @if($deductionEntries->isEmpty() && (float)$settlement->total_platform_penalties == 0 && (float)$settlement->rider_balance == 0)
                        <tr>
                            <td colspan="2" style="text-align:center;color:#64748b;padding:16px;">
                                {{ __('portal.print_no_deductions') }}
                            </td>
                        </tr>
                    @endif
                    <tr class="subtotal-row">
                        <td>{{ __('portal.print_total_deductions') }}</td>
                        <td>{{ number_format($totalDeductions, 2) }}</td>
                    </tr>
                </tbody>
            </table>

        @else
            {{-- ── The Chefz: Formula Breakdown ── --}}
            <div class="sec-title">{{ __('portal.print_calc_cz') }}</div>
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>{{ __('portal.print_table_item') }}</th>
                        <th>{{ __('portal.print_table_amount') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('portal.print_orders_row') }}</td>
                        <td>{{ number_format($settlement->total_orders) }} {{ __('portal.print_orders_unit') }}</td>
                    </tr>
                    <tr class="cred-row">
                        <td>{{ __('portal.delivery_fees_label') }}</td>
                        <td>{{ number_format((float)$settlement->gross_delivery_fees, 2) }}</td>
                    </tr>
                    @if((float)$settlement->platform_compensations > 0)
                        <tr class="cred-row">
                            <td>{{ __('portal.platform_compensations_lbl') }}</td>
                            <td>{{ number_format((float)$settlement->platform_compensations, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->chefz_tax_amount > 0)
                        <tr class="ded-row">
                            <td>
                                {{ __('portal.vat_label') }}
                                ({{ number_format((float)$settlement->chefz_tax_rate * 100, 1) }}%)
                            </td>
                            <td>{{ number_format((float)$settlement->chefz_tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->company_share_amount > 0)
                        <tr class="ded-row">
                            <td>
                                {{ __('portal.company_share_label') }}
                                ({{ number_format((float)$settlement->company_share_rate * 100, 1) }}%)
                            </td>
                            <td>{{ number_format((float)$settlement->company_share_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->platform_deductions > 0)
                        <tr class="ded-row">
                            <td>{{ __('portal.deductions_section') }}</td>
                            <td>{{ number_format((float)$settlement->platform_deductions, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="subtotal-row">
                        <td>{{ __('portal.print_total_deductions') }}</td>
                        <td>{{ number_format((float)$settlement->deductions_total, 2) }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        {{-- ── Net Summary ── --}}
        @php
            $summaryEarnings   = $isHs
                ? (float)$settlement->distance_payment + (float)$settlement->company_benefits_total
                : (float)($settlement->commission_total ?? 0);
            $summaryDeductions = $isHs
                ? (float)$settlement->total_platform_penalties + (float)$settlement->rider_balance + (float)$settlement->company_deductions_total
                : (float)($settlement->deductions_total ?? 0);
        @endphp
        <div class="sec-title">{{ __('portal.print_financial_summary') }}</div>
        <table class="fin-table">
            <tbody>
                <tr class="cred-row">
                    <td>{{ __('portal.print_total_entitlements') }}</td>
                    <td>{{ number_format($summaryEarnings, 2) }}</td>
                </tr>
                <tr class="ded-row">
                    <td>{{ __('portal.print_total_deductions') }}</td>
                    <td>{{ number_format($summaryDeductions, 2) }}</td>
                </tr>
                <tr class="net-row">
                    <td>{{ __('portal.print_net_salary_due') }}</td>
                    <td>{{ number_format((float)$settlement->net_salary, 2) }} {{ __('portal.currency_sar_short') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- ── Signatures ── --}}
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-label" style="margin-bottom:32px;">{{ __('portal.print_signature_delegate') }}</div>
                <div class="sig-label">{{ $delegate->name }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-label" style="margin-bottom:32px;">{{ __('portal.print_signature_manager') }}</div>
                <div class="sig-label">{{ $companyName }}</div>
            </div>
        </div>

        {{-- ── Footer ── --}}
        <div class="doc-footer">
            <div>{{ $companyName }}@if($hasNameEn) · {{ $companyNameEn }}@endif</div>
            @if($hasCr)
                <div>{{ __('portal.print_cr_full', ['cr' => $companyCr]) }}</div>
            @endif
            <div style="margin-top:4px;color:#b8c3d6;">
                {{ $period->getDisplayLabel() }} — {{ __('portal.print_platform_label', ['platform' => $platLabel]) }} — {{ __('portal.print_print_date', ['date' => now()->format('Y/m/d H:i')]) }}
            </div>
        </div>

    </div>
</div>

<script>
window.addEventListener('load', function () {
    setTimeout(function () {
        window.print();
    }, 600);
});
</script>
</body>
</html>
