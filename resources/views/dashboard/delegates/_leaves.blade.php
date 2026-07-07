{{-- نموذج إضافة إجازة --}}
@can('update', $delegate)
    <div class="mb-3">
        <button class="btn btn-sm btn-primary" type="button" data-toggle="collapse" data-target="#addLeaveForm">
            <i class="la la-plus"></i> إضافة إجازة
        </button>
        <div class="collapse mt-2" id="addLeaveForm">
            <div class="card border">
                <div class="card-body">
                    <form action="{{ route('dashboard.delegates.leaves.store', $delegate) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>نوع الإجازة <span class="text-danger">*</span></label>
                                    <select name="leave_type_id" class="form-control border-primary" required>
                                        <option value="">اختر النوع</option>
                                        @foreach ($leaveTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->getTranslation('name', 'ar') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>تاريخ البداية <span class="text-danger">*</span></label>
                                    <input type="date" name="start_date" class="form-control border-primary"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>تاريخ النهاية <span class="text-danger">*</span></label>
                                    <input type="date" name="end_date" class="form-control border-primary"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>ملاحظات</label>
                                    <input type="text" name="notes" class="form-control border-primary">
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-success">
                            <i class="la la-check"></i> حفظ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- جدول الإجازات --}}
@if ($leaves->isEmpty())
    <div class="text-center text-muted py-4">
        <i class="la la-calendar font-large-2"></i>
        <p class="mt-2">لا توجد إجازات مسجلة</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>نوع الإجازة</th>
                    <th>تاريخ البداية</th>
                    <th>تاريخ النهاية</th>
                    <th>عدد الأيام</th>
                    <th>الملاحظات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($leaves as $leave)
                    <tr>
                        <td>{{ $leaves->firstItem() + $loop->index }}</td>
                        <td>{{ $leave->leaveType?->getTranslation('name', 'ar') ?? '—' }}</td>
                        <td>{{ $leave->start_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $leave->end_date?->format('Y-m-d') ?? '—' }}</td>
                        <td>
                            @if ($leave->start_date && $leave->end_date)
                                {{ $leave->start_date->diffInDays($leave->end_date) + 1 }} يوم
                            @else
                                —
                            @endif
                        </td>
                        <td>{{ $leave->notes ?? '—' }}</td>
                        <td>
                            @can('update', $delegate)
                                <a href="{{ route('dashboard.delegates.leaves.edit', [$delegate, $leave]) }}"
                                   class="btn btn-sm btn-warning" title="تعديل">
                                    <i class="la la-edit"></i>
                                </a>
                                <form action="{{ route('dashboard.delegates.leaves.destroy', [$delegate, $leave]) }}"
                                      method="POST" class="d-inline-block"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه الإجازة؟')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                        <i class="la la-trash"></i>
                                    </button>
                                </form>
                            @endcan
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ترقيم الصفحات --}}
    <div class="d-flex justify-content-center mt-3">
        {{ $leaves->links() }}
    </div>
@endif
