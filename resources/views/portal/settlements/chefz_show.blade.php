@extends('layouts.portal.app')

@section('title', __('portal.chefz_settlement_title', ['period' => $period->getDisplayLabel()]))

@section('content')
@php
    $isRtl      = app()->getLocale() === 'ar';
    $iconMargin = $isRtl ? 'margin-left:6px;' : 'margin-right:6px;';
    $numAlign   = $isRtl ? 'left' : 'right';

    $stClass = match($period->status->value) {
        'closed'    => 'closed',
        'published' => 'published',
        default     => 'approved',
    };
    $stLabel = match($period->status->value) {
        'closed'    => __('portal.status_paid'),
        'published' => __('portal.status_approved'),
        default     => __('portal.status_under_review'),
    };

    $payoutLabel = match($viewMode) {
        'payout1' => __('portal.payout_1_label'),
        'payout2' => __('portal.payout_2_label'),
        default   => __('portal.payout_total_label'),
    };

    // Mahmoud's approved formula: Driver Base = Gross − VAT; Company Share on Subtotal (after additions)
    $driverBase = (float)$settlement->gross_delivery_fees - (float)$settlement->chefz_tax_amount;
    $subtotal   = (float)$settlement->commission_total;  // subtotal before company share
@endphp

{{-- ─── Company header ────────────────────────────────────────── --}}
<div class="p-card mb-4">
    <div style="background:linear-gradient(135deg,#0f2444,#7e22ce);
                padding:18px 20px;color:white;border-radius:14px 14px 0 0;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
                <div style="font-size:12px;opacity:.65;margin-bottom:3px;">{{ $companyName }}</div>
                <div style="font-size:18px;font-weight:800;">{{ __('portal.chefz_salary_slip') }}</div>
                <div style="font-size:13px;opacity:.8;margin-top:4px;">
                    <i class="la la-calendar" style="{{ $iconMargin }}"></i>{{ $period->getDisplayLabel() }}
                    &nbsp;|&nbsp;
                    {{ $payoutLabel }}
                </div>
            </div>
            <span class="status-badge {{ $stClass }}" style="font-size:12px;padding:4px 12px;">{{ $stLabel }}</span>
        </div>
    </div>
    <div style="padding:14px 18px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">{{ __('portal.delegate_name_label') }}</div>
            <div style="font-size:14px;font-weight:700;margin-top:2px;">{{ $delegate->name }}</div>
        </div>
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">{{ __('portal.id_number_label') }}</div>
            <div style="font-size:14px;font-weight:700;margin-top:2px;font-family:monospace;">{{ $delegate->national_id ?? $delegate->delegate_code }}</div>
        </div>
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">{{ __('portal.platform_label') }}</div>
            <div style="margin-top:4px;"><span class="platform-badge cz">{{ __('portal.platform_chefz') }}</span></div>
        </div>
    </div>
</div>

{{-- ─── Payout selector tabs ───────────────────────────────────── --}}
<div style="display:flex;gap:8px;margin-bottom:20px;flex-wrap:wrap;">
    <a href="{{ route('portal.settlements.show', $period->id) }}"
       style="padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;
              text-decoration:none;transition:all .15s;
              {{ $viewMode === 'total' ? 'background:var(--primary);color:white;' : 'background:white;color:var(--muted);border:1.5px solid var(--border);' }}">
        <i class="la la-calendar-check-o"></i> {{ __('portal.payout_total_label') }}
    </a>
    @if ($payout1)
        <a href="{{ route('portal.settlements.show', [$period->id, 'payout' => 1]) }}"
           style="padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;
                  text-decoration:none;transition:all .15s;
                  {{ $viewMode === 'payout1' ? 'background:#0369a1;color:white;' : 'background:white;color:var(--muted);border:1.5px solid var(--border);' }}">
            {{ __('portal.payout_1_label') }}
        </a>
    @endif
    @if ($payout2)
        <a href="{{ route('portal.settlements.show', [$period->id, 'payout' => 2]) }}"
           style="padding:8px 16px;border-radius:20px;font-size:13px;font-weight:600;
                  text-decoration:none;transition:all .15s;
                  {{ $viewMode === 'payout2' ? 'background:#7e22ce;color:white;' : 'background:white;color:var(--muted);border:1.5px solid var(--border);' }}">
            {{ __('portal.payout_2_label') }}
        </a>
    @endif
</div>

{{-- Monthly total: P1 | P2 comparison --}}
@if ($viewMode === 'total' && $payout1 && $payout2)
    <div class="p-card mb-4">
        <div class="section-header">{{ __('portal.comparison_two_payouts') }}</div>
        <table class="detail-table" style="width:100%;">
            <thead>
                <tr style="background:var(--bg);">
                    <th style="padding:10px;text-align:{{ $isRtl ? 'right' : 'left' }};font-size:12px;color:var(--muted);">{{ __('portal.table_item_col') }}</th>
                    <th style="padding:10px;text-align:center;font-size:12px;color:#0369a1;">{{ __('portal.print_payout_1_col') }}</th>
                    <th style="padding:10px;text-align:center;font-size:12px;color:#7e22ce;">{{ __('portal.print_payout_2_col') }}</th>
                    <th style="padding:10px;text-align:center;font-size:12px;font-weight:700;">{{ __('portal.table_total_col') }}</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="padding:8px 10px;font-size:13px;">{{ __('portal.orders_total_label') }}</td>
                    <td style="text-align:center;font-variant-numeric:tabular-nums;">{{ number_format($payout1->total_orders) }}</td>
                    <td style="text-align:center;font-variant-numeric:tabular-nums;">{{ number_format($payout2->total_orders) }}</td>
                    <td style="text-align:center;font-weight:700;font-variant-numeric:tabular-nums;">{{ number_format($settlement->total_orders) }}</td>
                </tr>
                <tr style="background:var(--bg);">
                    <td style="padding:8px 10px;font-size:13px;">{{ __('portal.delivery_fees_label') }}</td>
                    <td style="text-align:center;color:#16a34a;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout1->gross_delivery_fees, 2) }}</td>
                    <td style="text-align:center;color:#16a34a;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout2->gross_delivery_fees, 2) }}</td>
                    <td style="text-align:center;color:#16a34a;font-weight:700;font-variant-numeric:tabular-nums;">{{ number_format((float)$settlement->gross_delivery_fees, 2) }}</td>
                </tr>
                <tr>
                    <td style="padding:8px 10px;font-size:13px;">{{ __('portal.vat_label') }}</td>
                    <td style="text-align:center;color:#e11d48;font-variant-numeric:tabular-nums;">({{ number_format((float)$payout1->chefz_tax_amount, 2) }})</td>
                    <td style="text-align:center;color:#e11d48;font-variant-numeric:tabular-nums;">({{ number_format((float)$payout2->chefz_tax_amount, 2) }})</td>
                    <td style="text-align:center;color:#e11d48;font-weight:700;font-variant-numeric:tabular-nums;">({{ number_format((float)$settlement->chefz_tax_amount, 2) }})</td>
                </tr>
                <tr style="background:var(--bg);">
                    <td style="padding:8px 10px;font-size:13px;">{{ __('portal.company_share_label') }}</td>
                    <td style="text-align:center;color:#e11d48;font-variant-numeric:tabular-nums;">({{ number_format((float)$payout1->company_share_amount, 2) }})</td>
                    <td style="text-align:center;color:#e11d48;font-variant-numeric:tabular-nums;">({{ number_format((float)$payout2->company_share_amount, 2) }})</td>
                    <td style="text-align:center;color:#e11d48;font-weight:700;font-variant-numeric:tabular-nums;">({{ number_format((float)$settlement->company_share_amount, 2) }})</td>
                </tr>
                @if ((float)$settlement->platform_compensations > 0)
                    <tr>
                        <td style="padding:8px 10px;font-size:13px;">{{ __('portal.platform_compensations_lbl') }}</td>
                        <td style="text-align:center;color:#0284c7;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout1->platform_compensations, 2) }}</td>
                        <td style="text-align:center;color:#0284c7;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout2->platform_compensations, 2) }}</td>
                        <td style="text-align:center;color:#0284c7;font-weight:700;font-variant-numeric:tabular-nums;">{{ number_format((float)$settlement->platform_compensations, 2) }}</td>
                    </tr>
                @endif
                @if ((float)$settlement->positive_bonus > 0)
                    <tr style="background:var(--bg);">
                        <td style="padding:8px 10px;font-size:13px;">{{ __('portal.bonuses_label') }}</td>
                        <td style="text-align:center;color:#16a34a;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout1->positive_bonus, 2) }}</td>
                        <td style="text-align:center;color:#16a34a;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout2->positive_bonus, 2) }}</td>
                        <td style="text-align:center;color:#16a34a;font-weight:700;font-variant-numeric:tabular-nums;">{{ number_format((float)$settlement->positive_bonus, 2) }}</td>
                    </tr>
                @endif
                @if ((float)$settlement->deductions_total > 0)
                    <tr>
                        <td style="padding:8px 10px;font-size:13px;">{{ __('portal.deductions_col') }}</td>
                        <td style="text-align:center;color:#e11d48;font-variant-numeric:tabular-nums;">({{ number_format((float)$payout1->deductions_total, 2) }})</td>
                        <td style="text-align:center;color:#e11d48;font-variant-numeric:tabular-nums;">({{ number_format((float)$payout2->deductions_total, 2) }})</td>
                        <td style="text-align:center;color:#e11d48;font-weight:700;font-variant-numeric:tabular-nums;">({{ number_format((float)$settlement->deductions_total, 2) }})</td>
                    </tr>
                @endif
                <tr style="border-top:2px solid #e2e8f0;">
                    <td style="padding:10px;font-size:14px;font-weight:700;">{{ __('portal.net_salary_row') }}</td>
                    <td style="text-align:center;font-size:15px;font-weight:800;color:#15803d;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout1->net_salary, 2) }}</td>
                    <td style="text-align:center;font-size:15px;font-weight:800;color:#6b21a8;font-variant-numeric:tabular-nums;">{{ number_format((float)$payout2->net_salary, 2) }}</td>
                    <td style="text-align:center;font-size:16px;font-weight:900;color:#15803d;font-variant-numeric:tabular-nums;">{{ number_format((float)$settlement->net_salary, 2) }} {{ __('portal.currency_sar_short') }}</td>
                </tr>
            </tbody>
        </table>
    </div>
@else
{{-- Per-payout detailed breakdown ──────────────────────────── --}}
<div class="settlement-detail-grid">
    <div>
        <div class="section-header">{{ __('portal.calculation_breakdown') }} — {{ $payoutLabel }}</div>
        <div class="p-card mb-3">
            <table class="detail-table" style="width:100%;">
                <tr style="background:#f0fdf4;">
                    <td style="padding:10px;font-size:13px;">
                        {{ __('portal.gross_fees_incl_vat') }}
                    </td>
                    <td style="text-align:{{ $numAlign }};font-weight:700;color:#16a34a;font-variant-numeric:tabular-nums;">
                        {{ number_format((float)$settlement->gross_delivery_fees, 2) }} {{ __('portal.currency_sar_short') }}
                    </td>
                </tr>
                <tr>
                    <td style="padding:10px;font-size:13px;">
                        {{ __('portal.minus_vat') }}
                        <small style="color:var(--muted);">({{ number_format((float)$settlement->chefz_tax_rate * 100, 1) }}%)</small>
                    </td>
                    <td style="text-align:{{ $numAlign }};color:#e11d48;font-variant-numeric:tabular-nums;">
                        ({{ number_format((float)$settlement->chefz_tax_amount, 2) }}) {{ __('portal.currency_sar_short') }}
                    </td>
                </tr>
                <tr style="background:var(--bg);">
                    <td style="padding:10px;font-size:13px;font-weight:600;">{{ __('portal.driver_base_label') }}</td>
                    <td style="text-align:{{ $numAlign }};font-weight:700;font-variant-numeric:tabular-nums;color:#2563eb;">
                        {{ number_format($driverBase, 2) }} {{ __('portal.currency_sar_short') }}
                    </td>
                </tr>
                @if ((float)$settlement->platform_compensations > 0)
                    <tr>
                        <td style="padding:10px;font-size:13px;">{{ __('portal.plus_compensations') }}</td>
                        <td style="text-align:{{ $numAlign }};color:#0284c7;font-variant-numeric:tabular-nums;">
                            {{ number_format((float)$settlement->platform_compensations, 2) }} {{ __('portal.currency_sar_short') }}
                        </td>
                    </tr>
                @endif
                @if ((float)$settlement->positive_bonus > 0)
                    <tr>
                        <td style="padding:10px;font-size:13px;">{{ __('portal.plus_bonuses') }}</td>
                        <td style="text-align:{{ $numAlign }};color:#16a34a;font-variant-numeric:tabular-nums;">
                            {{ number_format((float)$settlement->positive_bonus, 2) }} {{ __('portal.currency_sar_short') }}
                        </td>
                    </tr>
                @endif
                @if ((float)$settlement->platform_compensations > 0 || (float)$settlement->positive_bonus > 0)
                    <tr style="background:var(--bg);">
                        <td style="padding:10px;font-size:13px;font-weight:600;">{{ __('portal.subtotal_before_share') }}</td>
                        <td style="text-align:{{ $numAlign }};font-weight:700;font-variant-numeric:tabular-nums;color:#2563eb;">
                            {{ number_format($subtotal, 2) }} {{ __('portal.currency_sar_short') }}
                        </td>
                    </tr>
                @endif
                <tr>
                    <td style="padding:10px;font-size:13px;">
                        {{ __('portal.minus_company_share') }}
                        <small style="color:var(--muted);">({{ number_format((float)$settlement->company_share_rate * 100, 1) }}% {{ __('portal.of_total_label') }})</small>
                    </td>
                    <td style="text-align:{{ $numAlign }};color:#e11d48;font-variant-numeric:tabular-nums;">
                        ({{ number_format((float)$settlement->company_share_amount, 2) }}) {{ __('portal.currency_sar_short') }}
                    </td>
                </tr>
                @if ((float)$settlement->deductions_total > 0)
                    <tr>
                        <td style="padding:10px;font-size:13px;">{{ __('portal.minus_total_deductions') }}</td>
                        <td style="text-align:{{ $numAlign }};color:#e11d48;font-variant-numeric:tabular-nums;">
                            ({{ number_format((float)$settlement->deductions_total, 2) }}) {{ __('portal.currency_sar_short') }}
                        </td>
                    </tr>
                @endif
                <tr style="border-top:2px solid #e2e8f0;background:#f8f9fc;">
                    <td style="padding:12px 10px;font-size:15px;font-weight:800;">{{ __('portal.net_salary_row') }}</td>
                    <td style="text-align:{{ $numAlign }};font-size:20px;font-weight:900;font-variant-numeric:tabular-nums;
                               color:{{ (float)$settlement->net_salary < 0 ? '#e11d48' : '#15803d' }};">
                        {{ number_format((float)$settlement->net_salary, 2) }} {{ __('portal.currency_sar_short') }}
                    </td>
                </tr>
            </table>
        </div>

        @if ((float)$settlement->bonus_total < 0)
            <div style="background:#fffbeb;border:1px solid #fcd34d;border-radius:10px;padding:12px 14px;margin-bottom:16px;font-size:13px;">
                <strong><i class="la la-info-circle" style="{{ $iconMargin }}color:#d97706;"></i> {{ __('portal.bonus_note_title') }}</strong>
                <p style="margin:6px 0 0;">{{ __('portal.bonus_note_body', ['amount' => number_format((float)$settlement->bonus_total, 2)]) }}</p>
            </div>
        @endif
    </div>

    {{-- Summary card --}}
    <div>
        <div class="section-header">{{ __('portal.summary_section') }}</div>
        <div class="p-card" style="text-align:center;padding:24px 20px;">
            <div style="font-size:12px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;margin-bottom:8px;">
                {{ __('portal.net_salary_row') }} — {{ $payoutLabel }}
            </div>
            <div style="font-size:36px;font-weight:900;font-variant-numeric:tabular-nums;
                        color:{{ (float)$settlement->net_salary < 0 ? '#e11d48' : '#15803d' }};line-height:1.1;">
                {{ number_format((float)$settlement->net_salary, 2) }}
            </div>
            <div style="font-size:16px;font-weight:500;color:var(--muted);margin-top:4px;">{{ __('portal.currency_sar') }}</div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-top:20px;">
                <div style="background:var(--bg);border-radius:10px;padding:12px;">
                    <div style="font-size:20px;font-weight:800;">{{ number_format($settlement->total_orders) }}</div>
                    <div style="font-size:11px;color:var(--muted);">{{ __('portal.orders_unit') }}</div>
                </div>
                <div style="background:var(--bg);border-radius:10px;padding:12px;">
                    <div style="font-size:16px;font-weight:800;font-variant-numeric:tabular-nums;">
                        {{ number_format((float)$settlement->gross_delivery_fees, 0) }}
                    </div>
                    <div style="font-size:11px;color:var(--muted);">{{ __('portal.fees_label') }}</div>
                </div>
            </div>

            <div style="margin-top:16px;padding-top:16px;border-top:1px solid var(--border);">
                <a href="{{ route('portal.settlements.show', [$period->id]) }}"
                   style="display:block;margin-bottom:8px;padding:10px;border-radius:8px;
                          background:var(--primary);color:white;font-weight:700;font-size:13px;
                          text-decoration:none;">
                    <i class="la la-calendar-check-o"></i> {{ __('portal.view_monthly_total') }}
                </a>
                <a href="{{ route('portal.settlements.print', [$period->id, 'payout' => $viewMode === 'payout1' ? 1 : ($viewMode === 'payout2' ? 2 : 'total')]) }}"
                   style="display:block;padding:10px;border-radius:8px;
                          border:1.5px solid var(--border);color:var(--text);font-weight:600;font-size:13px;
                          text-decoration:none;">
                    <i class="la la-print"></i> {{ __('portal.print_salary_slip') }}
                </a>
            </div>
        </div>
    </div>
</div>
@endif

{{-- Action buttons --}}
<div style="display:flex;justify-content:space-between;margin-top:16px;flex-wrap:wrap;gap:8px;">
    <a href="{{ route('portal.settlements.index') }}"
       style="padding:10px 18px;border-radius:8px;border:1.5px solid var(--border);
              color:var(--text);font-weight:600;font-size:13px;text-decoration:none;">
        <i class="la la-arrow-{{ $isRtl ? 'right' : 'left' }}"></i> {{ __('portal.back_to_payouts') }}
    </a>
    <a href="{{ route('portal.settlements.print', [$period->id, 'payout' => $viewMode === 'payout1' ? 1 : ($viewMode === 'payout2' ? 2 : 'total')]) }}"
       style="padding:10px 18px;border-radius:8px;
              background:var(--primary);color:white;font-weight:600;font-size:13px;text-decoration:none;">
        <i class="la la-print"></i> {{ __('portal.print_btn') }}
    </a>
</div>

@endsection
