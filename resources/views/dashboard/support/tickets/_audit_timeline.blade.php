{{--
  Partial: per-ticket audit timeline.
  Variables: $ticket (with auditLogs loaded)
--}}
<div class="card mb-2">
    <div class="card-header py-2"><strong>سجل التدقيق</strong></div>
    <div class="card-body p-2">

        @if($ticket->auditLogs->isEmpty())
            <p class="text-muted small mb-0">لا توجد إدخالات في السجل بعد</p>
        @else
            <ul class="list-unstyled mb-0" style="border-right:3px solid #e2e8f0; padding-right:12px;">
                @foreach($ticket->auditLogs as $log)
                    @php
                        $actorClass = match($log->actor_type?->value) {
                            'admin'    => 'text-primary',
                            'delegate' => 'text-info',
                            default    => 'text-secondary',
                        };
                    @endphp
                    <li class="mb-2 position-relative">
                        <span style="position:absolute;right:-19px;top:4px;width:12px;height:12px;border-radius:50%;background:#6c757d;border:2px solid #fff;display:block;"></span>
                        <div class="small">
                            <span class="font-weight-bold {{ $actorClass }}">{{ $log->actor_label }}</span>
                            <span class="text-muted"> — </span>
                            <span>{{ $log->description }}</span>
                        </div>
                        @if($log->from_value || $log->to_value)
                            <div class="small text-muted">
                                @if($log->from_value)
                                    <span class="badge badge-light">{{ $log->from_value }}</span>
                                    <i class="la la-arrow-left"></i>
                                @endif
                                @if($log->to_value)
                                    <span class="badge badge-secondary">{{ $log->to_value }}</span>
                                @endif
                            </div>
                        @endif
                        <div class="text-muted" style="font-size:11px;">
                            {{ $log->created_at?->format('Y-m-d H:i') }}
                        </div>
                    </li>
                @endforeach
            </ul>
        @endif

    </div>
</div>
