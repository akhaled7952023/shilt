@extends('layouts.portal.app')

@section('title', __('portal.chefz_settlements_title'))

@section('content')
@php
    $allPayouts = $grouped->flatten();
    $totalNet   = $allPayouts->sum('net_salary');
    $totalOrders = $allPayouts->sum('total_orders');
    $periodCount = $grouped->count();
    $isRtl = app()->getLocale() === 'ar';
@endphp

{{-- ─── Year filter tabs ───────────────────────────────────── --}}
@if($availableYears->count() > 1)
    <div style="display:flex;align-items:center;gap:8px;margin-bottom:16px;flex-wrap:wrap;">
        <a href="{{ route('portal.settlements.index') }}"
           style="padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;
                  text-decoration:none;transition:all .15s;
                  {{ !$year ? 'background:var(--primary);color:white;' : 'background:white;color:var(--muted);border:1.5px solid var(--border);' }}">
            {{ __('portal.year_filter_all') }}
        </a>
        @foreach($availableYears as $y)
            <a href="{{ route('portal.settlements.index', ['year' => $y]) }}"
               style="padding:6px 14px;border-radius:20px;font-size:13px;font-weight:600;
                      text-decoration:none;transition:all .15s;
                      {{ $year == $y ? 'background:var(--primary);color:white;' : 'background:white;color:var(--muted);border:1.5px solid var(--border);' }}">
                {{ $y }}
            </a>
        @endforeach
    </div>
@endif

{{-- ─── Summary header ──────────────────────────────────────── --}}
<div style="background:linear-gradient(135deg,#1d4ed8 0%,#2563eb 60%,#7c3aed 100%);
            border-radius:14px;padding:20px;margin-bottom:20px;color:white;
            position:relative;overflow:hidden;">
    <div style="position:absolute;top:-20px;left:-20px;width:80px;height:80px;
                border-radius:50%;background:rgba(255,255,255,.07);"></div>
    <div style="position:absolute;bottom:-30px;right:40px;width:100px;height:100px;
                border-radius:50%;background:rgba(255,255,255,.05);"></div>
    <div style="position:relative;z-index:1;">
        <div style="font-size:13px;opacity:.8;margin-bottom:12px;font-weight:500;">
            {{ __('portal.chefz_summary') }}
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            <div>
                <div style="font-size:26px;font-weight:800;line-height:1;">{{ $periodCount }}</div>
                <div style="font-size:12px;opacity:.75;margin-top:3px;">{{ __('portal.summary_periods_complete') }}</div>
            </div>
            <div>
                <div style="font-size:18px;font-weight:800;line-height:1;font-variant-numeric:tabular-nums;">
                    {{ number_format((float)$totalNet, 2) }}
                </div>
                <div style="font-size:12px;opacity:.75;margin-top:3px;">{{ __('portal.summary_total_salary_sar') }}</div>
            </div>
            <div>
                <div style="font-size:26px;font-weight:800;line-height:1;">{{ number_format($totalOrders) }}</div>
                <div style="font-size:12px;opacity:.75;margin-top:3px;">{{ __('portal.summary_total_orders') }}</div>
            </div>
        </div>
    </div>
</div>

{{-- ─── Settlements grouped by period ──────────────────────── --}}
@if($grouped->isEmpty())
    <div class="p-card">
        <div class="empty-state">
            <i class="la la-history"></i>
            <h6>{{ __('portal.no_approved_settlements') }}</h6>
            <p>{{ __('portal.no_approved_settlements_sub') }}</p>
        </div>
    </div>
@else
    <div class="settlements-grid">
        @foreach($grouped as $periodId => $payouts)
            @php
                $period    = $payouts->first()->period;
                $payout1   = $payouts->firstWhere('payout_number', 1);
                $payout2   = $payouts->firstWhere('payout_number', 2);
                $isComplete = $payout1 && $payout2;
                $totalNetPeriod = $payouts->sum('net_salary');
                $totalOrdersPeriod = $payouts->sum('total_orders');
                $spSt  = $period?->status;
                $sClass = match($spSt?->value ?? '') {
                    'closed'    => 'closed',
                    'published' => 'published',
                    'approved'  => 'approved',
                    default     => 'open',
                };
                $sLabel = match($spSt?->value ?? '') {
                    'closed'    => __('portal.status_paid'),
                    'published' => __('portal.status_approved'),
                    'approved'  => __('portal.status_under_review'),
                    default     => __('portal.status_processing'),
                };
            @endphp
            <div class="month-card">
                <div class="month-card-top cz"></div>
                <div class="month-card-body">
                    {{-- Period + status --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <div style="font-weight:800;font-size:16px;margin-bottom:4px;color:var(--text);">
                                {{ $period?->getDisplayLabel() ?? '—' }}
                            </div>
                            <span class="platform-badge cz">{{ __('portal.platform_chefz') }}</span>
                            @if ($isComplete)
                                <span style="font-size:10px;color:#16a34a;{{ $isRtl ? 'margin-right:4px;' : 'margin-left:4px;' }}">
                                    <i class="la la-check-circle"></i> {{ __('portal.payout_two') }}
                                </span>
                            @else
                                <span style="font-size:10px;color:#d97706;{{ $isRtl ? 'margin-right:4px;' : 'margin-left:4px;' }}">
                                    <i class="la la-exclamation-circle"></i> {{ __('portal.payout_one') }}
                                </span>
                            @endif
                        </div>
                        <span class="status-badge {{ $sClass }}">{{ $sLabel }}</span>
                    </div>

                    {{-- Monthly net salary (prominent) --}}
                    <div style="margin-bottom:14px;">
                        <div style="font-size:11px;color:var(--muted);font-weight:600;
                                    text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px;">
                            {{ __('portal.monthly_net_total') }}
                        </div>
                        <div style="font-size:26px;font-weight:800;color:#15803d;
                                    font-variant-numeric:tabular-nums;line-height:1;">
                            {{ number_format((float)$totalNetPeriod, 2) }}
                            <span style="font-size:13px;font-weight:500;color:var(--muted);">{{ __('portal.currency_sar_short') }}</span>
                        </div>
                    </div>

                    {{-- Per-payout breakdown --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;margin-bottom:10px;">
                        @if ($payout1)
                            <div style="background:#f0f9ff;border:1px solid #bae6fd;border-radius:8px;padding:8px 10px;text-align:center;">
                                <div style="font-size:11px;color:#0284c7;font-weight:600;margin-bottom:4px;">{{ __('portal.payout_1_label') }}</div>
                                <div style="font-size:15px;font-weight:800;color:#0369a1;font-variant-numeric:tabular-nums;">
                                    {{ number_format((float)$payout1->net_salary, 2) }}
                                </div>
                                <a href="{{ route('portal.settlements.show', [$period->id, 'payout' => 1]) }}"
                                   style="font-size:10px;color:#0284c7;text-decoration:none;">{{ __('portal.view_link') }}</a>
                            </div>
                        @else
                            <div style="background:#f8f9fa;border:1px dashed #dee2e6;border-radius:8px;padding:8px 10px;text-align:center;">
                                <div style="font-size:11px;color:var(--muted);margin-bottom:4px;">{{ __('portal.payout_1_label') }}</div>
                                <div style="font-size:12px;color:var(--muted);">{{ __('portal.not_available') }}</div>
                            </div>
                        @endif
                        @if ($payout2)
                            <div style="background:#fdf4ff;border:1px solid #e9d5ff;border-radius:8px;padding:8px 10px;text-align:center;">
                                <div style="font-size:11px;color:#7e22ce;font-weight:600;margin-bottom:4px;">{{ __('portal.payout_2_label') }}</div>
                                <div style="font-size:15px;font-weight:800;color:#6b21a8;font-variant-numeric:tabular-nums;">
                                    {{ number_format((float)$payout2->net_salary, 2) }}
                                </div>
                                <a href="{{ route('portal.settlements.show', [$period->id, 'payout' => 2]) }}"
                                   style="font-size:10px;color:#7e22ce;text-decoration:none;">{{ __('portal.view_link') }}</a>
                            </div>
                        @else
                            <div style="background:#f8f9fa;border:1px dashed #dee2e6;border-radius:8px;padding:8px 10px;text-align:center;">
                                <div style="font-size:11px;color:var(--muted);margin-bottom:4px;">{{ __('portal.payout_2_label') }}</div>
                                <div style="font-size:12px;color:var(--muted);">{{ __('portal.not_available') }}</div>
                            </div>
                        @endif
                    </div>

                    <div style="display:grid;grid-template-columns:1fr 1fr;gap:8px;">
                        <div style="background:var(--bg);border-radius:8px;padding:8px 10px;text-align:center;">
                            <div style="font-size:14px;font-weight:700;color:var(--text);">
                                {{ number_format($totalOrdersPeriod) }}
                            </div>
                            <div style="font-size:10px;color:var(--muted);margin-top:2px;">{{ __('portal.total_orders_label') }}</div>
                        </div>
                        <div style="background:#fff1f2;border-radius:8px;padding:8px 10px;text-align:center;">
                            <div style="font-size:13px;font-weight:700;color:#e11d48;font-variant-numeric:tabular-nums;">
                                {{ number_format((float)$payouts->sum('deductions_total'), 0) }}
                            </div>
                            <div style="font-size:10px;color:#e11d48;margin-top:2px;">{{ __('portal.deductions_label') }}</div>
                        </div>
                    </div>
                </div>
                <div class="month-card-footer">
                    <a href="{{ route('portal.settlements.show', $period->id) }}"
                       style="display:inline-flex;align-items:center;gap:6px;
                              font-size:13px;font-weight:700;color:var(--primary);
                              text-decoration:none;">
                        <i class="la la-eye" style="font-size:16px;"></i>
                        {{ __('portal.month_summary_link') }}
                    </a>
                    <span style="font-size:11px;color:var(--muted);">
                        <i class="la la-calendar" style="{{ $isRtl ? 'margin-left:3px;' : 'margin-right:3px;' }}"></i>
                        {{ $period?->getDisplayLabel() ?? '' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
