{{--
  Partial: SLA status indicator for the ticket queue table.
  Variables: $ticket (SupportTicket)
--}}
@php
    $now = now();
    $responseStatus = null; // 'met' | 'late' | 'pending' | null
    $resolutionStatus = null;

    if ($ticket->sla_first_response_deadline) {
        if ($ticket->sla_first_response_met === 1) {
            $responseStatus = 'met';
        } elseif ($ticket->sla_first_response_met === 0) {
            $responseStatus = 'late';
        } elseif ($now->gt($ticket->sla_first_response_deadline)) {
            $responseStatus = 'late';
        } else {
            $responseStatus = 'pending';
        }
    }

    if ($ticket->sla_resolution_deadline) {
        if ($ticket->sla_resolution_met === 1) {
            $resolutionStatus = 'met';
        } elseif ($ticket->sla_resolution_met === 0) {
            $resolutionStatus = 'late';
        } elseif ($now->gt($ticket->sla_resolution_deadline)) {
            $resolutionStatus = 'late';
        } else {
            $resolutionStatus = 'pending';
        }
    }

    $icons = [
        'met'     => ['icon' => 'la-check-circle', 'colour' => 'text-success', 'title' => 'في الوقت'],
        'late'    => ['icon' => 'la-times-circle',  'colour' => 'text-danger',  'title' => 'متأخر'],
        'pending' => ['icon' => 'la-clock-o',        'colour' => 'text-muted',   'title' => 'جارٍ'],
    ];
@endphp

<span class="small">
    @if($responseStatus)
        <i class="la {{ $icons[$responseStatus]['icon'] }} {{ $icons[$responseStatus]['colour'] }}"
           title="الرد الأول: {{ $icons[$responseStatus]['title'] }}"></i>
    @endif
    @if($resolutionStatus)
        <i class="la {{ $icons[$resolutionStatus]['icon'] }} {{ $icons[$resolutionStatus]['colour'] }}"
           title="الحل: {{ $icons[$resolutionStatus]['title'] }}"></i>
    @endif
    @if(! $responseStatus && ! $resolutionStatus)
        <span class="text-muted">—</span>
    @endif
</span>
