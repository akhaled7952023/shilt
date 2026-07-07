@extends('layouts.portal.app')

@section('title', __('portal.notifications_title'))

@push('styles')
<style>
    .notif-item {
        display: flex;
        align-items: flex-start;
        gap: 14px;
        padding: 16px;
        border-bottom: 1px solid var(--border);
        transition: background .15s;
        position: relative;
    }
    .notif-item:last-child { border-bottom: none; }
    .notif-item.unread { background: #f8faff; }
    .notif-item.unread::before {
        content: '';
        position: absolute;
        top: 0; bottom: 0;
        {{ app()->getLocale() === 'ar' ? 'right: 0;' : 'left: 0;' }}
        width: 3px;
        background: var(--primary);
        border-radius: {{ app()->getLocale() === 'ar' ? '0 3px 3px 0' : '3px 0 0 3px' }};
    }
    .notif-icon-wrap {
        width: 42px; height: 42px; border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-size: 20px; flex-shrink: 0;
    }
    .notif-content { flex: 1; min-width: 0; }
    .notif-title { font-size: 14px; font-weight: 700; color: var(--text); line-height: 1.4; margin-bottom: 4px; }
    .notif-item.unread .notif-title { color: #0f172a; }
    .notif-body  { font-size: 13px; color: var(--muted); line-height: 1.55; margin-bottom: 6px; }
    .notif-meta  { font-size: 11px; color: #94a3b8; display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
    .notif-unread-dot {
        width: 8px; height: 8px; border-radius: 50%; background: var(--primary); flex-shrink: 0; margin-top: 6px;
    }
    .notif-read-btn {
        background: none; border: 1.5px solid var(--border); border-radius: 8px;
        color: var(--muted); padding: 4px 8px; cursor: pointer; font-size: 12px;
        white-space: nowrap; transition: all .15s; flex-shrink: 0;
        font-family: 'Tajawal', sans-serif; margin-top: 4px;
    }
    .notif-read-btn:hover { background: var(--primary-light); color: var(--primary); border-color: var(--primary); }
    .group-label {
        font-size: 11px; font-weight: 700; color: var(--muted);
        text-transform: uppercase; letter-spacing: .4px;
        padding: 10px 16px 6px;
        background: var(--bg);
        border-bottom: 1px solid var(--border);
    }
</style>
@endpush

@section('content')
@php
    $unreadTotal = $notifications->where(fn($n) => $n->isUnread())->count();

    // Group by date
    $today     = $notifications->filter(fn($n) => $n->created_at->isToday());
    $yesterday = $notifications->filter(fn($n) => $n->created_at->isYesterday());
    $earlier   = $notifications->filter(fn($n) => !$n->created_at->isToday() && !$n->created_at->isYesterday());
@endphp

{{-- ─── Page header ──────────────────────────────────────────── --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
    <div style="display:flex;align-items:center;gap:10px;">
        <div style="width:42px;height:42px;border-radius:12px;background:#eff6ff;color:#2563eb;
                    display:flex;align-items:center;justify-content:center;font-size:22px;">
            <i class="la la-bell"></i>
        </div>
        <div>
            <div style="font-size:18px;font-weight:800;color:var(--text);">{{ __('portal.notifications_title') }}</div>
            @if($unreadTotal > 0)
                <div style="font-size:12px;color:var(--muted);">
                    {{ $unreadTotal }} {{ $unreadTotal > 1 ? __('portal.unread_badge_plural') : __('portal.unread_badge') }}
                </div>
            @endif
        </div>
    </div>

    @if($unreadTotal > 0)
        <form method="POST" action="{{ route('portal.notifications.read-all') }}" style="margin:0;">
            @csrf
            <button type="submit"
                    style="background:var(--primary-light);color:var(--primary);border:1.5px solid #bfdbfe;
                           border-radius:9px;padding:8px 14px;font-family:'Tajawal',sans-serif;
                           font-size:13px;font-weight:600;cursor:pointer;
                           display:flex;align-items:center;gap:6px;">
                <i class="la la-check-circle" style="font-size:16px;"></i>
                {{ __('portal.mark_all_read') }}
            </button>
        </form>
    @endif
</div>

{{-- ─── Notifications list ───────────────────────────────────── --}}
@if($notifications->isEmpty())
    <div class="p-card">
        <div class="empty-state" style="padding:64px 24px;">
            <i class="la la-bell-slash"></i>
            <h6>{{ __('portal.no_notifications') }}</h6>
            <p>{{ __('portal.no_notifications_sub') }}</p>
        </div>
    </div>
@else
    <div class="p-card" style="overflow:hidden;">
        {{-- Today --}}
        @if($today->isNotEmpty())
            <div class="group-label">{{ __('portal.today_label') }}</div>
            @foreach($today as $n)
                @include('portal.notifications._item', ['n' => $n])
            @endforeach
        @endif

        {{-- Yesterday --}}
        @if($yesterday->isNotEmpty())
            <div class="group-label">{{ __('portal.yesterday_label') }}</div>
            @foreach($yesterday as $n)
                @include('portal.notifications._item', ['n' => $n])
            @endforeach
        @endif

        {{-- Earlier --}}
        @if($earlier->isNotEmpty())
            <div class="group-label">{{ __('portal.earlier_label') }}</div>
            @foreach($earlier as $n)
                @include('portal.notifications._item', ['n' => $n])
            @endforeach
        @endif
    </div>

    {{-- Pagination --}}
    @if($notifications->hasPages())
        <div style="margin-top:16px;display:flex;justify-content:center;">
            {{ $notifications->links() }}
        </div>
    @endif
@endif

@endsection

@push('scripts')
<script>
function markRead(id, btn) {
    const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
    fetch(`/portal/notifications/${id}/read`, {
        method: 'POST',
        headers: {
            'X-CSRF-TOKEN': csrfToken,
            'X-Requested-With': 'XMLHttpRequest',
        }
    }).then(res => {
        if (res.ok) {
            const item = btn.closest('.notif-item');
            item.classList.remove('unread');
            btn.remove();
            // Update badge in header
            const badge = document.getElementById('notif-badge');
            if (badge) {
                const count = parseInt(badge.textContent) - 1;
                if (count <= 0) { badge.remove(); } else { badge.textContent = count; }
            }
        }
    });
}
</script>
@endpush
