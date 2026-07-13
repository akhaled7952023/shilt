{{--
  Modal: Reject financial request.
  Variables: $fr (FinancialRequest)
  Note: rejection_reason is drawn from the main composer (populated via JS on modal open).
--}}
<div class="modal fade" id="modal-reject-fr-{{ $fr->id }}" tabindex="-1" role="dialog"
     aria-labelledby="rejectModalLabel-{{ $fr->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="rejectModalLabel-{{ $fr->id }}">
                    <i class="la la-times-circle"></i> رفض الطلب المالي
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST"
                  action="{{ route('dashboard.support.financial-requests.reject', $fr) }}">
                @csrf @method('PATCH')
                <input type="hidden" name="rejection_reason" data-from-composer>

                <div class="modal-body">
                    <div class="alert alert-light border small mb-3">
                        <strong>المندوب:</strong> {{ $fr->delegate?->name }} &nbsp;·&nbsp;
                        <strong>النوع:</strong> {{ $fr->request_category->label() }} &nbsp;·&nbsp;
                        <strong>المبلغ المطلوب:</strong> {{ number_format($fr->requested_amount, 2) }} ريال
                    </div>

                    <div class="mb-1">
                        <label class="small font-weight-bold">سبب الرفض الذي سيُرسَل للمندوب:</label>
                        <div class="p-2 border rounded small bg-light"
                             style="min-height:60px; white-space:pre-wrap; color:#374151;"
                             data-from-composer-preview>— لم تُكتب رسالة —</div>
                        <small class="text-muted">
                            يُؤخذ هذا النص من صندوق الرسائل أدناه. أغلق هذه النافذة لتعديله.
                        </small>
                    </div>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-danger"
                            onclick="return confirm('تأكيد رفض الطلب المالي؟ لا يمكن التراجع.')">
                        <i class="la la-times"></i> تأكيد الرفض
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
