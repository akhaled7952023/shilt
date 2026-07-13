{{--
  Partial: chronological reply thread on ticket detail.
  Variables: $ticket (with replies loaded), $unreadReplyIds (int[])
--}}
@php $unreadReplyIds = $unreadReplyIds ?? []; @endphp

<div class="card mb-2">
    <div class="card-header py-2 d-flex align-items-center justify-content-between">
        <strong><i class="la la-comments"></i> المحادثة</strong>
        <span class="badge badge-secondary" style="font-size:11px;">
            {{ $ticket->replies->count() }} {{ $ticket->replies->count() === 1 ? 'رسالة' : 'رسائل' }}
        </span>
    </div>
    <div class="card-body" style="padding:16px 12px;">

        @if($ticket->replies->isEmpty())
            <div class="text-center py-5 text-muted">
                <i class="la la-comments" style="font-size:3rem; opacity:.2; display:block; margin-bottom:8px;"></i>
                <p class="small mb-0">لا توجد رسائل بعد.</p>
            </div>
        @else
            {{-- dir="ltr" normalises bubble placement: delegate=right, admin=left --}}
            <div dir="ltr" style="display:flex; flex-direction:column; gap:18px;">

                @foreach($ticket->replies->sortBy('created_at') as $reply)
                    @php
                        $isDelegate = $reply->author_type === 'delegate';
                        $isInternal = (bool) $reply->is_internal_note;
                        $isUnread   = in_array((int) $reply->id, $unreadReplyIds);
                        $authorName = $isDelegate
                            ? ($reply->authorDelegate?->name ?? 'مندوب')
                            : ($reply->authorUser?->name ?? 'مسؤول');
                        $initials   = mb_strtoupper(mb_substr($authorName, 0, 1, 'UTF-8'), 'UTF-8');

                        if ($isDelegate)        { $avatarBg = '#2563eb'; }
                        elseif ($isInternal)    { $avatarBg = '#d97706'; }
                        else                    { $avatarBg = '#475569'; }
                    @endphp

                    <div style="display:flex; flex-direction:column;
                                align-items:{{ $isDelegate ? 'flex-end' : 'flex-start' }};">

                        {{-- Author + meta row --}}
                        <div dir="rtl"
                             style="display:flex; align-items:center; gap:7px; margin-bottom:5px;
                                    {{ $isDelegate ? 'flex-direction:row-reverse;' : '' }}">

                            {{-- Avatar --}}
                            <div style="width:28px; height:28px; border-radius:50%; flex-shrink:0;
                                        display:flex; align-items:center; justify-content:center;
                                        background:{{ $avatarBg }}; color:#fff;
                                        font-size:11px; font-weight:700; letter-spacing:0;">
                                {{ $initials }}
                            </div>

                            <span style="font-size:12px; font-weight:600; color:#374151;">
                                {{ $authorName }}
                            </span>

                            <span style="font-size:11px; color:#9ca3af;"
                                  title="{{ $reply->created_at->format('Y-m-d H:i') }}">
                                {{ $reply->created_at->diffForHumans() }}
                            </span>

                            @if($isInternal)
                                <span class="badge badge-warning" style="font-size:10px; padding:2px 6px;">
                                    <i class="la la-lock"></i> داخلي
                                </span>
                            @endif

                            @if($isUnread)
                                <span class="badge badge-danger" style="font-size:10px; padding:2px 6px;">
                                    جديد
                                </span>
                            @endif

                        </div>

                        {{-- Message bubble --}}
                        <div dir="rtl" style="
                            max-width:85%;
                            padding:12px 16px;
                            border-radius:14px;
                            font-size:13.5px;
                            line-height:1.7;
                            white-space:pre-wrap;
                            word-break:break-word;
                            @if($isInternal)
                                background:#fef3c7; color:#92400e;
                                border:1.5px solid #fde68a;
                                border-bottom-left-radius:4px;
                            @elseif($isDelegate)
                                background:#2563eb; color:#fff;
                                border-bottom-right-radius:4px;
                            @else
                                background:#f8fafc; color:#1f2937;
                                border:1.5px solid #e5e7eb;
                                border-bottom-left-radius:4px;
                            @endif
                            @if($isUnread && !$isInternal)
                                box-shadow:0 0 0 2px #3b82f6;
                            @endif
                        ">{{ $reply->content }}</div>

                        {{-- Attachments --}}
                        @if($reply->attachments->isNotEmpty())
                            <div style="margin-top:6px; max-width:85%;
                                        display:flex; flex-wrap:wrap; gap:6px;
                                        {{ $isDelegate ? 'justify-content:flex-end;' : '' }}">
                                @foreach($reply->attachments as $att)
                                    @php $isImage = str_starts_with($att->mime_type ?? '', 'image/'); @endphp

                                    @if($isImage)
                                        {{-- Image thumbnail --}}
                                        <a href="{{ route('dashboard.support.tickets.attachments.download', [$ticket, $att]) }}"
                                           target="_blank"
                                           title="{{ $att->original_filename }}"
                                           style="display:inline-flex; align-items:center; justify-content:center;
                                                  width:72px; height:72px; border-radius:10px; overflow:hidden;
                                                  border:1.5px solid {{ $isDelegate ? 'rgba(255,255,255,.3)' : '#e5e7eb' }};
                                                  background:#f3f4f6; flex-shrink:0; text-decoration:none;">
                                            <i class="la la-image" style="font-size:26px; color:#9ca3af;"></i>
                                        </a>
                                    @else
                                        {{-- File card --}}
                                        <a href="{{ route('dashboard.support.tickets.attachments.download', [$ticket, $att]) }}"
                                           target="_blank"
                                           style="display:inline-flex; align-items:center; gap:6px;
                                                  padding:7px 12px; border-radius:10px; text-decoration:none;
                                                  font-size:12px; max-width:220px;
                                                  {{ $isDelegate
                                                     ? 'background:rgba(255,255,255,.18); color:#fff; border:1px solid rgba(255,255,255,.25);'
                                                     : 'background:#f3f4f6; color:#374151; border:1px solid #e5e7eb;' }}">
                                            <i class="la la-file-pdf-o" style="color:#ef4444; font-size:18px; flex-shrink:0;"></i>
                                            <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">
                                                {{ Str::limit($att->original_filename, 24) }}
                                            </span>
                                            <span style="font-size:10px; opacity:.65; flex-shrink:0;">
                                                {{ number_format($att->file_size / 1024, 0) }} KB
                                            </span>
                                        </a>
                                    @endif
                                @endforeach
                            </div>
                        @endif

                    </div>
                @endforeach

            </div>
        @endif

    </div>
</div>

@if(! empty($unreadReplyIds))
<style>
@keyframes unreadPulse {
    0%   { box-shadow: 0 0 0 4px rgba(59,130,246,.25); }
    100% { box-shadow: 0 0 0 0   rgba(59,130,246,0); }
}
</style>
@endif
