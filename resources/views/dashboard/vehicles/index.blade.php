@extends('layouts.dashboard.app')

@section('title') المركبات @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">المركبات</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-6 col-12 d-flex justify-content-end align-items-center mb-2">
                @can('create', \App\Models\Vehicle::class)
                    <a href="{{ route('dashboard.vehicles.create') }}" class="btn btn-primary">
                        <i class="la la-plus"></i> إضافة مركبة
                    </a>
                @endcan
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">

                        {{-- Filters --}}
                        <form method="GET" action="{{ route('dashboard.vehicles.index') }}" class="mb-2">
                            <div class="row">
                                <div class="col-md-3">
                                    <input type="text" name="search" class="form-control form-control-sm"
                                           placeholder="بحث برقم اللوحة، الماركة، الموديل، المندوب..."
                                           value="{{ $filters['search'] ?? '' }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="status" class="form-control form-control-sm">
                                        <option value="">كل الحالات</option>
                                        <option value="available"    {{ ($filters['status'] ?? '') === 'available'    ? 'selected' : '' }}>متاح</option>
                                        <option value="assigned"     {{ ($filters['status'] ?? '') === 'assigned'     ? 'selected' : '' }}>مُعيَّن</option>
                                        <option value="maintenance"  {{ ($filters['status'] ?? '') === 'maintenance'  ? 'selected' : '' }}>صيانة</option>
                                        <option value="retired"      {{ ($filters['status'] ?? '') === 'retired'      ? 'selected' : '' }}>متقاعد</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <select name="vehicle_type_id" class="form-control form-control-sm">
                                        <option value="">كل الأنواع</option>
                                        @foreach ($vehicleTypes as $type)
                                            <option value="{{ $type->id }}" {{ ($filters['vehicle_type_id'] ?? '') == $type->id ? 'selected' : '' }}>
                                                {{ $type->getTranslation('name', 'ar') }}
                                            </option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm btn-primary">
                                        <i class="la la-search"></i> بحث
                                    </button>
                                    <a href="{{ route('dashboard.vehicles.index') }}" class="btn btn-sm btn-secondary">
                                        <i class="la la-times"></i> إعادة تعيين
                                    </a>
                                </div>
                            </div>
                        </form>

                        <div class="table-responsive">
                            <table class="table table-hover table-sm">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>الصورة</th>
                                        <th>رقم اللوحة</th>
                                        <th>نوع المركبة</th>
                                        <th>الموديل</th>
                                        <th>المندوب الحالي</th>
                                        <th>الحالة</th>
                                        <th>الإجراءات</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($vehicles as $vehicle)
                                        <tr>
                                            <td>{{ $loop->iteration }}</td>
                                            <td>
                                                @if ($vehicle->vehicle_image)
                                                    <img src="{{ Storage::url($vehicle->vehicle_image) }}"
                                                         alt="{{ $vehicle->plate_number }}"
                                                         style="width:40px;height:40px;object-fit:cover;border-radius:4px;">
                                                @else
                                                    <div style="width:40px;height:40px;background:#e0e0e0;border-radius:4px;display:flex;align-items:center;justify-content:center;">
                                                        <i class="la la-car text-muted"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <a href="{{ route('dashboard.vehicles.show', $vehicle) }}" class="font-weight-bold">
                                                    {{ $vehicle->plate_number }}
                                                </a>
                                            </td>
                                            <td>{{ $vehicle->vehicleType?->getTranslation('name', 'ar') ?? '—' }}</td>
                                            <td>{{ $vehicle->make }} {{ $vehicle->model }}</td>
                                            <td>
                                                @if ($vehicle->activeAssignment?->delegate)
                                                    <a href="{{ route('dashboard.delegates.show', $vehicle->activeAssignment->delegate) }}">
                                                        {{ $vehicle->activeAssignment->delegate->name }}
                                                    </a>
                                                @else
                                                    <span class="text-muted">—</span>
                                                @endif
                                            </td>
                                            <td>@include('dashboard.components._status_badge', ['status' => $vehicle->status->value])</td>
                                            <td>
                                                <a href="{{ route('dashboard.vehicles.show', $vehicle) }}"
                                                   class="btn btn-sm btn-info" title="عرض">
                                                    <i class="la la-eye"></i>
                                                </a>
                                                @can('update', $vehicle)
                                                    <a href="{{ route('dashboard.vehicles.edit', $vehicle) }}"
                                                       class="btn btn-sm btn-warning" title="تعديل">
                                                        <i class="la la-edit"></i>
                                                    </a>
                                                @endcan
                                                @can('delete', $vehicle)
                                                    <form action="{{ route('dashboard.vehicles.destroy', $vehicle) }}"
                                                          method="POST" class="d-inline-block"
                                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه المركبة؟')">
                                                        @csrf @method('DELETE')
                                                        <button type="submit" class="btn btn-sm btn-danger" title="حذف">
                                                            <i class="la la-trash"></i>
                                                        </button>
                                                    </form>
                                                @endcan
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="8" class="text-center text-muted py-4">لا توجد مركبات</td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>

                        {{ $vehicles->withQueryString()->links() }}

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
