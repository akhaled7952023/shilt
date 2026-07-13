{{--
  Partial: ticket detail header card.
  Variables: $ticket (SupportTicket with loaded relations)
--}}
@php
    $sourceLabels = ['portal' => 'البوابة', 'dashboard' => 'لوحة التحكم', 'system' => 'النظام'];
    $sourceIcons  = ['portal' => 'la-globe', 'dashboard' => 'la-desktop', 'system' => 'la-cogs'];
    $srcVal = $ticket->source?->value ?? '';
@endphp
<div class="card mb-2">
    <div class="card-body py-2">
        <div class="row align-items-start">

            {{-- Ticket identity --}}
            <div class="col-md-6">
                <h5 class="mb-0 font-weight-bold">
                    {{ $ticket->ticket_number }}
                    <span class="badge {{ $ticket->status?->badgeClass() }} mr-1">{{ $ticket->status?->label() }}</span>
                    <span class="badge {{ $ticket->category?->badgeClass() }}">{{ $ticket->category?->label() }}</span>
                </h5>
                <p class="text-muted mb-0 small">{{ $ticket->subject }}</p>
                <div class="mt-1 small">
                    <i class="la {{ $sourceIcons[$srcVal] ?? 'la-question' }}"></i>
                    <span>{{ $sourceLabels[$srcVal] ?? '' }}</span>
                    &nbsp;·&nbsp;
                    <span title="{{ $ticket->opened_at?->format('Y-m-d H:i') }}">
                        فُتحت {{ $ticket->opened_at?->diffForHumans() }}
                    </span>
                    @if($ticket->relatedPeriod)
                        &nbsp;·&nbsp;
                        <span>الفترة: {{ $ticket->relatedPeriod->getDisplayLabel() }}</span>
                    @endif
                </div>
            </div>

            {{-- Delegate info --}}
            <div class="col-md-3">
                <div class="small"><strong>المندوب:</strong> {{ $ticket->delegate?->name }}</div>
                @if($ticket->delegate?->platform_id)
                    <div class="small text-muted">المعرف: {{ $ticket->delegate->platform_id }}</div>
                @endif
                @if($ticket->delegate?->phone)
                    <div class="small text-muted">{{ $ticket->delegate->phone }}</div>
                @endif
            </div>

            {{-- SLA strip --}}
            <div class="col-md-3 text-right small">
                @if($ticket->sla_first_response_deadline)
                    <div>
                        <strong>الرد الأول:</strong>
                        @if($ticket->sla_first_response_met === 1)
                            <span class="text-success"><i class="la la-check"></i> في الوقت</span>
                        @elseif($ticket->sla_first_response_met === 0)
                            <span class="text-danger"><i class="la la-times"></i> متأخر</span>
                        @elseif(now()->gt($ticket->sla_first_response_deadline))
                            <span class="text-danger"><i class="la la-exclamation-triangle"></i> تجاوز الموعد</span>
                        @else
                            <span class="text-muted">
                                <i class="la la-clock-o"></i>
                                {{ $ticket->sla_first_response_deadline->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                @endif
                @if($ticket->sla_resolution_deadline)
                    <div class="mt-1">
                        <strong>الحل:</strong>
                        @if($ticket->sla_resolution_met === 1)
                            <span class="text-success"><i class="la la-check"></i> في الوقت</span>
                        @elseif($ticket->sla_resolution_met === 0)
                            <span class="text-danger"><i class="la la-times"></i> متأخر</span>
                        @elseif($ticket->resolved_at)
                            <span class="text-success"><i class="la la-check"></i> تم الحل</span>
                        @elseif(now()->gt($ticket->sla_resolution_deadline))
                            <span class="text-danger"><i class="la la-exclamation-triangle"></i> تجاوز الموعد</span>
                        @else
                            <span class="text-muted">
                                <i class="la la-clock-o"></i>
                                {{ $ticket->sla_resolution_deadline->diffForHumans() }}
                            </span>
                        @endif
                    </div>
                @endif
                @if($ticket->close_grace_deadline && $ticket->status?->value === 'resolved')
                    <div class="mt-1 text-warning">
                        <i class="la la-hourglass-half"></i>
                        الإغلاق التلقائي: {{ $ticket->close_grace_deadline->diffForHumans() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
