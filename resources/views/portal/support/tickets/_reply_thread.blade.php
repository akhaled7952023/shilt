{{--
    Partial: portal reply thread.
    Variables: $ticket (SupportTicket, replies already loaded, internal notes excluded)
--}}
@php $delegate = auth('delegate')->user(); @endphp

<div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:14px;">
    <h2 style="font-size:15px; font-weight:700; color:var(--text); margin:0;">
        {{ __('portal.ticket_replies_section') }}
    </h2>
    <span style="font-size:12px; color:var(--muted);">
        {{ $ticket->replies->count() }} {{ $ticket->replies->count() === 1 ? 'رسالة' : 'رسائل' }}
    </span>
</div>

@if($ticket->replies->isEmpty())
    <div style="padding:40px 20px; text-align:center; color:var(--muted); background:white;
                border-radius:var(--radius); border:1.5px dashed var(--border);">
        <i class="la la-comments" style="font-size:40px; opacity:.2; display:block; margin-bottom:12px;"></i>
        <p style="font-size:13px; margin:0;">{{ __('portal.ticket_no_replies') }}</p>
    </div>
@else
    {{-- dir="ltr" normalises bubble placement: delegate=right, admin=left --}}
    <div dir="ltr" style="display:flex; flex-direction:column; gap:18px;">
        @foreach($ticket->replies as $reply)
            @php
                $isDelegate = $reply->author_type === 'delegate';
            @endphp
            <div style="display:flex; flex-direction:column;
                        align-items:{{ $isDelegate ? 'flex-end' : 'flex-start' }};">

                {{-- Author label --}}
                <div dir="rtl" style="font-size:11px; color:var(--muted); margin-bottom:4px; padding:0 4px;">
                    @if($isDelegate)
                        <strong style="color:var(--text);">{{ __('portal.ticket_reply_by_you') }}</strong>
                    @else
                        <strong style="color:var(--text);">{{ __('portal.ticket_reply_by_admin') }}</strong>
                    @endif
                    &nbsp;·&nbsp;
                    <span title="{{ $reply->created_at?->format('Y-m-d H:i:s') }}">
                        {{ $reply->created_at?->diffForHumans() }}
                    </span>
                </div>

                {{-- Message bubble --}}
                <div dir="rtl" style="
                    max-width:85%; padding:12px 16px; border-radius:14px;
                    font-size:13.5px; line-height:1.6; white-space:pre-wrap; word-break:break-word;
                    {{ $isDelegate
                        ? 'background:var(--primary); color:white; border-bottom-right-radius:4px;'
                        : 'background:white; color:var(--text); border:1.5px solid var(--border); border-bottom-left-radius:4px;' }}
                ">{{ $reply->content }}</div>

                {{-- Attachments outside bubble — matches admin chat layout --}}
                @if($reply->attachments->isNotEmpty())
                    <div style="margin-top:6px; max-width:85%; display:flex; flex-wrap:wrap; gap:6px;
                                {{ $isDelegate ? 'justify-content:flex-end;' : '' }}">
                        @foreach($reply->attachments as $att)
                            @php $isImage = str_starts_with($att->mime_type ?? '', 'image/'); @endphp

                            @if($isImage)
                                <a href="{{ route('portal.support.tickets.attachments.download', [$ticket, $att]) }}"
                                   target="_blank"
                                   title="{{ $att->original_filename }}"
                                   style="display:inline-block; flex-shrink:0; text-decoration:none;
                                          border-radius:10px; overflow:hidden;
                                          border:1.5px solid {{ $isDelegate ? 'rgba(37,99,235,.3)' : 'var(--border)' }};
                                          box-shadow:0 1px 4px rgba(0,0,0,.10);">
                                    <img src="{{ route('portal.support.tickets.attachments.download', [$ticket, $att]) }}"
                                         alt="{{ $att->original_filename }}"
                                         loading="lazy"
                                         style="display:block; width:72px; height:72px; object-fit:cover;">
                                </a>
                            @else
                                <a href="{{ route('portal.support.tickets.attachments.download', [$ticket, $att]) }}"
                                   target="_blank"
                                   style="display:inline-flex; align-items:center; gap:6px;
                                          padding:6px 10px; border-radius:10px; text-decoration:none;
                                          font-size:12px; max-width:190px;
                                          {{ $isDelegate
                                             ? 'background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe;'
                                             : 'background:#f3f4f6; color:var(--text); border:1px solid var(--border);' }}">
                                    <i class="la la-file-alt" style="font-size:17px; flex-shrink:0;
                                       color:{{ $isDelegate ? '#2563eb' : 'var(--primary)' }};"></i>
                                    <span style="overflow:hidden; text-overflow:ellipsis; white-space:nowrap; flex:1; font-size:11px;">
                                        {{ Str::limit($att->original_filename, 20) }}
                                    </span>
                                    <i class="la la-download" style="font-size:12px; flex-shrink:0; opacity:.65;"></i>
                                </a>
                            @endif
                        @endforeach
                    </div>
                @endif

            </div>
        @endforeach
    </div>
@endif
