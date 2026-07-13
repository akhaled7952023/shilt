@if(isset($pendingPreview) && $pendingPreview['total'] > 0)
@php
    $hasImportable = $pendingPreview['importable'] > 0;
    $allSkipped    = $pendingPreview['importable'] === 0;
@endphp

<div class="alert alert-{{ $allSkipped ? 'secondary' : 'warning' }} alert-dismissible fade show d-flex align-items-center justify-content-between mb-2" style="border-right:4px solid {{ $allSkipped ? '#858796' : '#f6c23e' }}">
    <div>
        <i class="la la-clock-o mr-1"></i>
        <strong>قيود مالية معلقة:</strong>
        {{ $pendingPreview['total'] }} قيد
        @if($hasImportable)
            — <span class="text-success font-weight-bold">{{ $pendingPreview['importable'] }} قابل للاستيراد</span>
            ({{ number_format($pendingPreview['total_amount'], 2) }} ريال)
        @endif
        @if($pendingPreview['skipped'] > 0)
            — <span class="text-muted">{{ $pendingPreview['skipped'] }} سيُتخطى</span>
        @endif
    </div>
    <div class="d-flex align-items-center" style="gap:8px">
        <button type="button" class="btn btn-sm btn-outline-secondary" data-toggle="modal" data-target="#pendingEntriesModal">
            <i class="la la-eye"></i> عرض التفاصيل
        </button>

        @if($hasImportable)
            <form method="POST"
                  action="{{ route('dashboard.monthly.periods.hungerstation.ftr.settlement.pending-entries.apply', $period) }}"
                  onsubmit="return confirm('هل تريد استيراد جميع القيود المعلقة وتطبيقها على التسويات؟ لا يمكن التراجع عن هذا الإجراء.')">
                @csrf
                <button type="submit" class="btn btn-sm btn-warning font-weight-bold">
                    <i class="la la-upload"></i> استيراد الكل
                </button>
            </form>
        @endif

        <button type="button" class="close ml-0" data-dismiss="alert"><span>&times;</span></button>
    </div>
</div>

{{-- Preview Modal --}}
<div class="modal fade" id="pendingEntriesModal" tabindex="-1" role="dialog" aria-labelledby="pendingEntriesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="pendingEntriesModalLabel">
                    <i class="la la-list-ul mr-1"></i>
                    القيود المالية المعلقة — {{ $period->getDisplayLabel() }}
                </h5>
                <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
            </div>
            <div class="modal-body p-0">
                <table class="table table-sm table-bordered table-hover mb-0" dir="rtl">
                    <thead class="thead-light">
                        <tr>
                            <th>المندوب</th>
                            <th>النوع</th>
                            <th class="text-center">مزية / خصم</th>
                            <th class="text-left">المبلغ</th>
                            <th class="text-center">الشهر/السنة</th>
                            <th class="text-center">الحالة</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($pendingPreview['entries'] as $row)
                            <tr class="{{ $row->will_import ? '' : 'table-secondary text-muted' }}">
                                <td>{{ $row->delegate?->name ?? "#{$row->entry->delegate_id}" }}</td>
                                <td>{{ $row->entry->label }}</td>
                                <td class="text-center">
                                    @if($row->entry->is_benefit)
                                        <span class="badge badge-success">مزية</span>
                                    @else
                                        <span class="badge badge-danger">خصم</span>
                                    @endif
                                </td>
                                <td class="text-left font-weight-bold">{{ number_format((float)$row->entry->amount, 2) }}</td>
                                <td class="text-center">{{ $row->entry->settlement_month }}/{{ $row->entry->settlement_year }}</td>
                                <td class="text-center">
                                    @if($row->will_import)
                                        <span class="badge badge-warning"><i class="la la-check"></i> سيُستورد</span>
                                    @else
                                        <span class="badge badge-secondary" title="{{ $row->skip_reason }}">
                                            <i class="la la-times"></i> تخطي
                                        </span>
                                        <small class="d-block text-muted">{{ $row->skip_reason }}</small>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                    @if($hasImportable)
                        <tfoot class="thead-light">
                            <tr>
                                <td colspan="3" class="text-right font-weight-bold">الإجمالي القابل للاستيراد</td>
                                <td class="text-left font-weight-bold">{{ number_format($pendingPreview['total_amount'], 2) }}</td>
                                <td colspan="2"></td>
                            </tr>
                        </tfoot>
                    @endif
                </table>
            </div>
            <div class="modal-footer">
                @if($hasImportable)
                    <form method="POST"
                          action="{{ route('dashboard.monthly.periods.hungerstation.ftr.settlement.pending-entries.apply', $period) }}"
                          onsubmit="return confirm('هل تريد استيراد جميع القيود المعلقة الآن؟')">
                        @csrf
                        <button type="submit" class="btn btn-warning font-weight-bold">
                            <i class="la la-upload"></i> استيراد الكل
                        </button>
                    </form>
                @endif
                <button type="button" class="btn btn-secondary" data-dismiss="modal">إغلاق</button>
            </div>
        </div>
    </div>
</div>
@endif
