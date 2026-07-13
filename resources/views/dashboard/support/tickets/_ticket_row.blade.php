{{-- Partial: single row in the ticket queue table. Variable: $ticket --}}
@php
    $sourceIcons = [
        'portal'    => ['icon' => 'la-globe',    'title' => 'البوابة'],
        'dashboard' => ['icon' => 'la-desktop',  'title' => 'لوحة التحكم'],
        'system'    => ['icon' => 'la-cogs',     'title' => 'النظام'],
    ];
    $src = $sourceIcons[$ticket->source?->value] ?? ['icon' => 'la-question', 'title' => ''];

    $priorityDots = [
        'low'    => 'background:#adb5bd',
        'normal' => 'background:#007bff',
        'high'   => 'background:#fd7e14',
        'urgent' => 'background:#dc3545',
    ];
    $dotStyle = $priorityDots[$ticket->priority?->value] ?? '';
@endphp
<tr>
    <td>
        <a href="{{ route('dashboard.support.tickets.show', $ticket) }}"
           class="font-weight-bold">
            {{ $ticket->ticket_number }}
        </a>
    </td>
    <td>
        <i class="la {{ $src['icon'] }} text-muted" title="{{ $src['title'] }}"></i>
    </td>
    <td>
        <span class="badge {{ $ticket->category?->badgeClass() }} badge-sm">
            {{ $ticket->category?->label() }}
        </span>
    </td>
    <td>
        <span style="display:inline-block;width:10px;height:10px;border-radius:50%;{{ $dotStyle }};vertical-align:middle;"
              title="{{ $ticket->priority?->label() }}"></span>
        <span class="small">{{ $ticket->priority?->label() }}</span>
    </td>
    <td class="small">
        {{ $ticket->delegate?->name }}
        @if($ticket->delegate?->platform_id)
            <br><span class="text-muted">{{ $ticket->delegate->platform_id }}</span>
        @endif
    </td>
    <td class="small">{{ Str::limit($ticket->subject, 55) }}</td>
    <td>
        <span class="badge {{ $ticket->status?->badgeClass() }}">
            {{ $ticket->status?->label() }}
        </span>
    </td>
    <td>
        @include('dashboard.support.tickets._sla_badge', ['ticket' => $ticket])
    </td>
    <td class="small text-muted" title="{{ $ticket->last_activity_at?->format('Y-m-d H:i') }}">
        {{ $ticket->last_activity_at?->diffForHumans() }}
    </td>
</tr>
