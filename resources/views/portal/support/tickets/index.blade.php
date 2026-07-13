@extends('layouts.portal.app')
@section('title') {{ __('portal.support_tickets_title') }} @endsection

@section('content')
<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:16px; flex-wrap:wrap; gap:10px;">
    <h1 style="font-size:18px; font-weight:800; color:var(--text); margin:0;">
        {{ __('portal.support_tickets_title') }}
    </h1>
    <a href="{{ route('portal.support.tickets.create') }}" class="portal-btn portal-btn-primary"
       style="width:auto; padding:10px 20px; font-size:14px; display:inline-flex; align-items:center; gap:6px; border-radius:10px;">
        <i class="la la-plus" style="font-size:16px;"></i>
        {{ __('portal.ticket_open_new') }}
    </a>
</div>

{{-- Status filter tabs --}}
@php
    $statuses = [
        ''           => __('portal.ticket_all_statuses'),
        'open'       => __('portal.ticket_status_open'),
        'in_progress'       => __('portal.ticket_status_in_progress'),
        'awaiting_delegate' => __('portal.ticket_status_awaiting_delegate'),
        'resolved'   => __('portal.ticket_status_resolved'),
        'closed'     => __('portal.ticket_status_closed'),
    ];
@endphp
<div style="display:flex; gap:6px; flex-wrap:wrap; margin-bottom:16px; overflow-x:auto; padding-bottom:4px;">
    @foreach($statuses as $val => $label)
        @php $active = (string)($status ?? '') === $val; @endphp
        <a href="{{ route('portal.support.tickets.index', $val ? ['status' => $val] : []) }}"
           style="display:inline-flex; align-items:center; padding:6px 14px; border-radius:20px; font-size:12px;
                  font-weight:{{ $active ? '700' : '500' }};
                  background:{{ $active ? 'var(--primary)' : 'white' }};
                  color:{{ $active ? 'white' : 'var(--muted)' }};
                  border:1.5px solid {{ $active ? 'var(--primary)' : 'var(--border)' }};
                  text-decoration:none; white-space:nowrap;">
            {{ $label }}
            @if(($statusCounts[$val] ?? 0) > 0)
                <span style="margin-inline-start:5px; background:{{ $active ? 'rgba(255,255,255,.3)' : 'var(--bg)' }};
                             padding:1px 7px; border-radius:10px; font-size:11px;">
                    {{ $statusCounts[$val] }}
                </span>
            @endif
        </a>
    @endforeach
</div>

@if($tickets->isEmpty())
    <div class="empty-state p-card" style="padding:48px 20px;">
        <i class="la la-ticket-alt"></i>
        <h6>{{ __('portal.ticket_empty_title') }}</h6>
        <p>{{ __('portal.ticket_empty_sub') }}</p>
        <a href="{{ route('portal.support.tickets.create') }}"
           class="portal-btn portal-btn-primary"
           style="width:auto; display:inline-block; padding:10px 24px; margin-top:16px; border-radius:10px; font-size:14px;">
            {{ __('portal.ticket_open_new') }}
        </a>
    </div>
@else
    <div class="p-card" style="overflow:visible;">
        @foreach($tickets as $ticket)
            @php
                $statusColors = [
                    'open'               => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
                    'in_progress'        => ['bg' => '#f0fdf4', 'color' => '#15803d'],
                    'awaiting_delegate'  => ['bg' => '#fefce8', 'color' => '#92400e'],
                    'resolved'           => ['bg' => '#f0fdf4', 'color' => '#166534'],
                    'reopened'           => ['bg' => '#fef2f2', 'color' => '#991b1b'],
                    'closed'             => ['bg' => '#f1f5f9', 'color' => '#64748b'],
                ];
                $sc    = $statusColors[$ticket->status->value] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
                $isAwaiting = $ticket->status->value === 'awaiting_delegate';
            @endphp
            <a href="{{ route('portal.support.tickets.show', $ticket) }}"
               style="display:flex; align-items:center; gap:12px; padding:14px 16px;
                      border-bottom:1px solid var(--border); text-decoration:none;
                      background:{{ $isAwaiting ? '#fefce8' : 'white' }};
                      transition:background .15s;"
               onmouseover="this.style.background='var(--bg)'"
               onmouseout="this.style.background='{{ $isAwaiting ? '#fefce8' : 'white' }}'">

                {{-- Status dot --}}
                <div style="width:10px; height:10px; border-radius:50%; background:{{ $sc['color'] }}; flex-shrink:0;"></div>

                <div style="flex:1; min-width:0;">
                    <div style="display:flex; align-items:center; gap:8px; margin-bottom:3px; flex-wrap:wrap;">
                        <span style="font-size:12px; font-weight:700; color:var(--muted); font-variant-numeric:tabular-nums;">
                            {{ $ticket->ticket_number }}
                        </span>
                        <span style="font-size:11px; padding:2px 8px; border-radius:12px;
                                     background:{{ $sc['bg'] }}; color:{{ $sc['color'] }}; font-weight:600;">
                            @lang('portal.ticket_status_' . $ticket->status->value)
                        </span>
                        @if($isAwaiting)
                            <span style="font-size:11px; color:#d97706; font-weight:600;">
                                <i class="la la-bell"></i> {{ __('portal.ticket_status_awaiting_delegate') }}
                            </span>
                        @endif
                    </div>
                    <div style="font-size:14px; font-weight:600; color:var(--text); white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
                        {{ $ticket->subject }}
                    </div>
                    <div style="font-size:11px; color:var(--muted); margin-top:3px;">
                        {{ $ticket->category->label() }} &nbsp;·&nbsp;
                        {{ $ticket->last_activity_at?->diffForHumans() }}
                    </div>
                </div>

                <i class="la la-angle-{{ app()->isLocale('ar') ? 'left' : 'right' }}"
                   style="color:var(--muted); font-size:18px; flex-shrink:0;"></i>
            </a>
        @endforeach
    </div>

    <div style="margin-top:16px;">
        {{ $tickets->links() }}
    </div>
@endif
@endsection
