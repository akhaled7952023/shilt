{{-- نموذج إضافة سجل صيانة --}}
@can('update', $vehicle)
    <div class="mb-3">
        <button class="btn btn-sm btn-primary" type="button" data-toggle="collapse" data-target="#addMaintenanceForm">
            <i class="la la-plus"></i> إضافة سجل صيانة
        </button>
        <div class="collapse mt-2" id="addMaintenanceForm">
            <div class="card border">
                <div class="card-body">
                    <form action="{{ route('dashboard.vehicles.maintenance.store', $vehicle) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>التاريخ <span class="text-danger">*</span></label>
                                    <input type="date" name="date" class="form-control border-primary"
                                           value="{{ date('Y-m-d') }}" required>
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>التكلفة</label>
                                    <input type="number" name="cost" class="form-control border-primary"
                                           placeholder="0.00" step="0.01" min="0">
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>الحالة <span class="text-danger">*</span></label>
                                    <select name="status" class="form-control border-primary" required>
                                        <option value="completed">مكتمل</option>
                                        <option value="in_progress">جارٍ</option>
                                        <option value="pending">معلق</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الوصف <span class="text-danger">*</span></label>
                                    <textarea name="description" class="form-control border-primary" rows="2" required></textarea>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-group">
                                    <label>الملاحظات</label>
                                    <textarea name="notes" class="form-control border-primary" rows="2"></textarea>
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

{{-- قائمة سجلات الصيانة --}}
@if ($vehicle->vehicleMaintenance->isEmpty())
    <div class="text-center text-muted py-4">
        <i class="la la-wrench font-large-2"></i>
        <p class="mt-2">لا توجد سجلات صيانة</p>
    </div>
@else
    <div class="table-responsive">
        <table class="table table-sm table-hover">
            <thead>
                <tr>
                    <th>#</th>
                    <th>التاريخ</th>
                    <th>الوصف</th>
                    <th>التكلفة</th>
                    <th>الحالة</th>
                    <th>الملاحظات</th>
                    <th>الإجراءات</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($vehicle->vehicleMaintenance as $record)
                    <tr>
                        <td>{{ $loop->iteration }}</td>
                        <td>{{ $record->date?->format('Y-m-d') ?? '—' }}</td>
                        <td>{{ $record->description }}</td>
                        <td>{{ $record->cost ? number_format($record->cost, 2) . ' ر.س' : '—' }}</td>
                        <td>
                            @switch($record->status)
                                @case('completed')  <span class="badge badge-success">مكتمل</span>   @break
                                @case('in_progress')<span class="badge badge-warning">جارٍ</span>     @break
                                @case('pending')    <span class="badge badge-secondary">معلق</span>   @break
                            @endswitch
                        </td>
                        <td>{{ $record->notes ?? '—' }}</td>
                        <td>
                            @can('update', $vehicle)
                                <a href="{{ route('dashboard.vehicles.maintenance.edit', [$vehicle, $record]) }}"
                                   class="btn btn-sm btn-warning" title="تعديل">
                                    <i class="la la-edit"></i>
                                </a>
                                <form action="{{ route('dashboard.vehicles.maintenance.destroy', [$vehicle, $record]) }}"
                                      method="POST" class="d-inline-block"
                                      onsubmit="return confirm('هل أنت متأكد من حذف هذا السجل؟')">
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
