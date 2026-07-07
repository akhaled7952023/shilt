@extends('layouts.portal.app')

@section('title', __('portal.settlement_title', ['period' => $period->getDisplayLabel()]))

@section('content')
@php
    $isHs       = $platformCode === 'hungerstation';
    $platLabel  = $isHs ? __('portal.platform_hungerstation') : __('portal.platform_chefz');
    $platColor  = $isHs ? 'hs' : 'cz';
    $isRtl      = app()->getLocale() === 'ar';
    $iconMargin = $isRtl ? 'margin-left:6px;' : 'margin-right:6px;';

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
@endphp

{{-- ─── Company header (full width) ─────────────────────────── --}}
<div class="p-card mb-4">
    <div style="background:linear-gradient(135deg,#0f2444,#1e40af);
                padding:18px 20px;color:white;border-radius:14px 14px 0 0;">
        <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px;">
            <div>
                <div style="font-size:12px;opacity:.65;margin-bottom:3px;">{{ $companyName }}</div>
                <div style="font-size:18px;font-weight:800;">{{ __('portal.monthly_salary_slip') }}</div>
                <div style="font-size:13px;opacity:.8;margin-top:4px;">
                    <i class="la la-calendar" style="{{ $iconMargin }}"></i>{{ $period->getDisplayLabel() }}
                </div>
            </div>
            <span class="status-badge {{ $stClass }}" style="font-size:12px;padding:4px 12px;">{{ $stLabel }}</span>
        </div>
    </div>
    <div style="padding:14px 18px;display:flex;flex-wrap:wrap;gap:16px;align-items:center;">
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">
                {{ __('portal.delegate_name_label') }}
            </div>
            <div style="font-size:14px;font-weight:700;margin-top:2px;">{{ $delegate->name }}</div>
        </div>
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">
                {{ __('portal.delegate_id_label') }}
            </div>
            <div style="font-size:14px;font-weight:700;margin-top:2px;font-family:monospace;">
                {{ $delegate->delegate_code }}
            </div>
        </div>
        <div>
            <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">
                {{ __('portal.platform_label') }}
            </div>
            <div style="margin-top:4px;"><span class="platform-badge {{ $platColor }}">{{ $platLabel }}</span></div>
        </div>
        @if($delegate->city)
            <div>
                <div style="font-size:10px;color:var(--muted);font-weight:600;text-transform:uppercase;letter-spacing:.3px;">
                    {{ __('portal.region_label') }}
                </div>
                <div style="font-size:14px;font-weight:700;margin-top:2px;">{{ $delegate->city->name }}</div>
            </div>
        @endif
    </div>
</div>

{{-- ─── Desktop 2-col / Mobile stacked ─────────────────────── --}}
<div class="settlement-detail-grid">

    {{-- ─ Main column: breakdown tables ─ --}}
    <div>
        @if($isHs)
            @php
                $benefitEntries   = $settlement->deductions->where('is_benefit', true);
                $deductionEntries = $settlement->deductions->where('is_benefit', false);
                $totalEarnings    = (float)$settlement->distance_payment + (float)$settlement->company_benefits_total;
                $totalDeductions  = (float)$settlement->total_platform_penalties
                                  + (float)$settlement->rider_balance
                                  + (float)$settlement->company_deductions_total;
            @endphp

            {{-- HungerStation FTR: Earnings breakdown ─────────────── --}}
            <div class="section-header">{{ __('portal.entitlements_ftr') }}</div>
            <div class="p-card mb-3">
                <table class="detail-table">
                    <tr>
                        <td>
                            <i class="la la-shopping-bag" style="{{ $iconMargin }}color:#2563eb;"></i>
                            {{ __('portal.orders_total_label') }}
                        </td>
                        <td>{{ number_format($settlement->total_orders) }}</td>
                    </tr>
                    <tr class="cred-row">
                        <td>
                            <i class="la la-money" style="{{ $iconMargin }}color:#16a34a;"></i>
                            {{ __('portal.base_salary_distance') }}
                        </td>
                        <td>{{ number_format((float)$settlement->distance_payment, 2) }}</td>
                    </tr>
                    @foreach($benefitEntries as $ben)
                        <tr class="cred-row">
                            <td>
                                <i class="la la-plus-circle" style="{{ $iconMargin }}color:#7c3aed;"></i>
                                {{ $ben->getTypeLabel() }}
                            </td>
                            <td>{{ number_format((float)$ben->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    <tr class="total-row">
                        <td><b>{{ __('portal.total_entitlements') }}</b></td>
                        <td><b>{{ number_format($totalEarnings, 2) }}</b></td>
                    </tr>
                </table>
            </div>

            {{-- HungerStation FTR: Deductions ─────────────── --}}
            <div class="section-header">{{ __('portal.deductions_section') }}</div>
            <div class="p-card mb-3">
                <table class="detail-table">
                    @if((float)$settlement->total_platform_penalties > 0)
                        <tr class="ded-row">
                            <td>
                                <i class="la la-minus" style="{{ $iconMargin }}color:#dc2626;"></i>
                                {{ __('portal.platform_penalties') }}
                            </td>
                            <td>{{ number_format((float)$settlement->total_platform_penalties, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->rider_balance > 0)
                        <tr class="ded-row">
                            <td>
                                <i class="la la-wallet" style="{{ $iconMargin }}color:#f59e0b;"></i>
                                {{ __('portal.wallet_recovery') }}
                            </td>
                            <td>{{ number_format((float)$settlement->rider_balance, 2) }}</td>
                        </tr>
                    @endif
                    @foreach($deductionEntries as $ded)
                        <tr class="ded-row">
                            <td>
                                <i class="la la-minus-circle" style="{{ $iconMargin }}color:#dc2626;"></i>
                                {{ $ded->getTypeLabel() }}
                            </td>
                            <td>{{ number_format((float)$ded->amount, 2) }}</td>
                        </tr>
                    @endforeach
                    @if($deductionEntries->isEmpty() && (float)$settlement->total_platform_penalties == 0 && (float)$settlement->rider_balance == 0)
                        <tr>
                            <td colspan="2" style="text-align:center;color:var(--muted);font-size:13px;padding:20px;">
                                <i class="la la-check-circle" style="font-size:18px;{{ $iconMargin }}color:#16a34a;"></i>
                                {{ __('portal.no_deductions_period') }}
                            </td>
                        </tr>
                    @endif
                    <tr class="total-row" style="background:#b91c1c;">
                        <td><b>{{ __('portal.total_deductions') }}</b></td>
                        <td><b>{{ number_format($totalDeductions, 2) }}</b></td>
                    </tr>
                </table>
            </div>

        @else
            {{-- The Chefz: Formula breakdown ─────────── --}}
            <div class="section-header">{{ __('portal.calculation_breakdown') }}</div>
            <div class="p-card mb-3">
                <table class="detail-table">
                    <tr>
                        <td>
                            <i class="la la-shopping-bag" style="{{ $iconMargin }}color:#2563eb;"></i>
                            {{ __('portal.orders_total_label') }}
                        </td>
                        <td>{{ number_format($settlement->total_orders) }}</td>
                    </tr>
                    <tr class="cred-row">
                        <td>
                            <i class="la la-money" style="{{ $iconMargin }}color:#16a34a;"></i>
                            {{ __('portal.delivery_fees_label') }}
                        </td>
                        <td>{{ number_format((float)$settlement->gross_delivery_fees, 2) }}</td>
                    </tr>
                    @if((float)$settlement->platform_compensations > 0)
                        <tr class="cred-row">
                            <td>
                                <i class="la la-plus" style="{{ $iconMargin }}color:#ea580c;"></i>
                                {{ __('portal.platform_compensations_lbl') }}
                            </td>
                            <td>{{ number_format((float)$settlement->platform_compensations, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->chefz_tax_amount > 0)
                        <tr class="ded-row">
                            <td>
                                <i class="la la-minus" style="{{ $iconMargin }}color:#dc2626;"></i>
                                {{ __('portal.vat_label') }}
                                <span style="font-size:11px;color:var(--muted);{{ $isRtl ? 'margin-right:4px;' : 'margin-left:4px;' }}">
                                    ({{ number_format((float)$settlement->chefz_tax_rate * 100, 1) }}%)
                                </span>
                            </td>
                            <td>{{ number_format((float)$settlement->chefz_tax_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->company_share_amount > 0)
                        <tr class="ded-row">
                            <td>
                                <i class="la la-minus" style="{{ $iconMargin }}color:#dc2626;"></i>
                                {{ __('portal.company_share_label') }}
                                <span style="font-size:11px;color:var(--muted);{{ $isRtl ? 'margin-right:4px;' : 'margin-left:4px;' }}">
                                    ({{ number_format((float)$settlement->company_share_rate * 100, 1) }}%)
                                </span>
                            </td>
                            <td>{{ number_format((float)$settlement->company_share_amount, 2) }}</td>
                        </tr>
                    @endif
                    @if((float)$settlement->platform_deductions > 0)
                        <tr class="ded-row">
                            <td>
                                <i class="la la-minus" style="{{ $iconMargin }}color:#dc2626;"></i>
                                {{ __('portal.deductions_section') }}
                            </td>
                            <td>{{ number_format((float)$settlement->platform_deductions, 2) }}</td>
                        </tr>
                    @endif
                    <tr class="total-row" style="background:#b91c1c;">
                        <td><b>{{ __('portal.total_deductions') }}</b></td>
                        <td><b>{{ number_format((float)$settlement->deductions_total, 2) }}</b></td>
                    </tr>
                </table>
            </div>
        @endif
    </div>

    {{-- ─ Secondary column: net summary (sticky on desktop) ─ --}}
    <div>
        {{-- Net salary box --}}
        <div class="net-box mb-3">
            <div style="font-size:11px;color:#16a34a;font-weight:700;
                        letter-spacing:.4px;text-transform:uppercase;margin-bottom:8px;">
                <i class="la la-check-circle" style="font-size:14px;{{ $iconMargin }}"></i>
                {{ __('portal.net_salary') }}
            </div>
            <div class="amount">{{ number_format((float)$settlement->net_salary, 2) }}</div>
            <div class="sub">{{ __('portal.currency_sar') }}</div>
        </div>

        {{-- Final summary card --}}
        @php
            $summaryEarnings   = $isHs
                ? (float)$settlement->distance_payment + (float)$settlement->company_benefits_total
                : (float)($settlement->commission_total ?? 0);
            $summaryDeductions = $isHs
                ? (float)$settlement->total_platform_penalties + (float)$settlement->rider_balance + (float)$settlement->company_deductions_total
                : (float)($settlement->deductions_total ?? 0);
        @endphp
        <div class="p-card mb-4">
            <table class="detail-table">
                <tr class="cred-row">
                    <td>{{ __('portal.total_entitlements') }}</td>
                    <td style="color:#16a34a;">{{ number_format($summaryEarnings, 2) }}</td>
                </tr>
                <tr class="ded-row">
                    <td>{{ __('portal.total_deductions') }}</td>
                    <td>{{ number_format($summaryDeductions, 2) }}</td>
                </tr>
                <tr class="net-row">
                    <td><b>{{ __('portal.net_salary_row') }}</b></td>
                    <td><b>{{ number_format((float)$settlement->net_salary, 2) }} {{ __('portal.currency_sar_short') }}</b></td>
                </tr>
            </table>
        </div>

        {{-- Print / PDF button --}}
        <a href="{{ route('portal.settlements.print', $period->id) }}"
           target="_blank"
           style="display:flex;align-items:center;justify-content:center;gap:8px;
                  background:var(--primary);color:white;border-radius:10px;
                  padding:11px 16px;margin-bottom:10px;
                  font-size:14px;font-weight:700;text-decoration:none;
                  transition:background .15s;">
            <i class="la la-print" style="font-size:18px;"></i>
            {{ __('portal.print_download_pdf') }}
        </a>

        {{-- Back link --}}
        <div style="text-align:center;padding:4px 0;">
            <a href="{{ route('portal.settlements.index') }}"
               style="display:inline-flex;align-items:center;gap:6px;
                      font-size:13px;color:var(--muted);text-decoration:none;font-weight:500;">
                <i class="la la-arrow-{{ $isRtl ? 'right' : 'left' }}" style="font-size:15px;"></i>
                {{ __('portal.back_to_settlements') }}
            </a>
        </div>
    </div>
</div>

@endsection
