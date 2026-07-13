{{--
  Partial: financial request panel on ticket detail (admin dashboard).
  Variables: $ticket (with financialRequest.reviewer, financialRequest.pendingEntry loaded)
--}}
@if($ticket->financialRequest)
@php
    $fr = $ticket->financialRequest;
    $entry = $fr->pendingEntry;
    $frBadge = match($fr->status) {
        \App\Enums\FinancialRequestStatus::Approved  => ['class' => 'badge-success',  'label' => 'مُوافق عليه'],
        \App\Enums\FinancialRequestStatus::Rejected  => ['class' => 'badge-danger',   'label' => 'مرفوض'],
        \App\Enums\FinancialRequestStatus::NeedsInfo => ['class' => 'badge-warning',  'label' => 'بانتظار معلومات'],
        default                                      => ['class' => 'badge-secondary','label' => 'بانتظار المراجعة'],
    };
@endphp

<div class="card mb-2">
    <div class="card-header py-2 d-flex justify-content-between align-items-center">
        <strong><i class="la la-money-bill-wave"></i> الطلب المالي</strong>
        <span class="badge {{ $frBadge['class'] }}">{{ $frBadge['label'] }}</span>
    </div>
    <div class="card-body py-2">

        {{-- Request details --}}
        <table class="table table-sm table-borderless mb-0 small">
            <tr>
                <td class="text-muted pl-0" style="width:42%">نوع الطلب</td>
                <td class="font-weight-bold">{{ $fr->request_category->label() }}</td>
            </tr>
            <tr>
                <td class="text-muted pl-0">المبلغ المطلوب</td>
                <td class="font-weight-bold">{{ number_format($fr->requested_amount, 2) }} ريال</td>
            </tr>
            @if($fr->requested_notes)
            <tr>
                <td class="text-muted pl-0">ملاحظات المندوب</td>
                <td style="white-space:pre-wrap;">{{ $fr->requested_notes }}</td>
            </tr>
            @endif

            {{-- Approval details --}}
            @if($fr->isApproved())
            <tr><td colspan="2"><hr class="my-1"></td></tr>
            <tr>
                <td class="text-muted pl-0">المبلغ المعتمد</td>
                <td class="text-success font-weight-bold">{{ number_format($fr->approved_amount, 2) }} ريال</td>
            </tr>
            <tr>
                <td class="text-muted pl-0">نوع التسوية</td>
                <td>{{ \App\Models\HungerStationFtrDelegateDeduction::TYPE_LABELS[$fr->approved_deduction_type] ?? $fr->approved_deduction_type }}</td>
            </tr>
            @if($fr->settlement_month)
            <tr>
                <td class="text-muted pl-0">شهر التسوية</td>
                <td>{{ $fr->settlement_month }}/{{ $fr->settlement_year }}</td>
            </tr>
            @endif
            @if($fr->approved_notes)
            <tr>
                <td class="text-muted pl-0">ملاحظات</td>
                <td>{{ $fr->approved_notes }}</td>
            </tr>
            @endif
            @endif

            {{-- Rejection reason --}}
            @if($fr->isRejected() && $fr->rejection_reason)
            <tr><td colspan="2"><hr class="my-1"></td></tr>
            <tr>
                <td class="text-muted pl-0">سبب الرفض</td>
                <td class="text-danger">{{ $fr->rejection_reason }}</td>
            </tr>
            @endif

            {{-- Reviewer --}}
            @if($fr->reviewer)
            <tr>
                <td class="text-muted pl-0">راجع بواسطة</td>
                <td>{{ $fr->reviewer->name }}<br>
                    <span class="text-muted">{{ $fr->reviewed_at?->format('Y-m-d H:i') }}</span>
                </td>
            </tr>
            @endif
        </table>

        {{-- Pending Entry status + Cancel --}}
        @if($entry)
        <hr class="my-2">
        <div class="d-flex justify-content-between align-items-center small">
            <span>
                القيد المالي:
                <span class="badge {{ $entry->isPending() ? 'badge-info' : ($entry->isCancelled() ? 'badge-danger' : 'badge-success') }}">
                    {{ $entry->status->label() }}
                </span>
            </span>
            @if($entry->canBeCancelled() && auth()->user()->isSuperAdmin())
                <button type="button" class="btn btn-xs btn-outline-danger"
                        data-toggle="modal" data-target="#modal-cancel-entry-{{ $entry->id }}">
                    <i class="la la-ban"></i> إلغاء القيد
                </button>
            @endif
        </div>
        @endif

        {{-- Super Admin: Approve / Reject (only when reviewable) --}}
        @if(auth()->user()->isSuperAdmin() && ($fr->canBeApproved() || $fr->canBeRejected()))
        <hr class="my-2">
        <p class="small text-muted mb-1">
            <i class="la la-info-circle"></i>
            اكتب رسالتك في صندوق الرسائل أدناه، ثم اختر الإجراء.
        </p>
        <div class="d-flex" style="gap:6px;">
            @if($fr->canBeApproved())
            <button type="button" class="btn btn-sm btn-success"
                    data-toggle="modal" data-target="#modal-approve-fr-{{ $fr->id }}">
                <i class="la la-check-circle"></i> موافقة
            </button>
            @endif
            @if($fr->canBeRejected())
            <button type="button" class="btn btn-sm btn-danger"
                    id="btn-open-reject-{{ $fr->id }}"
                    data-reject-modal="#modal-reject-fr-{{ $fr->id }}">
                <i class="la la-times-circle"></i> رفض
            </button>
            @endif
        </div>
        @endif

    </div>
</div>

{{-- Approval Modal --}}
@if($fr->canBeApproved())
    @include('dashboard.support.tickets._financial_approve_modal', ['fr' => $fr])
@endif

{{-- Rejection Modal --}}
@if($fr->canBeRejected())
    @include('dashboard.support.tickets._financial_reject_modal', ['fr' => $fr])
@endif

{{-- Cancel Entry Modal --}}
@if($entry && $entry->canBeCancelled())
    @include('dashboard.support.tickets._financial_cancel_modal', ['entry' => $entry])
@endif

<script>
(function () {
    // Reject button: validate composer has >= 10 chars before opening modal
    var rejectBtn = document.getElementById('btn-open-reject-{{ $fr->id }}');
    if (rejectBtn) {
        rejectBtn.addEventListener('click', function () {
            var composer = document.getElementById('main-composer');
            var msg = composer ? composer.value.trim() : '';
            if (msg.length < 10) {
                alert('يرجى كتابة سبب الرفض في صندوق الرسائل (10 أحرف على الأقل) قبل الرفض.');
                if (composer) composer.focus();
                return;
            }
            $('#modal-reject-fr-{{ $fr->id }}').modal('show');
        });
    }
}());
</script>

@endif
