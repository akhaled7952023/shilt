<div class="card">
    <div class="card-header">
        <h4 class="card-title">{{ __('dashboard.vehicle_documents') }}</h4>
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
                $vehicleDocumentService = app(\App\Services\Dashboard\Vehicles\IVehicleDocumentService::class);
                $vehicleDocs = $vehicleDocumentService->getForVehicle($vehicle->id);
            @endphp

            <table class="table table-responsive-sm">
                <thead>
                    <tr>
                        <th>#</th>
                        <th>نوع الوثيقة</th>
                        <th>تاريخ الإصدار</th>
                        <th>تاريخ الانتهاء</th>
                        <th>الحالة</th>
                        <th>الملف</th>
                        <th>الإجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($vehicleDocs as $doc)
                        <tr>
                            <th>{{ $loop->iteration }}</th>
                            <td>{{ $doc->documentType?->getTranslation('name', 'ar') ?? '—' }}</td>
                            <td>{{ $doc->issue_date?->format('Y-m-d') ?? '—' }}</td>
                            <td>{{ $doc->expiry_date?->format('Y-m-d') ?? '—' }}</td>
                            <td>
                                @include('dashboard.components._status_badge', [
                                    'status' => $vehicleDocumentService->getExpiryStatus($doc)
                                ])
                            </td>
                            <td>
                                <a href="{{ Storage::url($doc->file_path) }}" target="_blank"
                                   class="btn btn-sm btn-outline-info">
                                    <i class="la la-file-text"></i> عرض
                                </a>
                            </td>
                            <td>
                                @can('update', $vehicle)
                                    <a href="javascript:void(0)"
                                       class="btn btn-sm btn-danger"
                                       onclick="if(confirm('هل أنت متأكد من حذف هذه الوثيقة؟')){document.getElementById('del-vdoc-{{ $doc->id }}').submit();} return false">
                                        <i class="la la-trash"></i>
                                    </a>
                                @endcan
                            </td>
                        </tr>
                        <form id="del-vdoc-{{ $doc->id }}"
                              action="{{ route('dashboard.vehicles.documents.destroy', [$vehicle, $doc]) }}"
                              method="POST" style="display:none;">
                            @csrf @method('DELETE')
                        </form>
                    @empty
                        <tr>
                            <td colspan="7" class="text-center">لا توجد وثائق</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>

            @can('update', $vehicle)
                <hr>
                <h6 class="mt-2 mb-1"><i class="la la-upload"></i> رفع وثيقة جديدة</h6>

                @include('dashboard.includes.validations-errors')

                <form action="{{ route('dashboard.vehicles.documents.store', $vehicle) }}"
                      method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>نوع الوثيقة <span class="text-danger">*</span></label>
                                <select name="document_type_id" class="form-control border-primary">
                                    <option value="">اختر نوع الوثيقة...</option>
                                    @foreach ($documentTypes as $type)
                                        <option value="{{ $type->id }}"
                                            {{ old('document_type_id') == $type->id ? 'selected' : '' }}>
                                            {{ $type->getTranslation('name', 'ar') }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('document_type_id') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>تاريخ الإصدار</label>
                                <input type="date" name="issue_date" class="form-control border-primary"
                                       value="{{ old('issue_date') }}">
                                @error('issue_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label>تاريخ الانتهاء</label>
                                <input type="date" name="expiry_date" class="form-control border-primary"
                                       value="{{ old('expiry_date') }}">
                                @error('expiry_date') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label>الملف <span class="text-danger">*</span></label>
                                <input type="file" name="file" class="form-control border-primary"
                                       accept=".jpg,.jpeg,.png,.pdf">
                                @error('file') <span class="text-danger">{{ $message }}</span> @enderror
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-8">
                            <div class="form-group">
                                <label>ملاحظات</label>
                                <textarea name="notes" rows="2" class="form-control border-primary"
                                          placeholder="ملاحظات اختيارية...">{{ old('notes') }}</textarea>
                            </div>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-group w-100">
                                <button type="submit" class="btn btn-primary btn-block">
                                    <i class="la la-check-square-o"></i> رفع الوثيقة
                                </button>
                            </div>
                        </div>
                    </div>
                </form>
            @endcan

        </div>
    </div>
</div>
