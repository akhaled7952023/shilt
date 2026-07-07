<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('dashboard.vehicle_assignments') }}</h4>
        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
        <div class="heading-elements">
            <ul class="mb-0 list-inline">
                <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                <li><a data-action="close"><i class="ft-x"></i></a></li>
            </ul>
        </div>
    </div>
    <div class="card-content collapse show">
        <div class="card-body">

            @php
                $assignmentService = app(\App\Services\Dashboard\Vehicles\IVehicleAssignmentService::class);
                $assignments = $assignmentService->getForVehicle($vehicle->id);
                $activeAssignment = $assignmentService->getActive($vehicle->id);
            @endphp

            <table class="table table-responsive-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>{{ __('dashboard.assigned_delegate') }}</th>
                        <th>{{ __('dashboard.assigned_at') }}</th>
                        <th>{{ __('dashboard.returned_at') }}</th>
                        <th>الحالة</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($assignments as $assignment)
                        <tr>
                            <th>{{ $loop->iteration }}</th>
                            <td>{{ $assignment->delegate?->name ?? '—' }}</td>
                            <td>{{ $assignment->assigned_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $assignment->returned_at?->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                @if ($assignment->is_active)
                                    <span class="badge badge-success">نشط</span>
                                @else
                                    <span class="badge badge-secondary">منتهي</span>
                                @endif
                            </td>
                            <td>
                                @if ($assignment->is_active)
                                    @can('update', $vehicle)
                                        <a href="javascript:void(0)"
                                           class="btn btn-sm btn-warning"
                                           onclick="document.getElementById('return-{{ $assignment->id }}').style.display='block'; this.style.display='none';">
                                            <i class="la la-undo"></i> إرجاع المركبة
                                        </a>
                                        <div id="return-{{ $assignment->id }}" style="display:none;">
                                            <form action="{{ route('dashboard.vehicles.assignments.return', [$vehicle, $assignment]) }}"
                                                  method="POST">
                                                @csrf @method('PATCH')
                                                <div class="input-group input-group-sm">
                                                    <input type="date" name="returned_at" class="form-control border-primary"
                                                           value="{{ now()->format('Y-m-d') }}" required>
                                                    <div class="input-group-append">
                                                        <button type="submit" class="btn btn-success btn-sm">تأكيد</button>
                                                        <button type="button" class="btn btn-secondary btn-sm"
                                                                onclick="this.closest('div[id]').style.display='none'; this.closest('td').querySelector('a').style.display='block'">إلغاء</button>
                                                    </div>
                                                </div>
                                            </form>
                                        </div>
                                    @endcan
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center">لا توجد تعيينات</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            {{-- نموذج تعيين مندوب جديد --}}
            @if ($vehicle->status->value === 'available')
                @can('update', $vehicle)
                    <hr>
                    <h6 class="mt-2 mb-1"><i class="la la-plus"></i> تعيين مندوب</h6>

                    @include('dashboard.includes.validations-errors')

                    <form action="{{ route('dashboard.vehicles.assignments.store', $vehicle) }}" method="POST">
                        @csrf
                        <div class="row">
                            <div class="col-md-5">
                                <div class="form-group">
                                    <label>المندوب <span class="text-danger">*</span></label>
                                    <select name="delegate_id" class="form-control border-primary">
                                        <option value="">اختر المندوب...</option>
                                        @foreach ($activeDelegates as $delegate)
                                            <option value="{{ $delegate->id }}"
                                                {{ old('delegate_id') == $delegate->id ? 'selected' : '' }}>
                                                {{ $delegate->name }}
                                            </option>
                                        @endforeach
                                    </select>
                                    @error('delegate_id') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-3">
                                <div class="form-group">
                                    <label>تاريخ التعيين <span class="text-danger">*</span></label>
                                    <input type="date" name="assigned_at" class="form-control border-primary"
                                           value="{{ old('assigned_at', now()->format('Y-m-d')) }}" required>
                                    @error('assigned_at') <span class="text-danger">{{ $message }}</span> @enderror
                                </div>
                            </div>
                            <div class="col-md-4">
                                <div class="form-group">
                                    <label>ملاحظات</label>
                                    <input type="text" name="notes" class="form-control border-primary"
                                           value="{{ old('notes') }}">
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary">
                                <i class="la la-check-square-o"></i> تعيين
                            </button>
                        </div>
                    </form>
                @endcan
            @endif

        </div>
    </div>
</div>
