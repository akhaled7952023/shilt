@extends('layouts.portal.app')
@section('title') {{ __('portal.ticket_new_title') }} @endsection

@section('content')
<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
    <a href="{{ route('portal.support.tickets.index') }}"
       style="width:36px; height:36px; border-radius:9px; background:white; border:1.5px solid var(--border);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--muted);
              flex-shrink:0;">
        <i class="la la-arrow-{{ app()->isLocale('ar') ? 'right' : 'left' }}" style="font-size:20px;"></i>
    </a>
    <div>
        <div style="font-size:12px; color:var(--muted); margin-bottom:1px;">{{ __('portal.ticket_new_title') }}</div>
        <h1 style="font-size:18px; font-weight:800; color:var(--text); margin:0;">
            {{ __('portal.ticket_step1_title') }}
        </h1>
    </div>
</div>

<p style="font-size:14px; color:var(--muted); margin-bottom:20px;">
    {{ __('portal.ticket_step1_subtitle') }}
</p>

@php
    $groups = [
        'financial' => [
            'label'      => __('portal.ticket_group_financial'),
            'icon'       => 'la-money-bill',
            'iconColor'  => '#d97706',
            'iconBg'     => '#fef3c7',
            'categories' => [
                \App\Enums\TicketCategory::SettlementObjection,
                \App\Enums\TicketCategory::FuelRequest,
                \App\Enums\TicketCategory::AdvanceRequest,
                \App\Enums\TicketCategory::TrafficViolationRequest,
                \App\Enums\TicketCategory::OtherFinancialRequest,
            ],
        ],
        'support' => [
            'label'      => __('portal.ticket_group_support'),
            'icon'       => 'la-life-ring',
            'iconColor'  => '#7c3aed',
            'iconBg'     => '#f5f3ff',
            'categories' => [
                \App\Enums\TicketCategory::TechnicalSupport,
            ],
        ],
    ];

    $catDescKey = fn(\App\Enums\TicketCategory $c) => 'portal.ticket_cat_desc_' . $c->value;
    $catLabelKey = fn(\App\Enums\TicketCategory $c) => 'portal.ticket_cat_' . $c->value;
@endphp

@foreach($groups as $groupKey => $group)
    <div style="margin-bottom:20px;">
        <div style="display:flex; align-items:center; gap:8px; margin-bottom:10px;">
            <div style="width:28px; height:28px; border-radius:7px; background:{{ $group['iconBg'] }};
                        display:flex; align-items:center; justify-content:center;">
                <i class="la {{ $group['icon'] }}" style="color:{{ $group['iconColor'] }}; font-size:15px;"></i>
            </div>
            <span style="font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.5px;">
                {{ $group['label'] }}
            </span>
        </div>

        <div class="p-card" style="overflow:hidden;">
            @foreach($group['categories'] as $i => $cat)
                <a href="{{ route('portal.support.tickets.create', ['category' => $cat->value]) }}"
                   style="display:flex; align-items:center; gap:12px; padding:14px 16px;
                          {{ $i < count($group['categories']) - 1 ? 'border-bottom:1px solid var(--border);' : '' }}
                          text-decoration:none; background:white; transition:background .15s;"
                   onmouseover="this.style.background='var(--bg)'"
                   onmouseout="this.style.background='white'">

                    <div style="flex:1; min-width:0;">
                        <div style="font-size:14px; font-weight:700; color:var(--text); margin-bottom:2px;">
                            @lang($catLabelKey($cat))
                        </div>
                        <div style="font-size:12px; color:var(--muted);">
                            @lang($catDescKey($cat))
                        </div>
                    </div>

                    <i class="la la-angle-{{ app()->isLocale('ar') ? 'left' : 'right' }}"
                       style="color:var(--muted); font-size:18px; flex-shrink:0;"></i>
                </a>
            @endforeach
        </div>
    </div>
@endforeach
@endsection
