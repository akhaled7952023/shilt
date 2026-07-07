@php
    $isRtl   = app()->getLocale() === 'ar';
    $locale  = app()->getLocale();
    $dir     = $isRtl ? 'rtl' : 'ltr';
    $thStart = $isRtl ? 'right' : 'left';
    $thEnd   = $isRtl ? 'left'  : 'right';
@endphp
<!DOCTYPE html>
<html lang="{{ $locale }}" dir="{{ $dir }}">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>{{ __('portal.print_doc_type_cz') }} — {{ $period->getDisplayLabel() }} — {{ $delegate->name }}</title>
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
.print-wrapper { max-width: 800px; margin: 0 auto; background: white; min-height: 100vh; }
.print-bar { background: #7e22ce; padding: 10px 24px; display: flex; align-items: center; justify-content: space-between; gap: 12px; }
.print-bar-text { color: rgba(255,255,255,.8); font-size: 13px; }
.print-btn { background: white; color: #7e22ce; border: none; border-radius: 8px; padding: 9px 20px; font-family: 'Tajawal', sans-serif; font-size: 14px; font-weight: 700; cursor: pointer; }
.back-link { color: rgba(255,255,255,.8); text-decoration: none; font-size: 13px; }
.back-link:hover { color: white; }
.doc { padding: 36px 40px 48px; }

.doc-header { display: flex; align-items: flex-start; justify-content: space-between; gap: 20px; margin-bottom: 28px; padding-bottom: 20px; border-bottom: 2px solid #7e22ce; }
.doc-company-side { display: flex; align-items: flex-start; gap: 14px; flex: 1; }
.doc-logo { width: 60px; height: 60px; object-fit: contain; border-radius: 8px; flex-shrink: 0; }
.doc-company-name { font-size: 21px; font-weight: 800; color: #7e22ce; margin-bottom: 2px; line-height: 1.2; }
.doc-company-name-en { font-size: 12px; color: #64748b; font-weight: 500; margin-bottom: 6px; }
.doc-company-meta { font-size: 11px; color: #64748b; line-height: 1.8; }
.doc-right-side { text-align: {{ $thEnd }}; flex-shrink: 0; }
.doc-doc-type { font-size: 13px; font-weight: 700; color: #0f172a; margin-bottom: 4px; }
.doc-period-label { font-size: 20px; font-weight: 800; color: #7e22ce; margin-bottom: 4px; }
.doc-period-sub { font-size: 12px; color: #64748b; margin-bottom: 2px; }

.info-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 0; border: 1px solid #e2e8f0; border-radius: 8px; overflow: hidden; margin-bottom: 28px; }
.info-cell { padding: 10px 16px; border-{{ $isRtl ? 'left' : 'right' }}: 1px solid #e2e8f0; border-bottom: 1px solid #e2e8f0; }
.info-key { font-size: 10px; font-weight: 700; color: #94a3b8; text-transform: uppercase; letter-spacing: .3px; margin-bottom: 2px; }
.info-val { font-size: 14px; font-weight: 700; color: #0f172a; }
.sec-title { font-size: 11px; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: .5px; margin-bottom: 8px; margin-top: 22px; }
.fin-table { width: 100%; border-collapse: collapse; font-size: 13.5px; }
.fin-table th { background: #7e22ce; color: white; padding: 9px 14px; font-weight: 700; font-size: 12px; text-align: {{ $thStart }}; }
.fin-table th:last-child { text-align: {{ $thEnd }}; }
.fin-table td { padding: 9px 14px; border-bottom: 1px solid #f1f5f9; color: #334155; }
.fin-table tr:last-child td { border-bottom: none; }
.fin-table tr:nth-child(even) td { background: #f8fafc; }
.fin-table td:last-child { font-weight: 700; text-align: {{ $thEnd }}; font-variant-numeric: tabular-nums; }
.cred-row td:last-child { color: #15803d; }
.ded-row td:last-child { color: #dc2626; }
.subtotal-row td { background: #e0f2fe !important; color: #0369a1; font-weight: 700; border-top: 1px solid #bae6fd; }
.net-row td { background: #15803d !important; color: white !important; font-weight: 800; font-size: 16px; border-top: 2px solid #16a34a; }
.summary-box { border: 2px solid #15803d; border-radius: 10px; padding: 20px 24px; margin-top: 28px; display: grid; grid-template-columns: repeat(3, 1fr); gap: 16px; text-align: center; }
.summary-item-label { font-size: 11px; color: #64748b; font-weight: 600; margin-bottom: 4px; }
.summary-item-val { font-size: 18px; font-weight: 800; font-variant-numeric: tabular-nums; }
.summary-net { color: #15803d; font-size: 24px; }
.signatures { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; margin-top: 48px; }
.sig-box { border-top: 1.5px solid #e2e8f0; padding-top: 10px; text-align: center; }
.sig-label { font-size: 12px; color: #64748b; }
.doc-footer { text-align: center; font-size: 11px; color: #94a3b8; margin-top: 40px; padding-top: 16px; border-top: 1px solid #e2e8f0; line-height: 1.8; }
@media print {
    .print-bar { display: none; }
    body { background: white; }
    .doc { padding: 16px 20px 24px; }
    .print-wrapper { max-width: 100%; }
    .fin-table th, .net-row td, .subtotal-row td { -webkit-print-color-adjust: exact; print-color-adjust: exact; }
}
</style>
</head>
<body>

@php
    $payoutLabel = match($viewMode) {
        'payout1' => __('portal.payout_1_label'),
        'payout2' => __('portal.payout_2_label'),
        default   => __('portal.payout_total_label'),
    };
    $driverBase       = (float)$settlement->gross_delivery_fees - (float)$settlement->chefz_tax_amount;
    $subtotal         = (float)$settlement->commission_total;
    $manualDeductions = (float)$settlement->deductions_total - (float)$settlement->platform_deductions;
    $stLabel = match($period->status->value) {
        'closed'    => __('portal.status_paid'),
        'published' => __('portal.status_approved'),
        default     => __('portal.status_under_review'),
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

    {{-- Action bar (hidden on print) --}}
    <div class="print-bar">
        <a href="{{ route('portal.settlements.show', $period->id) }}" class="back-link">
            {{ __('portal.print_back_detail') }}
        </a>
        <span class="print-bar-text">{{ $period->getDisplayLabel() }} — {{ $payoutLabel }}</span>
        <button onclick="window.print()" class="print-btn">{{ __('portal.print_back_btn') }}</button>
    </div>

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
                <div class="doc-doc-type">{{ __('portal.print_doc_type_cz') }}</div>
                <div class="doc-period-label">{{ $period->getDisplayLabel() }}</div>
                <div class="doc-period-sub">{{ $payoutLabel }}</div>
                <div class="doc-period-sub">{{ $stLabel }}</div>
                <div class="doc-period-sub">{{ __('portal.print_print_date', ['date' => now()->format('Y-m-d')]) }}</div>
            </div>
        </div>

        {{-- Delegate info --}}
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
                <div class="info-val">{{ __('portal.platform_chefz') }}</div>
            </div>
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_period') }}</div>
                <div class="info-val">{{ $period->getDisplayLabel() }}</div>
            </div>
            <div class="info-cell">
                <div class="info-key">{{ __('portal.print_info_payout') }}</div>
                <div class="info-val">{{ $payoutLabel }}</div>
            </div>
        </div>

        {{-- Formula breakdown --}}
        <div class="sec-title">{{ __('portal.print_calc_cz') }}</div>
        <table class="fin-table">
            <thead>
                <tr>
                    <th>{{ __('portal.print_table_item') }}</th>
                    <th>{{ __('portal.print_table_amount') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr class="cred-row">
                    <td>{{ __('portal.gross_fees_incl_vat') }}</td>
                    <td>+ {{ number_format((float)$settlement->gross_delivery_fees, 2) }}</td>
                </tr>
                <tr class="ded-row">
                    <td>
                        {{ __('portal.vat_label') }}
                        ({{ number_format((float)$settlement->chefz_tax_rate * 100, 1) }}%)
                    </td>
                    <td>- {{ number_format((float)$settlement->chefz_tax_amount, 2) }}</td>
                </tr>
                <tr class="subtotal-row">
                    <td>{{ __('portal.driver_base_label') }}</td>
                    <td>{{ number_format($driverBase, 2) }}</td>
                </tr>
                @if ((float)$settlement->platform_compensations > 0)
                    <tr class="cred-row">
                        <td>{{ __('portal.platform_compensations_lbl') }}</td>
                        <td>+ {{ number_format((float)$settlement->platform_compensations, 2) }}</td>
                    </tr>
                @endif
                @if ((float)$settlement->positive_bonus > 0)
                    <tr class="cred-row">
                        <td>{{ __('portal.bonuses_label') }}</td>
                        <td>+ {{ number_format((float)$settlement->positive_bonus, 2) }}</td>
                    </tr>
                @endif
                @if ((float)$settlement->platform_compensations > 0 || (float)$settlement->positive_bonus > 0)
                    <tr class="subtotal-row">
                        <td>{{ __('portal.subtotal_before_share') }}</td>
                        <td>{{ number_format($subtotal, 2) }}</td>
                    </tr>
                @endif
                <tr class="ded-row">
                    <td>
                        {{ __('portal.company_share_label') }}
                        ({{ number_format((float)$settlement->company_share_rate * 100, 1) }}% {{ __('portal.of_total_label') }})
                    </td>
                    <td>- {{ number_format((float)$settlement->company_share_amount, 2) }}</td>
                </tr>
                @if ((float)$settlement->platform_deductions > 0)
                    <tr class="ded-row">
                        <td>{{ __('portal.deductions_section') }}</td>
                        <td>- {{ number_format((float)$settlement->platform_deductions, 2) }}</td>
                    </tr>
                @endif
                @if ($manualDeductions > 0)
                    <tr class="ded-row">
                        <td>{{ __('portal.deductions_label') }}</td>
                        <td>- {{ number_format($manualDeductions, 2) }}</td>
                    </tr>
                @endif
                <tr class="net-row">
                    <td>{{ __('portal.print_net_salary_due') }}</td>
                    <td>{{ number_format((float)$settlement->net_salary, 2) }} {{ __('portal.currency_sar_short') }}</td>
                </tr>
            </tbody>
        </table>

        {{-- Monthly comparison (total view only) --}}
        @if ($viewMode === 'total' && $payout1 && $payout2)
            <div class="sec-title">{{ __('portal.print_comparison_payouts') }}</div>
            <table class="fin-table">
                <thead>
                    <tr>
                        <th>{{ __('portal.print_table_item') }}</th>
                        <th>{{ __('portal.print_payout_1_col') }}</th>
                        <th>{{ __('portal.print_payout_2_col') }}</th>
                        <th>{{ __('portal.print_total_col') }}</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td>{{ __('portal.print_summary_orders') }}</td>
                        <td>{{ number_format($payout1->total_orders) }}</td>
                        <td>{{ number_format($payout2->total_orders) }}</td>
                        <td style="font-weight:700;">{{ number_format($settlement->total_orders) }}</td>
                    </tr>
                    <tr>
                        <td>{{ __('portal.print_summary_fees') }}</td>
                        <td>{{ number_format((float)$payout1->gross_delivery_fees, 2) }}</td>
                        <td>{{ number_format((float)$payout2->gross_delivery_fees, 2) }}</td>
                        <td style="font-weight:700;">{{ number_format((float)$settlement->gross_delivery_fees, 2) }}</td>
                    </tr>
                    <tr class="net-row">
                        <td>{{ __('portal.print_summary_net') }}</td>
                        <td>{{ number_format((float)$payout1->net_salary, 2) }}</td>
                        <td>{{ number_format((float)$payout2->net_salary, 2) }}</td>
                        <td>{{ number_format((float)$settlement->net_salary, 2) }} {{ __('portal.currency_sar_short') }}</td>
                    </tr>
                </tbody>
            </table>
        @endif

        {{-- Summary box --}}
        <div class="summary-box">
            <div>
                <div class="summary-item-label">{{ __('portal.print_summary_orders') }}</div>
                <div class="summary-item-val">{{ number_format($settlement->total_orders) }}</div>
            </div>
            <div>
                <div class="summary-item-label">{{ __('portal.print_summary_fees') }}</div>
                <div class="summary-item-val">{{ number_format((float)$settlement->gross_delivery_fees, 0) }}</div>
            </div>
            <div>
                <div class="summary-item-label">{{ __('portal.print_summary_net') }}</div>
                <div class="summary-item-val summary-net">{{ number_format((float)$settlement->net_salary, 2) }}</div>
            </div>
        </div>

        {{-- Signatures --}}
        <div class="signatures">
            <div class="sig-box">
                <div class="sig-label">{{ __('portal.print_signature_delegate') }}</div>
                <div style="height:40px;"></div>
                <div style="font-size:13px;font-weight:600;">{{ $delegate->name }}</div>
            </div>
            <div class="sig-box">
                <div class="sig-label">{{ __('portal.print_company_stamp') }}</div>
                <div style="height:40px;"></div>
                <div style="font-size:13px;font-weight:600;">{{ $companyName }}</div>
            </div>
        </div>

        {{-- Footer --}}
        <div class="doc-footer">
            <div>{{ $companyName }}@if($hasNameEn) · {{ $companyNameEn }}@endif</div>
            @if($hasCr)
                <div>{{ __('portal.print_cr_full', ['cr' => $companyCr]) }}</div>
            @endif
            <div style="margin-top:4px;color:#b8c3d6;">
                {{ __('portal.print_doc_type_cz') }} — {{ $period->getDisplayLabel() }} — {{ $payoutLabel }} — {{ __('portal.print_print_date', ['date' => now()->format('Y-m-d H:i')]) }}
            </div>
        </div>

    </div>
</div>

</body>
</html>
