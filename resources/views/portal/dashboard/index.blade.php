@extends('layouts.portal.app')

@section('title', __('portal.dashboard_title'))

@section('content')
@php
    $isHs      = $platformCode === 'hungerstation';
    $platLabel = $isHs ? __('portal.platform_hungerstation') : __('portal.platform_chefz');
    $platColor = $isHs ? 'hs' : 'cz';

    $currentOrders = (int)   ($currentSettlement?->total_orders ?? 0);
    // FTR: distance_payment is base pay; total entitlements adds company_benefits_total
    $currentFees   = $isHs
        ? (float) ($currentSettlement?->distance_payment ?? 0)
        : (float) ($currentSettlement?->gross_delivery_fees ?? 0);
    $currentSalary = $isHs
        ? (float) ($currentSettlement?->distance_payment ?? 0) + (float) ($currentSettlement?->company_benefits_total ?? 0)
        : (float) ($currentSettlement?->commission_total ?? 0);
    $currentDed    = $isHs
        ? (float) ($currentSettlement?->total_platform_penalties ?? 0) + (float) ($currentSettlement?->rider_balance ?? 0) + (float) ($currentSettlement?->company_deductions_total ?? 0)
        : (float) ($currentSettlement?->deductions_total ?? 0);
    $currentComp   = $isHs ? 0.0 : (float) ($currentSettlement?->platform_compensations ?? 0);
    $currentNet    = (float) ($currentSettlement?->net_salary ?? 0);
    $periodLabel   = $currentSettlement?->period?->getDisplayLabel() ?? '—';
    $periodStatus  = $currentSettlement?->period?->status ?? null;

    $stClass = 'open'; $stLabel = __('portal.status_processing');
    if ($periodStatus) {
        $stClass = match($periodStatus->value) {
            'closed'    => 'closed',
            'published' => 'published',
            'approved'  => 'approved',
            default     => 'open',
        };
        $stLabel = match($periodStatus->value) {
            'closed'    => __('portal.status_paid'),
            'published' => __('portal.status_approved'),
            'approved'  => __('portal.status_under_review'),
            default     => __('portal.status_processing'),
        };
    }

    // Previous month for comparison
    $prevS       = $recentSettlements->get(1);
    $prevNet     = (float) ($prevS?->net_salary    ?? 0);
    $prevOrders  = (int)   ($prevS?->total_orders  ?? 0);
    $netDiff     = $currentNet - $prevNet;
    $netDiffPct  = $prevNet > 0 ? round(($netDiff / $prevNet) * 100, 1) : null;
    $ordDiff     = $currentOrders - $prevOrders;

    // Trend (oldest → newest)
    $trendItems = $recentSettlements->reverse()->values();
    $trendMax   = max(1, $trendItems->max('net_salary'));
@endphp

{{-- ─── Unread notifications alert ──────────────────────────── --}}
@if(($unreadCount ?? 0) > 0)
    <a href="{{ route('portal.notifications.index') }}"
       style="display:flex;align-items:center;gap:10px;
              background:#eff6ff;border:1.5px solid #bfdbfe;border-radius:10px;
              padding:10px 16px;margin-bottom:14px;text-decoration:none;color:var(--primary);">
        <div style="width:32px;height:32px;border-radius:9px;background:#dbeafe;
                    display:flex;align-items:center;justify-content:center;font-size:17px;flex-shrink:0;">
            <i class="la la-bell"></i>
        </div>
        <div style="flex:1;font-size:13px;font-weight:600;">
            {{ $unreadCount > 1 ? __('portal.unread_notifs_plural', ['count' => $unreadCount]) : __('portal.unread_notifs', ['count' => $unreadCount]) }}
        </div>
        <i class="la la-angle-left" style="font-size:14px;"></i>
    </a>
@endif

{{-- ─── Row 1: Welcome + Current Month ─────────────────────── --}}
<div class="row g-3 mb-3">

    {{-- Welcome card --}}
    <div class="col-12 col-md-7">
        <div class="welcome-card h-100">
            <div style="display:flex;align-items:center;gap:14px;position:relative;z-index:1;">
                <div class="welcome-avatar">
                    @if($delegate->profile_photo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($delegate->profile_photo) }}" alt="">
                    @else
                        {{ mb_substr($delegate->name ?? __('portal.avatar_fallback'), 0, 1) }}
                    @endif
                </div>
                <div style="flex:1;min-width:0;">
                    <div style="font-size:12px;opacity:.8;margin-bottom:2px;">{{ __('portal.welcome_greeting') }}</div>
                    <div style="font-size:20px;font-weight:800;line-height:1.25;
                                white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                        {{ $delegate->name }}
                    </div>
                    <div style="margin-top:7px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;">
                        <span class="platform-badge {{ $platColor }}"
                              style="background:rgba(255,255,255,.22);color:white;font-size:11px;">
                            {{ $platLabel }}
                        </span>
                        <span style="font-size:11px;opacity:.75;">
                            <i class="la la-id-card" style="margin-left:3px;"></i>{{ $delegate->delegate_code }}
                        </span>
                    </div>
                </div>
                <div style="opacity:.55;font-size:44px;flex-shrink:0;position:relative;z-index:1;">
                    <i class="la la-{{ $isHs ? 'motorcycle' : 'bicycle' }}"></i>
                </div>
            </div>

            {{-- Info chips --}}
            <div style="display:flex;gap:7px;margin-top:16px;flex-wrap:wrap;position:relative;z-index:1;">
                @if($delegate->city)
                    <div style="background:rgba(255,255,255,.15);border-radius:8px;padding:5px 10px;font-size:12px;">
                        <i class="la la-map-marker" style="margin-left:4px;"></i>{{ $delegate->city->name }}
                    </div>
                @endif
                <div style="background:rgba(255,255,255,.15);border-radius:8px;padding:5px 10px;font-size:12px;">
                    @if($delegate->status->value === 'active')
                        <i class="la la-check-circle" style="margin-left:4px;color:#86efac;"></i>
                    @else
                        <i class="la la-times-circle" style="margin-left:4px;color:#fca5a5;"></i>
                    @endif
                    {{ $delegate->status->label() }}
                </div>
                @if($vehicleAssignment?->vehicle)
                    <div style="background:rgba(255,255,255,.15);border-radius:8px;padding:5px 10px;font-size:12px;">
                        <i class="la la-car" style="margin-left:4px;"></i>
                        {{ $vehicleAssignment->vehicle->plate_number ?? __('portal.vehicle_fallback') }}
                    </div>
                @endif
                @if($currentSettlement)
                    <div style="background:rgba(255,255,255,.15);border-radius:8px;padding:5px 10px;font-size:12px;
                                display:flex;align-items:center;gap:5px;">
                        <i class="la la-calendar"></i>{{ $periodLabel }}
                        <span class="status-badge {{ $stClass }}" style="font-size:10px;">{{ $stLabel }}</span>
                    </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Current period summary + comparison --}}
    <div class="col-12 col-md-5">
        <div class="p-card h-100" style="padding:0;display:flex;flex-direction:column;">
            @if($currentSettlement)
                {{-- Net salary --}}
                <div style="padding:20px;text-align:center;
                            border-bottom:1px solid var(--border);">
                    <div style="font-size:11px;color:var(--muted);font-weight:700;
                                letter-spacing:.3px;text-transform:uppercase;margin-bottom:6px;">
                        {{ __('portal.net_salary_period', ['period' => $periodLabel]) }}
                    </div>
                    <div style="font-size:34px;font-weight:800;color:#15803d;
                                line-height:1;font-variant-numeric:tabular-nums;">
                        {{ number_format($currentNet, 2) }}
                    </div>
                    <div style="font-size:12px;color:var(--muted);margin-top:3px;">{{ __('portal.currency_sar') }}</div>
                </div>

                {{-- Previous month comparison --}}
                @if($prevS)
                    <div style="padding:16px;border-bottom:1px solid var(--border);">
                        <div style="font-size:11px;color:var(--muted);font-weight:700;
                                    text-transform:uppercase;letter-spacing:.3px;margin-bottom:10px;">
                            {{ __('portal.vs_prev_month') }}
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:8px;">
                            <span style="font-size:13px;color:var(--muted);">{{ __('portal.net_salary') }}</span>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:13px;font-weight:700;color:var(--text);">
                                    {{ number_format($prevNet, 2) }}
                                </span>
                                @if($netDiffPct !== null)
                                    <span class="cmp-chip {{ $netDiff > 0 ? 'up' : ($netDiff < 0 ? 'down' : 'flat') }}">
                                        <i class="la la-{{ $netDiff > 0 ? 'arrow-up' : ($netDiff < 0 ? 'arrow-down' : 'minus') }}"></i>
                                        {{ abs($netDiffPct) }}%
                                    </span>
                                @endif
                            </div>
                        </div>
                        <div style="display:flex;justify-content:space-between;align-items:center;">
                            <span style="font-size:13px;color:var(--muted);">{{ __('portal.kpi_orders') }}</span>
                            <div style="display:flex;align-items:center;gap:6px;">
                                <span style="font-size:13px;font-weight:700;color:var(--text);">
                                    {{ number_format($prevOrders) }}
                                </span>
                                @if($ordDiff !== 0)
                                    <span class="cmp-chip {{ $ordDiff > 0 ? 'up' : 'down' }}">
                                        <i class="la la-{{ $ordDiff > 0 ? 'arrow-up' : 'arrow-down' }}"></i>
                                        {{ abs($ordDiff) }}
                                    </span>
                                @endif
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Trend sparkline --}}
                @if($trendItems->count() >= 2)
                    <div style="padding:14px 16px;">
                        <div style="font-size:11px;color:var(--muted);font-weight:700;
                                    text-transform:uppercase;letter-spacing:.3px;margin-bottom:10px;">
                            {{ __('portal.trend_last_periods', ['count' => $trendItems->count()]) }}
                        </div>
                        <div class="trend-wrap">
                            @foreach($trendItems as $ti => $ts)
                                @php
                                    $bh = max(4, (int) round((float)$ts->net_salary / $trendMax * 34));
                                    $isCurrent = $ti === $trendItems->count() - 1;
                                @endphp
                                <div class="trend-bar {{ $isCurrent ? 'current' : '' }}"
                                     style="height:{{ $bh }}px;"
                                     title="{{ $ts->period?->getDisplayLabel() }}: {{ number_format((float)$ts->net_salary, 0) }} {{ __('portal.currency_sar_short') }}">
                                </div>
                            @endforeach
                        </div>
                        <div style="display:flex;justify-content:space-between;margin-top:4px;">
                            <span style="font-size:10px;color:var(--muted);">
                                {{ $trendItems->first()?->period?->getDisplayLabel() ?? '' }}
                            </span>
                            <span style="font-size:10px;color:var(--primary);font-weight:700;">
                                {{ $trendItems->last()?->period?->getDisplayLabel() ?? '' }}
                            </span>
                        </div>
                    </div>
                @endif
            @else
                <div class="empty-state" style="flex:1;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:36px 20px;">
                    <i class="la la-file-text-o"></i>
                    <h6>{{ __('portal.no_settlement_current') }}</h6>
                    <p>{{ __('portal.no_settlement_current_sub') }}</p>
                </div>
            @endif
        </div>
    </div>
</div>

{{-- ─── Best Month Banner ────────────────────────────────────── --}}
@php
    $bestIsDifferent = $isHs
        ? ($bestMonth?->id !== $currentSettlement?->id)
        : ($bestMonth?->monthly_period_id !== $currentSettlement?->monthly_period_id);
@endphp
@if($bestMonth && $bestIsDifferent)
    <div style="background:linear-gradient(135deg,#854d0e,#d97706);
                border-radius:var(--radius);padding:14px 18px;margin-bottom:16px;
                display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;">
        <div style="display:flex;align-items:center;gap:10px;color:white;">
            <div style="font-size:26px;">🏆</div>
            <div>
                <div style="font-size:11px;opacity:.8;font-weight:600;letter-spacing:.3px;text-transform:uppercase;">
                    {{ __('portal.best_month_ever') }}
                </div>
                <div style="font-size:15px;font-weight:700;margin-top:2px;">
                    {{ $bestMonth->period?->getDisplayLabel() ?? '—' }}
                </div>
            </div>
        </div>
        <div style="text-align:left;color:white;">
            <div style="font-size:22px;font-weight:800;font-variant-numeric:tabular-nums;line-height:1;">
                {{ number_format((float)$bestMonth->net_salary, 2) }}
            </div>
            <div style="font-size:11px;opacity:.8;margin-top:2px;">{{ __('portal.currency_sar') }}</div>
        </div>
    </div>
@endif

{{-- ─── KPI Cards ────────────────────────────────────────────── --}}
@if($currentSettlement)
<div class="kpi-grid mb-4">
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#eff6ff;color:#2563eb;"><i class="la la-shopping-bag"></i></div>
        <div class="kpi-value">{{ number_format($currentOrders) }}</div>
        <div class="kpi-label">{{ __('portal.kpi_orders') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#f0fdf4;color:#16a34a;"><i class="la la-money"></i></div>
        <div class="kpi-value" style="font-size:15px;">{{ number_format($currentFees, 2) }}</div>
        <div class="kpi-label">{{ __('portal.kpi_delivery_fees') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fdf4ff;color:#9333ea;"><i class="la la-calculator"></i></div>
        <div class="kpi-value" style="font-size:15px;">{{ number_format($currentSalary, 2) }}</div>
        <div class="kpi-label">{{ __('portal.kpi_total_entitlements') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fff1f2;color:#e11d48;"><i class="la la-minus-square"></i></div>
        <div class="kpi-value" style="font-size:15px;color:#e11d48;">{{ number_format($currentDed, 2) }}</div>
        <div class="kpi-label">{{ __('portal.kpi_total_deductions') }}</div>
    </div>
    <div class="kpi-card">
        <div class="kpi-icon" style="background:#fff7ed;color:#ea580c;"><i class="la la-plus-square"></i></div>
        <div class="kpi-value" style="font-size:15px;color:#ea580c;">{{ number_format($currentComp, 2) }}</div>
        <div class="kpi-label">{{ __('portal.kpi_compensations') }}</div>
    </div>
    <div class="kpi-card" style="border:1.5px solid #bbf7d0;background:linear-gradient(135deg,#f0fdf4,#dcfce7);">
        <div class="kpi-icon" style="background:#dcfce7;color:#16a34a;"><i class="la la-check-circle"></i></div>
        <div class="kpi-value" style="font-size:15px;color:#15803d;">{{ number_format($currentNet, 2) }}</div>
        <div class="kpi-label" style="color:#16a34a;font-weight:600;">{{ __('portal.kpi_net_salary') }}</div>
    </div>
</div>
@endif

{{-- ─── Vehicle ──────────────────────────────────────────────── --}}
@if($vehicleAssignment?->vehicle)
    @php $v = $vehicleAssignment->vehicle; @endphp
    <div class="section-header">{{ __('portal.vehicle_assigned_section') }}</div>
    <div class="p-card mb-4">
        <div style="display:flex;align-items:center;gap:14px;padding:16px;">
            <div style="width:46px;height:46px;border-radius:12px;
                        background:#eff6ff;color:#2563eb;
                        display:flex;align-items:center;justify-content:center;
                        font-size:22px;flex-shrink:0;">
                <i class="la la-car"></i>
            </div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:15px;margin-bottom:4px;">
                    {{ trim(($v->make ?? '') . ' ' . ($v->model ?? '')) ?: __('portal.vehicle_fallback') }}
                </div>
                <div style="font-size:13px;color:var(--muted);display:flex;align-items:center;gap:8px;">
                    @if($v->plate_number ?? null)
                        <span style="background:#f1f5f9;border-radius:6px;padding:2px 9px;
                                     font-weight:600;font-family:monospace;letter-spacing:.5px;">
                            {{ $v->plate_number }}
                        </span>
                    @endif
                    @if($v->year ?? null)
                        <span>{{ $v->year }}</span>
                    @endif
                </div>
            </div>
            <span class="status-badge published">{{ __('portal.vehicle_badge') }}</span>
        </div>
    </div>
@endif

{{-- ─── Recent Settlements ───────────────────────────────────── --}}
@if($recentSettlements->isNotEmpty())
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
        <span class="section-header" style="margin-bottom:0;">{{ __('portal.recent_settlements') }}</span>
        <a href="{{ route('portal.settlements.index') }}"
           style="font-size:13px;color:var(--primary);text-decoration:none;font-weight:600;">
            {{ __('portal.view_all') }} <i class="la la-angle-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}" style="font-size:11px;"></i>
        </a>
    </div>

    @foreach($recentSettlements as $s)
        @php
            $sp      = $s->period;
            $spSt    = $sp?->status;
            $canView = $spSt?->isVisibleToDelegate() ?? false;
            $sClass  = match($spSt?->value ?? '') {
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
        <div class="settlement-row">
            <div class="sr-bar {{ $platColor }}"></div>
            <div class="sr-body">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;">
                    <span style="font-weight:700;font-size:14px;">{{ $sp?->getDisplayLabel() ?? '—' }}</span>
                    <span class="status-badge {{ $sClass }}">{{ $sLabel }}</span>
                </div>
                <div style="display:flex;align-items:center;justify-content:space-between;">
                    <span style="font-size:13px;color:var(--muted);">
                        <i class="la la-shopping-bag" style="{{ app()->getLocale() === 'ar' ? 'margin-left:3px;' : 'margin-right:3px;' }}"></i>
                        {{ number_format($s->total_orders) }} {{ __('portal.orders_unit') }}
                    </span>
                    <span style="font-size:15px;font-weight:800;color:#15803d;font-variant-numeric:tabular-nums;">
                        {{ number_format((float)$s->net_salary, 2) }} {{ __('portal.currency_sar_short') }}
                    </span>
                </div>
            </div>
            @if($canView)
                <a href="{{ route('portal.settlements.show', $sp->id) }}"
                   style="display:flex;align-items:center;padding:0 14px;
                          color:var(--primary);font-size:20px;text-decoration:none;
                          {{ app()->getLocale() === 'ar' ? 'border-right:1px solid var(--border);' : 'border-left:1px solid var(--border);' }}">
                    <i class="la la-angle-{{ app()->getLocale() === 'ar' ? 'left' : 'right' }}"></i>
                </a>
            @else
                <div style="display:flex;align-items:center;padding:0 14px;
                            color:var(--border);font-size:18px;
                            {{ app()->getLocale() === 'ar' ? 'border-right:1px solid var(--border);' : 'border-left:1px solid var(--border);' }}">
                    <i class="la la-lock" style="font-size:15px;"></i>
                </div>
            @endif
        </div>
    @endforeach
@else
    <div class="p-card">
        <div class="empty-state">
            <i class="la la-history"></i>
            <h6>{{ __('portal.no_settlements_yet') }}</h6>
            <p>{{ __('portal.no_settlements_yet_sub') }}</p>
        </div>
    </div>
@endif

@endsection
