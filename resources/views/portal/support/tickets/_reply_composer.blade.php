{{--
    Partial: portal reply composer.
    Variables: $ticket (SupportTicket)
--}}
@if($ticket->isClosedPermanently())
    {{-- Permanently closed: no composer, show CTA --}}
    <div style="margin-top:20px; padding:20px; background:#f8fafc; border-radius:var(--radius);
                border:1.5px dashed var(--border); text-align:center;">
        <i class="la la-lock" style="font-size:28px; color:var(--muted); opacity:.4; display:block; margin-bottom:8px;"></i>
        <p style="font-size:13px; color:var(--muted); margin:0 0 12px;">
            {{ __('portal.ticket_closed_notice') }}
        </p>
        <a href="{{ route('portal.support.tickets.create') }}"
           style="display:inline-block; padding:10px 20px; background:var(--primary); color:white;
                  border-radius:10px; font-size:13px; font-weight:700; text-decoration:none;">
            {{ __('portal.ticket_closed_open_new') }}
        </a>
    </div>

@elseif($ticket->status->value === 'resolved' && $ticket->isWithinGracePeriod())
    {{-- Resolved but in grace period: show notice + allow reply --}}
    <div style="margin-top:12px; padding:10px 14px; background:#fef9c3; border:1px solid #fde047;
                border-radius:10px; font-size:13px; color:#713f12; margin-bottom:12px;">
        <i class="la la-clock-o"></i>
        {{ __('portal.ticket_resolved_notice', ['deadline' => $ticket->close_grace_deadline?->format('Y-m-d H:i')]) }}
    </div>

    @include('portal.support.tickets._reply_form')

@elseif($ticket->canDelegateReply())
    @include('portal.support.tickets._reply_form')

@else
    {{-- Status does not allow replies (closed/resolved past grace) --}}
    <div style="margin-top:16px; padding:12px 14px; background:#f1f5f9; border-radius:10px;
                font-size:13px; color:var(--muted); text-align:center;">
        <i class="la la-ban"></i>
        {{ __('portal.ticket_reply_not_allowed') }}
    </div>
@endif
