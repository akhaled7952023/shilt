{{--
  Modal: Cancel a pending financial entry (super_admin only).
  Variables: $entry (PendingFinancialEntry)
--}}
<div class="modal fade" id="modal-cancel-entry-{{ $entry->id }}" tabindex="-1" role="dialog"
     aria-labelledby="cancelModalLabel-{{ $entry->id }}" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">

            <div class="modal-header bg-warning">
                <h5 class="modal-title" id="cancelModalLabel-{{ $entry->id }}">
                    <i class="la la-ban"></i> إلغاء القيد المالي المعلق #{{ $entry->id }}
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST"
                  action="{{ route('dashboard.support.pending-entries.cancel', $entry) }}">
                @csrf @method('PATCH')

                <div class="modal-body">

                    <div class="alert alert-warning small mb-3">
                        <i class="la la-info-circle"></i>
                        سيُعاد الطلب المالي إلى حالة «بانتظار المراجعة» لإتاحة الموافقة من جديد بمعلومات صحيحة.
                    </div>

                    <div class="form-group mb-0">
                        <label>سبب الإلغاء <span class="text-danger">*</span></label>
                        <textarea name="cancel_reason" class="form-control" rows="3"
                                  required minlength="5" maxlength="500"
                                  placeholder="أدخل سبب الإلغاء..."></textarea>
                    </div>

                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-warning"
                            onclick="return confirm('تأكيد إلغاء القيد المالي؟')">
                        <i class="la la-ban"></i> تأكيد الإلغاء
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>
