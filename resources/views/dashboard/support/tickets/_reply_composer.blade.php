{{--
  Partial: unified admin message composer.
  One textarea — action buttons decide what happens to the message.
  Variables: $ticket
--}}
@php
    $fr          = $ticket->financialRequest;
    $canResolve  = in_array($ticket->status?->value, ['open', 'in_progress', 'awaiting_delegate', 'reopened']);
    $isSuperAdmin = auth()->user()->isSuperAdmin();
@endphp

<div class="card mb-2" id="composer-card">
    <div class="card-header py-2">
        <strong><i class="la la-edit"></i> الرسائل</strong>
    </div>
    <div class="card-body p-3">

        @include('dashboard.includes.validations-errors')

        {{-- ① Main Reply Form (public reply + internal note + file upload) --}}
        <form id="main-reply-form"
              method="POST"
              action="{{ route('dashboard.support.tickets.reply', $ticket) }}"
              enctype="multipart/form-data">
            @csrf
            <input type="hidden" name="is_internal_note" id="note-flag" value="0">

            <div class="form-group mb-2">
                <textarea id="main-composer" name="content" class="form-control"
                          rows="4" maxlength="10000"
                          placeholder="اكتب رسالتك هنا...">{{ old('content') }}</textarea>
            </div>

            <div class="form-group mb-0">
                <input type="file" name="attachments[]" class="form-control-file small"
                       accept=".jpg,.jpeg,.png,.webp,.pdf" multiple>
                <small class="text-muted">بحد أقصى 3 ملفات · 10 ميجا · jpeg, png, webp, pdf</small>
            </div>

            {{-- Action button row --}}
            <div class="d-flex flex-wrap align-items-center mt-3 pt-2 border-top" style="gap:6px;">

                {{-- Send public reply --}}
                <button type="submit" class="btn btn-primary btn-sm"
                        onclick="document.getElementById('note-flag').value='0'">
                    <i class="la la-send"></i> رد عام
                </button>

                {{-- Internal note --}}
                <button type="submit" class="btn btn-sm btn-outline-warning"
                        onclick="document.getElementById('note-flag').value='1'">
                    <i class="la la-lock"></i> ملاحظة داخلية
                </button>

                @if($fr && $fr->isPending())
                    <span class="text-muted">|</span>
                    {{-- Request More Info --}}
                    <button type="button" class="btn btn-sm btn-outline-info" id="btn-req-info">
                        <i class="la la-question-circle"></i> طلب معلومات
                    </button>
                @endif

                @if($canResolve)
                    <span class="text-muted">|</span>
                    {{-- Resolve --}}
                    <button type="button" class="btn btn-sm btn-outline-success" id="btn-resolve">
                        <i class="la la-check"></i> حل التذكرة
                    </button>
                @endif

                @if($isSuperAdmin && ! $ticket->isClosedPermanently())
                    {{-- Force Close --}}
                    <button type="button" class="btn btn-sm btn-outline-danger" id="btn-force-close">
                        <i class="la la-lock"></i> إغلاق نهائي
                    </button>
                @endif

            </div>
        </form>

        {{-- ② Secondary hidden action forms (no file upload needed) --}}
        @if($fr && $fr->isPending())
            <form id="form-req-info" method="POST" style="display:none"
                  action="{{ route('dashboard.support.financial-requests.request-info', $fr) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="message" id="rinfo-message">
            </form>
        @endif

        @if($canResolve)
            <form id="form-resolve" method="POST" style="display:none"
                  action="{{ route('dashboard.support.tickets.resolve', $ticket) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="delegate_message" id="resolve-msg">
            </form>
        @endif

        @if($isSuperAdmin && ! $ticket->isClosedPermanently())
            <form id="form-fclose" method="POST" style="display:none"
                  action="{{ route('dashboard.support.tickets.close', $ticket) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="delegate_message" id="fclose-msg">
            </form>
        @endif

    </div>
</div>

<script>
(function () {
    var ta = document.getElementById('main-composer');
    if (!ta) return;

    // Request More Info
    var btnInfo = document.getElementById('btn-req-info');
    if (btnInfo) {
        btnInfo.addEventListener('click', function () {
            var msg = ta.value.trim();
            if (msg.length < 10) {
                alert('يرجى كتابة رسالة لا تقل عن 10 أحرف قبل طلب المعلومات.');
                ta.focus();
                return;
            }
            document.getElementById('rinfo-message').value = msg;
            document.getElementById('form-req-info').submit();
        });
    }

    // Resolve
    var btnResolve = document.getElementById('btn-resolve');
    if (btnResolve) {
        btnResolve.addEventListener('click', function () {
            var msg = ta.value.trim();
            var conf = msg.length
                ? 'تأكيد حل التذكرة وإرسال الرسالة للمندوب؟'
                : 'تأكيد حل التذكرة بدون رسالة للمندوب؟';
            if (!confirm(conf)) return;
            document.getElementById('resolve-msg').value = msg;
            document.getElementById('form-resolve').submit();
        });
    }

    // Force Close
    var btnFC = document.getElementById('btn-force-close');
    if (btnFC) {
        btnFC.addEventListener('click', function () {
            if (!confirm('إغلاق نهائي لا يمكن التراجع عنه إلا بإعادة الفتح يدوياً. متابعة؟')) return;
            document.getElementById('fclose-msg').value = ta.value.trim();
            document.getElementById('form-fclose').submit();
        });
    }

    // Pre-fill approve/reject modals from composer when they open
    if (typeof $ !== 'undefined') {
        $(document).on('show.bs.modal', function (e) {
            var val = ta.value;
            $(e.target).find('[data-from-composer]').val(val);
            $(e.target).find('[data-from-composer-preview]').text(
                val.trim() || '— لم تُكتب رسالة —'
            );
        });
    }
}());
</script>
