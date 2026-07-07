@php
    $link = null;
    if ($n->type === 'settlement_published' && isset($n->data['period_id'])) {
        $link = route('portal.settlements.show', $n->data['period_id']);
    }
    $isRtl = app()->getLocale() === 'ar';
@endphp

<div class="notif-item {{ $n->isUnread() ? 'unread' : '' }}" id="notif-{{ $n->id }}">
    <div class="notif-icon-wrap"
         style="background:{{ $n->typeBg() }};color:{{ $n->typeColor() }};">
        <i class="la {{ $n->typeIcon() }}"></i>
    </div>

    <div class="notif-content">
        <div class="notif-title">{{ $n->getLocalizedTitle() }}</div>
        @php $localizedBody = $n->getLocalizedBody(); @endphp
        @if($localizedBody)
            <div class="notif-body">{{ $localizedBody }}</div>
        @endif
        <div class="notif-meta">
            <span>
                <i class="la la-clock-o" style="{{ $isRtl ? 'margin-left:3px;' : 'margin-right:3px;' }}"></i>
                {{ $n->created_at->diffForHumans() }}
            </span>
            @if($link)
                <a href="{{ $link }}" style="color:var(--primary);font-weight:600;text-decoration:none;font-size:12px;">
                    <i class="la la-external-link" style="{{ $isRtl ? 'margin-left:2px;' : 'margin-right:2px;' }}"></i>{{ __('portal.view_settlement_link') }}
                </a>
            @endif
        </div>
    </div>

    @if($n->isUnread())
        <div style="display:flex;flex-direction:column;align-items:{{ $isRtl ? 'flex-end' : 'flex-start' }};gap:6px;flex-shrink:0;">
            <div class="notif-unread-dot"></div>
            <button class="notif-read-btn" onclick="markRead({{ $n->id }}, this)">
                <i class="la la-check" style="{{ $isRtl ? 'margin-left:3px;' : 'margin-right:3px;' }}"></i>{{ __('portal.mark_read_btn') }}
            </button>
        </div>
    @endif
</div>
