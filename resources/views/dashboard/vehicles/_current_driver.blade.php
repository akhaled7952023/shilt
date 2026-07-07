@php $activeAssignment = $vehicle->activeAssignment; @endphp

@if ($activeAssignment && $activeAssignment->delegate)
    {{-- عرض المندوب الحالي --}}
    <div class="d-flex align-items-center p-3 border rounded mb-3">
        <div class="mr-3">
            @if ($activeAssignment->delegate->profile_photo)
                <img src="{{ Storage::url($activeAssignment->delegate->profile_photo) }}"
                     style="width:60px;height:60px;object-fit:cover;border-radius:50%;border:2px solid #eee;">
            @else
                <div style="width:60px;height:60px;background:#e0e0e0;border-radius:50%;display:flex;align-items:center;justify-content:center;">
                    <i class="la la-user font-large-1 text-muted"></i>
                </div>
            @endif
        </div>
        <div class="flex-grow-1">
            <h5 class="mb-1">
                <a href="{{ route('dashboard.delegates.show', $activeAssignment->delegate) }}">
                    {{ $activeAssignment->delegate->name }}
                </a>
            </h5>
            <table class="table table-borderless table-sm mb-0" style="max-width:400px;">
                <tr>
                    <th>كود المندوب</th>
                    <td>{{ $activeAssignment->delegate->delegate_code ?? '—' }}</td>
                </tr>
                <tr>
                    <th>الجوال</th>
                    <td>{{ $activeAssignment->delegate->phone ?? '—' }}</td>
                </tr>
                <tr>
                    <th>المدينة</th>
                    <td>{{ $activeAssignment->delegate->city?->getTranslation('name', 'ar') ?? '—' }}</td>
                </tr>
                <tr>
                    <th>المنصة</th>
                    <td>{{ $activeAssignment->delegate->platform?->name ?? '—' }}</td>
                </tr>
                <tr>
                    <th>تاريخ استلام المركبة</th>
                    <td>{{ $activeAssignment->assigned_at?->format('Y-m-d') ?? '—' }}</td>
                </tr>
            </table>
        </div>
    </div>

    {{-- زر فصل المندوب --}}
    @can('update', $vehicle)
        <form action="{{ route('dashboard.vehicles.assignments.unassign', $vehicle) }}"
              method="POST"
              onsubmit="return confirm('هل أنت متأكد من فصل المندوب عن هذه المركبة؟')">
            @csrf @method('DELETE')
            <button type="submit" class="btn btn-danger btn-sm">
                <i class="la la-unlink"></i> فصل المندوب
            </button>
        </form>
    @endcan

@else
    {{-- لا يوجد مندوب حالياً --}}
    <div class="text-center text-muted py-4 mb-3">
        <i class="la la-user font-large-2"></i>
        <p class="mt-2">لا يوجد مندوب مرتبط حالياً</p>
    </div>

    {{-- نموذج التعيين: يظهر فقط عند غياب مندوب نشط --}}
    @can('update', $vehicle)
        <hr>
        <h6 class="font-weight-bold mb-2"><i class="la la-user-plus"></i> تعيين مندوب</h6>
        <form action="{{ route('dashboard.vehicles.assignments.store', $vehicle) }}" method="POST">
            @csrf
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>المندوب <span class="text-danger">*</span></label>
                        @php $activeDelegates = app(\App\Services\Dashboard\Delegates\IDelegateService::class)->getActiveByPlatformCode('hungerstation'); @endphp
                        <select name="delegate_id" id="delegate_select" class="form-control border-primary" required>
                            <option value="">اختر المندوب...</option>
                            @foreach ($activeDelegates as $delegate)
                                <option value="{{ $delegate->id }}">
                                    {{ $delegate->name }} — {{ $delegate->delegate_code ?? '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>تاريخ التعيين <span class="text-danger">*</span></label>
                        <input type="date" name="assigned_at" class="form-control border-primary"
                               value="{{ date('Y-m-d') }}" required>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="form-group">
                        <label>ملاحظات</label>
                        <input type="text" name="notes" class="form-control border-primary">
                    </div>
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <div class="form-group w-100">
                        <button type="submit" class="btn btn-primary btn-block">
                            <i class="la la-check"></i> تعيين
                        </button>
                    </div>
                </div>
            </div>
        </form>
    @endcan

    @section('scripts')
    <link rel="stylesheet" href="{{ asset('asset/dashboard/vendors/css/forms/selects/select2.min.css') }}">
    <script src="{{ asset('asset/dashboard/vendors/js/forms/select/select2.full.min.js') }}"></script>
    <script>
    $(document).ready(function () {
        $('#delegate_select').select2({
            placeholder: 'اختر المندوب...',
            allowClear: true,
            dir: 'rtl',
            width: '100%',
            language: {
                noResults: function () { return 'لا توجد نتائج'; },
                searching: function () { return 'جارٍ البحث...'; }
            }
        });
    });
    </script>
    @endsection

@endif
