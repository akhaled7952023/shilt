@extends('layouts.portal.app')

@section('title', __('portal.settlements_title'))

@section('content')
@php
    $isHs      = $platformCode === 'hungerstation';
    $platLabel = $isHs ? __('portal.platform_hungerstation') : __('portal.platform_chefz');
    $platColor = $isHs ? 'hs' : 'cz';
    $isRtl     = app()->getLocale() === 'ar';

    $totalNet    = $settlements->sum('net_salary');
    $totalOrders = $settlements->sum('total_orders');
    $count       = count($settlements);
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
            {{ __('portal.settlements_summary') }}
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:16px;">
            <div>
                <div style="font-size:26px;font-weight:800;line-height:1;">{{ $count }}</div>
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

{{-- ─── Settlements grid ────────────────────────────────────── --}}
@if($settlements->isEmpty())
    <div class="p-card">
        <div class="empty-state">
            <i class="la la-history"></i>
            <h6>{{ __('portal.no_approved_settlements') }}</h6>
            <p>{{ __('portal.no_approved_settlements_sub') }}</p>
        </div>
    </div>
@else
    <div class="settlements-grid">
        @foreach($settlements as $s)
            @php
                $sp     = $s->period;
                $spSt   = $sp?->status;
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
                $grossFees  = $isHs
                    ? (float) ($s->distance_payment ?? 0)
                    : (float) ($s->gross_delivery_fees ?? 0);
                $deductions = $isHs
                    ? (float) ($s->total_platform_penalties ?? 0) + (float) ($s->rider_balance ?? 0) + (float) ($s->company_deductions_total ?? 0)
                    : (float) ($s->deductions_total ?? 0);
            @endphp
            <div class="month-card">
                <div class="month-card-top {{ $platColor }}"></div>
                <div class="month-card-body">
                    {{-- Period + status --}}
                    <div style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:14px;">
                        <div>
                            <div style="font-weight:800;font-size:16px;margin-bottom:4px;color:var(--text);">
                                {{ $sp?->getDisplayLabel() ?? '—' }}
                            </div>
                            <span class="platform-badge {{ $platColor }}">{{ $platLabel }}</span>
                        </div>
                        <span class="status-badge {{ $sClass }}">{{ $sLabel }}</span>
                    </div>

                    {{-- Net salary (prominent) --}}
                    <div style="margin-bottom:14px;">
                        <div style="font-size:11px;color:var(--muted);font-weight:600;
                                    text-transform:uppercase;letter-spacing:.3px;margin-bottom:4px;">
                            {{ __('portal.net_salary') }}
                        </div>
                        <div style="font-size:26px;font-weight:800;color:#15803d;
                                    font-variant-numeric:tabular-nums;line-height:1;">
                            {{ number_format((float)$s->net_salary, 2) }}
                            <span style="font-size:13px;font-weight:500;color:var(--muted);">{{ __('portal.currency_sar_short') }}</span>
                        </div>
                    </div>

                    {{-- Quick stats --}}
                    <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:8px;">
                        <div style="background:var(--bg);border-radius:8px;padding:8px 10px;text-align:center;">
                            <div style="font-size:14px;font-weight:700;color:var(--text);">
                                {{ number_format($s->total_orders) }}
                            </div>
                            <div style="font-size:10px;color:var(--muted);margin-top:2px;">{{ __('portal.orders_label') }}</div>
                        </div>
                        <div style="background:#f0fdf4;border-radius:8px;padding:8px 10px;text-align:center;">
                            <div style="font-size:13px;font-weight:700;color:#16a34a;font-variant-numeric:tabular-nums;">
                                {{ number_format($grossFees, 0) }}
                            </div>
                            <div style="font-size:10px;color:#16a34a;margin-top:2px;">{{ __('portal.fees_label') }}</div>
                        </div>
                        <div style="background:#fff1f2;border-radius:8px;padding:8px 10px;text-align:center;">
                            <div style="font-size:13px;font-weight:700;color:#e11d48;font-variant-numeric:tabular-nums;">
                                {{ number_format($deductions, 0) }}
                            </div>
                            <div style="font-size:10px;color:#e11d48;margin-top:2px;">{{ __('portal.deductions_label') }}</div>
                        </div>
                    </div>
                </div>
                <div class="month-card-footer">
                    <a href="{{ route('portal.settlements.show', $sp->id) }}"
                       style="display:inline-flex;align-items:center;gap:6px;
                              font-size:13px;font-weight:700;color:var(--primary);
                              text-decoration:none;">
                        <i class="la la-eye" style="font-size:16px;"></i>
                        {{ __('portal.view_details') }}
                    </a>
                    <span style="font-size:11px;color:var(--muted);">
                        <i class="la la-calendar" style="{{ $isRtl ? 'margin-left:3px;' : 'margin-right:3px;' }}"></i>
                        {{ $sp?->getDisplayLabel() ?? '' }}
                    </span>
                </div>
            </div>
        @endforeach
    </div>
@endif

@endsection
