{{--
  Modal: Approve financial request.
  Variables: $fr (FinancialRequest with ticket, delegate loaded)
--}}
@php
    $defaultType   = $fr->request_category->defaultDeductionType();
    $deductionTypes = \App\Models\HungerStationFtrDelegateDeduction::DEDUCTION_TYPE_LABELS;
    $benefitTypes   = \App\Models\HungerStationFtrDelegateDeduction::BENEFIT_TYPE_LABELS;
    $currentMonth   = now()->month;
    $arabicMonths   = [
        1=>'يناير',2=>'فبراير',3=>'مارس',4=>'أبريل',5=>'مايو',6=>'يونيو',
        7=>'يوليو',8=>'أغسطس',9=>'سبتمبر',10=>'أكتوبر',11=>'نوفمبر',12=>'ديسمبر',
    ];
@endphp

<div class="modal fade" id="modal-approve-fr-{{ $fr->id }}" tabindex="-1" role="dialog"
     aria-labelledby="approveModalLabel-{{ $fr->id }}" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">

            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="approveModalLabel-{{ $fr->id }}">
                    <i class="la la-check-circle"></i> موافقة على الطلب المالي
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>

            <form method="POST"
                  action="{{ route('dashboard.support.financial-requests.approve', $fr) }}">
                @csrf @method('PATCH')

                <div class="modal-body">

                    {{-- Request summary (read-only) --}}
                    <div class="alert alert-light border small mb-3">
                        <strong>المندوب:</strong> {{ $fr->delegate?->name }} &nbsp;·&nbsp;
                        <strong>النوع:</strong> {{ $fr->request_category->label() }} &nbsp;·&nbsp;
                        <strong>المبلغ المطلوب:</strong> {{ number_format($fr->requested_amount, 2) }} ريال
                    </div>

                    <div class="row">

                        {{-- Approved Amount --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>المبلغ المعتمد <span class="text-danger">*</span></label>
                                <div class="input-group">
                                    <input type="number" name="approved_amount"
                                           class="form-control"
                                           value="{{ old('approved_amount', $fr->requested_amount) }}"
                                           min="0.01" max="999999.99" step="0.01" required>
                                    <div class="input-group-append">
                                        <span class="input-group-text">ريال</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Settlement Month (no workspace needed) --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>شهر التسوية <span class="text-danger">*</span></label>
                                <select name="settlement_month" class="form-control" required>
                                    <option value="">-- اختر الشهر --</option>
                                    @foreach($arabicMonths as $num => $name)
                                        <option value="{{ $num }}"
                                            {{ (old('settlement_month', $currentMonth) == $num) ? 'selected' : '' }}>
                                            {{ $name }}
                                        </option>
                                    @endforeach
                                </select>
                                <small class="text-muted">سنة التسوية: {{ now()->year }}</small>
                            </div>
                        </div>

                        {{-- Is Benefit toggle --}}
                        <div class="col-md-12">
                            <div class="form-group mb-1">
                                <label>نوع التأثير على الراتب <span class="text-danger">*</span></label>
                                <div class="d-flex" style="gap:16px;">
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="is_benefit_0_{{ $fr->id }}" name="is_benefit"
                                               value="0" class="custom-control-input approve-is-benefit-toggle"
                                               data-modal="{{ $fr->id }}"
                                               {{ old('is_benefit', '0') == '0' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_benefit_0_{{ $fr->id }}">
                                            خصم (يُخصم من الراتب)
                                        </label>
                                    </div>
                                    <div class="custom-control custom-radio">
                                        <input type="radio" id="is_benefit_1_{{ $fr->id }}" name="is_benefit"
                                               value="1" class="custom-control-input approve-is-benefit-toggle"
                                               data-modal="{{ $fr->id }}"
                                               {{ old('is_benefit') == '1' ? 'checked' : '' }}>
                                        <label class="custom-control-label" for="is_benefit_1_{{ $fr->id }}">
                                            إضافة (يُضاف للراتب)
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Deduction type (shown when is_benefit = 0) --}}
                        <div class="col-md-6" id="deduction-type-group-{{ $fr->id }}">
                            <div class="form-group">
                                <label>نوع الخصم <span class="text-danger">*</span></label>
                                <select name="deduction_type" id="deduction-type-select-{{ $fr->id }}"
                                        class="form-control" required>
                                    @foreach($deductionTypes as $key => $label)
                                        <option value="{{ $key }}"
                                            {{ old('deduction_type', $defaultType) === $key ? 'selected' : '' }}>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Benefit type (shown when is_benefit = 1) --}}
                        {{-- disabled prevents submission when hidden; JS toggles disabled on switch --}}
                        <div class="col-md-6 d-none" id="benefit-type-group-{{ $fr->id }}">
                            <div class="form-group">
                                <label>نوع الإضافة <span class="text-danger">*</span></label>
                                <select name="deduction_type" id="benefit-type-select-{{ $fr->id }}"
                                        class="form-control" disabled>
                                    @foreach($benefitTypes as $key => $label)
                                        <option value="{{ $key }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        {{-- Custom label (optional) --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>وصف مخصص <span class="text-muted small">(اختياري — يُكمَّل تلقائياً)</span></label>
                                <input type="text" name="approved_label" class="form-control"
                                       value="{{ old('approved_label') }}"
                                       placeholder="يُشتق تلقائياً من نوع الخصم"
                                       maxlength="200">
                            </div>
                        </div>

                        {{-- Notes --}}
                        <div class="col-md-6">
                            <div class="form-group">
                                <label>ملاحظات داخلية <span class="text-muted small">(اختياري)</span></label>
                                <textarea name="notes" class="form-control" rows="2"
                                          maxlength="500"
                                          placeholder="ملاحظات داخلية على الموافقة...">{{ old('notes') }}</textarea>
                            </div>
                        </div>

                        {{-- Delegate message: auto-filled from main composer via JS --}}
                        <div class="col-md-12">
                            <div class="form-group mb-0">
                                <label class="small text-muted">
                                    <i class="la la-info-circle"></i>
                                    رسالة للمندوب (من صندوق الرسائل):
                                </label>
                                <div class="small p-2 bg-light border rounded text-muted" style="min-height:40px; white-space:pre-wrap;"
                                     data-from-composer-preview>— لم تُكتب رسالة —</div>
                                <input type="hidden" name="delegate_message" data-from-composer>
                            </div>
                        </div>

                    </div>{{-- .row --}}

                </div>{{-- .modal-body --}}

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">إلغاء</button>
                    <button type="submit" class="btn btn-success">
                        <i class="la la-check"></i> تأكيد الموافقة
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<script>
(function () {
    var modalId = '{{ $fr->id }}';
    var deductionGroup = document.getElementById('deduction-type-group-' + modalId);
    var benefitGroup   = document.getElementById('benefit-type-group-' + modalId);
    var deductionSel   = document.getElementById('deduction-type-select-' + modalId);
    var benefitSel     = document.getElementById('benefit-type-select-' + modalId);

    document.querySelectorAll('.approve-is-benefit-toggle[data-modal="' + modalId + '"]').forEach(function(radio) {
        radio.addEventListener('change', function () {
            var isBenefit = this.value === '1';

            // Show/hide groups
            deductionGroup.classList.toggle('d-none', isBenefit);
            benefitGroup.classList.toggle('d-none', !isBenefit);

            // Disable the hidden select so it does NOT submit — only the visible one submits
            deductionSel.disabled = isBenefit;
            benefitSel.disabled   = !isBenefit;
        });
    });
}());
</script>
