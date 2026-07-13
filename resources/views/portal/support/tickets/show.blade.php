@extends('layouts.portal.app')
@section('title') {{ __('portal.ticket_detail_title', ['number' => $ticket->ticket_number]) }} @endsection

@section('content')

{{-- Back + header --}}
<div style="display:flex; align-items:center; gap:12px; margin-bottom:20px;">
    <a href="{{ route('portal.support.tickets.index') }}"
       style="width:36px; height:36px; border-radius:9px; background:white; border:1.5px solid var(--border);
              display:flex; align-items:center; justify-content:center; text-decoration:none; color:var(--muted);
              flex-shrink:0;">
        <i class="la la-arrow-{{ app()->isLocale('ar') ? 'right' : 'left' }}" style="font-size:20px;"></i>
    </a>
    <div style="min-width:0;">
        <div style="font-size:12px; color:var(--muted); margin-bottom:1px; font-variant-numeric:tabular-nums;">
            {{ $ticket->ticket_number }}
        </div>
        <h1 style="font-size:16px; font-weight:800; color:var(--text); margin:0; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">
            {{ $ticket->subject }}
        </h1>
    </div>
</div>

{{-- Ticket meta card --}}
@php
    $statusColors = [
        'open'               => ['bg' => '#eff6ff', 'color' => '#1d4ed8'],
        'in_progress'        => ['bg' => '#f0fdf4', 'color' => '#15803d'],
        'awaiting_delegate'  => ['bg' => '#fefce8', 'color' => '#92400e'],
        'resolved'           => ['bg' => '#f0fdf4', 'color' => '#166534'],
        'reopened'           => ['bg' => '#fef2f2', 'color' => '#991b1b'],
        'closed'             => ['bg' => '#f1f5f9', 'color' => '#64748b'],
    ];
    $sc = $statusColors[$ticket->status->value] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
@endphp

<div class="p-card" style="padding:16px; margin-bottom:20px;">
    <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
        <div>
            <div style="font-size:11px; color:var(--muted); margin-bottom:3px;">{{ __('portal.ticket_col_status') }}</div>
            <span style="display:inline-block; padding:3px 10px; border-radius:12px; font-size:12px; font-weight:700;
                         background:{{ $sc['bg'] }}; color:{{ $sc['color'] }};">
                @lang('portal.ticket_status_' . $ticket->status->value)
            </span>
        </div>
        <div>
            <div style="font-size:11px; color:var(--muted); margin-bottom:3px;">{{ __('portal.ticket_col_category') }}</div>
            <div style="font-size:13px; font-weight:600; color:var(--text);">{{ $ticket->category->label() }}</div>
        </div>
        <div>
            <div style="font-size:11px; color:var(--muted); margin-bottom:3px;">{{ __('portal.ticket_opened_at') }}</div>
            <div style="font-size:13px; color:var(--text);"
                 title="{{ $ticket->opened_at?->format('Y-m-d H:i:s') }}">
                {{ $ticket->opened_at?->format('Y-m-d') }}
            </div>
        </div>
        <div>
            <div style="font-size:11px; color:var(--muted); margin-bottom:3px;">{{ __('portal.ticket_priority_label') }}</div>
            <div style="font-size:13px; color:var(--text);">{{ $ticket->priority->label() }}</div>
        </div>
    </div>
</div>

{{-- Financial request panel (if applicable) --}}
@if($ticket->financialRequest)
    @php $fr = $ticket->financialRequest; @endphp
    <div class="p-card" style="padding:16px; margin-bottom:20px;
                               border-inline-start:4px solid {{ $fr->status->value === 'approved' ? 'var(--success)' : ($fr->status->value === 'rejected' ? 'var(--danger)' : 'var(--warning)') }};">
        <div style="font-size:12px; font-weight:700; color:var(--muted); text-transform:uppercase; letter-spacing:.4px; margin-bottom:12px;">
            {{ __('portal.ticket_financial_request_label') }}
        </div>
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:10px;">
            <div>
                <div style="font-size:11px; color:var(--muted);">{{ __('portal.ticket_financial_amount') }}</div>
                <div style="font-size:15px; font-weight:800; font-variant-numeric:tabular-nums;">
                    {{ number_format($fr->requested_amount, 2) }} <span style="font-size:11px; color:var(--muted);">{{ __('portal.currency_sar_short') }}</span>
                </div>
            </div>
            <div>
                <div style="font-size:11px; color:var(--muted);">{{ __('portal.ticket_financial_status') }}</div>
                @if($fr->status->value === 'approved')
                    <div style="font-size:13px; font-weight:700; color:var(--success);">
                        {{ __('portal.ticket_financial_approved') }}
                    </div>
                    @if($fr->approved_amount)
                        <div style="font-size:11px; color:var(--muted); margin-top:2px;">
                            {{ __('portal.ticket_financial_approved_amount') }}: {{ number_format($fr->approved_amount, 2) }}
                        </div>
                    @endif
                @elseif($fr->status->value === 'rejected')
                    <div style="font-size:13px; font-weight:700; color:var(--danger);">
                        {{ __('portal.ticket_financial_rejected') }}
                    </div>
                    @if($fr->rejection_reason)
                        <div style="font-size:12px; color:var(--muted); margin-top:4px;">
                            {{ __('portal.ticket_financial_rejection_reason') }}: {{ $fr->rejection_reason }}
                        </div>
                    @endif
                @elseif($fr->status->value === 'needs_info')
                    <div style="font-size:13px; font-weight:700; color:#d97706;">
                        {{ __('portal.ticket_financial_needs_info') }}
                    </div>
                    <div style="font-size:12px; color:var(--muted); margin-top:4px;">
                        {{ __('portal.ticket_financial_needs_info_hint') }}
                    </div>
                @else
                    <div style="font-size:13px; font-weight:600; color:var(--warning);">
                        {{ __('portal.ticket_financial_pending') }}
                    </div>
                @endif
            </div>
        </div>
    </div>
@endif

{{-- Reply thread --}}
@include('portal.support.tickets._reply_thread', compact('ticket'))

{{-- Reply composer --}}
@include('portal.support.tickets._reply_composer', compact('ticket'))

@endsection
