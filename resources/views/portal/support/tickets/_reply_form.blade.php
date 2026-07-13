{{-- Inner reply form partial — included only when delegate IS allowed to reply --}}
<div style="margin-top:20px;" class="p-card" style="padding:16px;">
    <div style="padding:16px;">
        <h3 style="font-size:14px; font-weight:700; color:var(--text); margin:0 0 12px;">
            {{ __('portal.ticket_reply_form_label') }}
        </h3>

        <form method="POST" action="{{ route('portal.support.tickets.reply', $ticket) }}"
              enctype="multipart/form-data">
            @csrf

            <textarea name="content"
                      class="portal-input @error('content') is-invalid @enderror"
                      placeholder="{{ __('portal.ticket_reply_placeholder') }}"
                      rows="4" maxlength="10000" required
                      style="resize:vertical; margin-bottom:10px;">{{ old('content') }}</textarea>
            @error('content')
                <div style="color:var(--danger); font-size:12px; margin-bottom:8px;">{{ $message }}</div>
            @enderror

            <div style="margin-bottom:14px;">
                <input type="file" name="attachments[]"
                       class="portal-input"
                       accept=".jpg,.jpeg,.png,.webp,.pdf" multiple
                       style="padding:8px; cursor:pointer; font-size:13px;">
                <div style="font-size:11px; color:var(--muted); margin-top:4px;">
                    {{ __('portal.ticket_form_attachments_hint') }}
                </div>
                @error('attachments')
                    <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
                @error('attachments.*')
                    <div style="color:var(--danger); font-size:12px; margin-top:4px;">{{ $message }}</div>
                @enderror
            </div>

            <button type="submit" class="portal-btn portal-btn-primary">
                {{ __('portal.ticket_reply_submit') }}
            </button>
        </form>
    </div>
</div>
