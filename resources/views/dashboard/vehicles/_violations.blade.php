{{-- نموذج إضافة مخالفة --}}
@can('update', $vehicle)
    <div class="mb-3">
        <button class="btn btn-sm btn-danger" type="button" data-toggle="collapse" data-target="#addViolationForm">
            <i class="la la-plus"></i> إضافة مخالفة
        </button>
        <div class="collapse mt-2" id="addViolationForm">
            <div class="card border">
                <div class="card-body">
                    @php
                        $warningTypes = app(\App\Services\Dashboard\MasterData\WarningTypes\IWarningTypeService::class)->getAllActive();
                        $delegates    = app(\App\Services\Dashboard\Delegates\IDelegateService::class)->getActive();
                    @endphp
                    <form action="{{ route('dashboard.vehicles.violations.store', $vehicle) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>نوع المخالفة</label>
                                    <select name="warning_type_id" class="form-control border-primary">
                                        <option value="">اختر النوع</option>
                                        @foreach ($warningTypes as $type)
                                            <option value="{{ $type->id }}">{{ $type->getTranslation('name', 'ar') }}</option>
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>المندوب</label>
                                    <select name="delegate_id" class="form-control border-primary">
                                        <option value="">اختر المندوب</option>
                                        @if ($vehicle->activeAssignment?->delegate)
                                            <option value="{{ $vehicle->activeAssignment->delegate->id }}" selected>
                                                {{ $vehicle->activeAssignment->delegate->name }}
                                            </option>
                                        @endif
                                        @foreach ($delegates as $d)
                                            @if (!$vehicle->activeAssignment || $d->id !== $vehicle->activeAssignment->delegate_id)
                                                <option value="{{ $d->id }}">{{ $d->name }} — {{ $d->delegate_code }}</option>
                                            @endif
                                        @endforeach
                                    </select>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>التاريخ <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control border-primary"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>القيمة</label>
                                    <input type="number" name="amount" class="form-control border-primary"
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-2">
                                <div class="form-group">
                                    <label>المكان</label>
                                    <input type="text" name="location" class="form-control border-primary">
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الملاحظات</label>
                                    <textarea name="notes" class="form-control border-primary" rows="2"></textarea>
                                </div>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-danger">
                            <i class="la la-check"></i> حفظ
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endcan

{{-- قائمة المخالفات --}}
@if ($vehicle->vehicleViolations->isEmpty())
    <div class="text-center text-muted py-4">
        <i class="la la-exclamation-triangle font-large-2"></i>
        <p class="mt-2">لا توجد مخالفات مسجلة</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>نوع المخالفة</th>
                    <th>المندوب</th>
                    <th>المكان</th>
                    <th>القيمة</th>
                    <th>الملاحظات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vehicle->vehicleViolations as $violation)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $violation->date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $violation->warningType?->getTranslation('name', 'ar') ?? '—' }}</td>
                        <td>
                            @if ($violation->delegate)
                                <a href="{{ route('dashboard.delegates.show', $violation->delegate) }}">
                                    {{ $violation->delegate->name }}
                                </a>
                            @else
                                <span class="text-muted">—</span>
                            @endif
                        </td>
                        <td>{{ $violation->location ?? '—' }}</td>
                        <td>{{ $violation->amount ? number_format($violation->amount, 2) . ' ر.س' : '—' }}</td>
                        <td>{{ $violation->notes ?? '—' }}</td>
                        <td>
                            @can('update', $vehicle)
                                <a href="{{ route('dashboard.vehicles.violations.edit', [$vehicle, $violation]) }}"
                                   class="btn btn-sm btn-warning" title="تعديل">
                                    <i class="la la-edit"></i>
                                </a>
                                <form action="{{ route('dashboard.vehicles.violations.destroy', [$vehicle, $violation]) }}"
                                      method="POST" class="d-inline-block"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذه المخالفة؟')">
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
@endif
