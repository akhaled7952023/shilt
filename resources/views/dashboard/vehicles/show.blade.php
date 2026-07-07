@extends('layouts.dashboard.app')

@section('title') ملف المركبة @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.vehicles.index') }}">المركبات</a></li>
                            <li class="breadcrumb-item active">{{ $vehicle->plate_number }}</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            {{-- بطاقة الرأس --}}
            <div class="card">
                <div class="card-content">
                    <div class="card-body">
                        <div class="row align-items-center">
                            <div class="col-md-1 text-center">
                                @if ($vehicle->vehicle_image)
                                    <img src="{{ Storage::url($vehicle->vehicle_image) }}"
                                         alt="{{ $vehicle->plate_number }}"
                                         style="width:80px;height:80px;object-fit:cover;border-radius:8px;border:3px solid #eee;">
                                @else
                                    <div style="width:80px;height:80px;background:#e0e0e0;border-radius:8px;margin:auto;display:flex;align-items:center;justify-content:center;">
                                        <i class="la la-car font-large-1 text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-7">
                                <h4 class="mb-0">{{ $vehicle->plate_number }}</h4>
                                <div class="mt-1">
                                    <span class="text-muted small">{{ $vehicle->make }} {{ $vehicle->model }}</span>
                                    @if ($vehicle->year)
                                        &nbsp;·&nbsp;
                                        <span class="text-muted small">{{ $vehicle->year }}</span>
                                    @endif
                                    @if ($vehicle->vehicleType)
                                        &nbsp;·&nbsp;
                                        <span class="text-muted small">{{ $vehicle->vehicleType->getTranslation('name', 'ar') }}</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    @include('dashboard.components._status_badge', ['status' => $vehicle->status->value])
                                    @if ($vehicle->color)
                                        <span class="badge badge-light ml-1">{{ $vehicle->color }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-left">
                                @can('update', $vehicle)
                                    <a href="{{ route('dashboard.vehicles.edit', $vehicle) }}"
                                       class="btn btn-primary btn-sm mb-1">
                                        <i class="la la-edit"></i> تعديل
                                    </a>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- التبويبات --}}
            @php $activeTab = session('tab', 'general'); @endphp
            <div class="card">
                <div class="card-content">
                    <div class="card-body p-0">

                        <ul class="nav nav-tabs" id="vehicleTabs" role="tablist">
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'general' ? 'active' : '' }}"
                                   id="tab-general" data-toggle="tab" href="#general" role="tab">
                                    <i class="la la-info-circle"></i> المعلومات العامة
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'documents' ? 'active' : '' }}"
                                   id="tab-documents" data-toggle="tab" href="#documents" role="tab">
                                    <i class="la la-file-text"></i> الوثائق
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'current-driver' ? 'active' : '' }}"
                                   id="tab-driver" data-toggle="tab" href="#current-driver" role="tab">
                                    <i class="la la-user"></i> السائق الحالي
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'driver-history' ? 'active' : '' }}"
                                   id="tab-driver-history" data-toggle="tab" href="#driver-history" role="tab">
                                    <i class="la la-history"></i> سجل السائقين
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'maintenance' ? 'active' : '' }}"
                                   id="tab-maintenance" data-toggle="tab" href="#maintenance" role="tab">
                                    <i class="la la-wrench"></i> الصيانة
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'violations' ? 'active' : '' }}"
                                   id="tab-violations" data-toggle="tab" href="#violations" role="tab">
                                    <i class="la la-exclamation-triangle"></i> المخالفات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'activity' ? 'active' : '' }}"
                                   id="tab-activity" data-toggle="tab" href="#activity" role="tab">
                                    <i class="la la-list-alt"></i> سجل النشاط
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-3" id="vehicleTabContent">

                            {{-- تبويب 1: المعلومات العامة --}}
                            <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th style="width:40%">رقم اللوحة</th>
                                                <td><strong>{{ $vehicle->plate_number }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>نوع المركبة</th>
                                                <td>{{ $vehicle->vehicleType?->getTranslation('name', 'ar') ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>الماركة</th>
                                                <td>{{ $vehicle->make }}</td>
                                            </tr>
                                            <tr>
                                                <th>الموديل</th>
                                                <td>{{ $vehicle->model }}</td>
                                            </tr>
                                            <tr>
                                                <th>سنة الصنع</th>
                                                <td>{{ $vehicle->year ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>اللون</th>
                                                <td>{{ $vehicle->color ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>رقم الهيكل</th>
                                                <td>{{ $vehicle->chassis_number ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
                                                <td>@include('dashboard.components._status_badge', ['status' => $vehicle->status->value])</td>
                                            </tr>
                                            <tr>
                                                <th>تاريخ الإضافة</th>
                                                <td>{{ $vehicle->created_at?->format('Y-m-d') ?? '—' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        {{-- صور المركبة --}}
                                        <p class="font-weight-bold text-muted mb-1">الصور</p>
                                        <div class="d-flex flex-wrap gap-2 mb-3">
                                            @if ($vehicle->vehicle_image)
                                                <div class="mr-2">
                                                    <p class="text-muted small mb-1">صورة المركبة</p>
                                                    <a href="{{ Storage::url($vehicle->vehicle_image) }}" target="_blank">
                                                        <img src="{{ Storage::url($vehicle->vehicle_image) }}"
                                                             style="width:80px;height:80px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                                    </a>
                                                </div>
                                            @endif
                                            @if ($vehicle->registration_image)
                                                <div class="mr-2">
                                                    <p class="text-muted small mb-1">صورة الاستمارة</p>
                                                    <a href="{{ Storage::url($vehicle->registration_image) }}" target="_blank">
                                                        <img src="{{ Storage::url($vehicle->registration_image) }}"
                                                             style="width:80px;height:80px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                                    </a>
                                                </div>
                                            @endif
                                            @if ($vehicle->insurance_image)
                                                <div class="mr-2">
                                                    <p class="text-muted small mb-1">صورة التأمين</p>
                                                    <a href="{{ Storage::url($vehicle->insurance_image) }}" target="_blank">
                                                        <img src="{{ Storage::url($vehicle->insurance_image) }}"
                                                             style="width:80px;height:80px;object-fit:cover;border-radius:4px;border:1px solid #ddd;">
                                                    </a>
                                                </div>
                                            @endif
                                            @if (!$vehicle->vehicle_image && !$vehicle->registration_image && !$vehicle->insurance_image)
                                                <span class="text-muted small">لا توجد صور</span>
                                            @endif
                                        </div>
                                        @if ($vehicle->notes)
                                            <p class="font-weight-bold text-muted mb-1">ملاحظات</p>
                                            <p>{{ $vehicle->notes }}</p>
                                        @endif
                                    </div>
                                </div>
                            </div>

                            {{-- تبويب 2: الوثائق --}}
                            <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" id="documents" role="tabpanel">
                                <div class="row">
                                    {{-- الاستمارة --}}
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="la la-file-text mr-1"></i> الاستمارة</strong>
                                            </div>
                                            <div class="card-body py-2">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th>رقم الاستمارة</th>
                                                        <td>{{ $vehicle->registration_number ?? '—' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاريخ الإصدار</th>
                                                        <td>{{ $vehicle->registration_issue_date?->format('Y-m-d') ?? '—' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاريخ الانتهاء</th>
                                                        <td>
                                                            @if ($vehicle->registration_expiry_date)
                                                                <span class="{{ $vehicle->registration_expiry_date->isPast() ? 'text-danger' : '' }}">
                                                                    {{ $vehicle->registration_expiry_date->format('Y-m-d') }}
                                                                </span>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- التأمين --}}
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="la la-shield mr-1"></i> التأمين</strong>
                                            </div>
                                            <div class="card-body py-2">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th>شركة التأمين</th>
                                                        <td>{{ $vehicle->insurance_company ?? '—' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>رقم الوثيقة</th>
                                                        <td>{{ $vehicle->insurance_policy_number ?? '—' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاريخ البداية</th>
                                                        <td>{{ $vehicle->insurance_start_date?->format('Y-m-d') ?? '—' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاريخ الانتهاء</th>
                                                        <td>
                                                            @if ($vehicle->insurance_expiry_date)
                                                                <span class="{{ $vehicle->insurance_expiry_date->isPast() ? 'text-danger' : '' }}">
                                                                    {{ $vehicle->insurance_expiry_date->format('Y-m-d') }}
                                                                </span>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- الفحص الدوري --}}
                                    <div class="col-md-4">
                                        <div class="card border">
                                            <div class="card-header bg-light py-2">
                                                <strong><i class="la la-check-circle mr-1"></i> الفحص الدوري</strong>
                                            </div>
                                            <div class="card-body py-2">
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th>رقم الفحص</th>
                                                        <td>{{ $vehicle->inspection_number ?? '—' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاريخ الإصدار</th>
                                                        <td>{{ $vehicle->inspection_issue_date?->format('Y-m-d') ?? '—' }}</td>
                                                    </tr>
                                                    <tr>
                                                        <th>تاريخ الانتهاء</th>
                                                        <td>
                                                            @if ($vehicle->inspection_expiry_date)
                                                                <span class="{{ $vehicle->inspection_expiry_date->isPast() ? 'text-danger' : '' }}">
                                                                    {{ $vehicle->inspection_expiry_date->format('Y-m-d') }}
                                                                </span>
                                                            @else
                                                                —
                                                            @endif
                                                        </td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- تبويب 3: السائق الحالي --}}
                            <div class="tab-pane fade {{ $activeTab === 'current-driver' ? 'show active' : '' }}" id="current-driver" role="tabpanel">
                                @include('dashboard.vehicles._current_driver', ['vehicle' => $vehicle])
                            </div>

                            {{-- تبويب 4: سجل السائقين --}}
                            <div class="tab-pane fade {{ $activeTab === 'driver-history' ? 'show active' : '' }}" id="driver-history" role="tabpanel">
                                @include('dashboard.vehicles._driver_history', ['vehicle' => $vehicle])
                            </div>

                            {{-- تبويب 5: الصيانة --}}
                            <div class="tab-pane fade {{ $activeTab === 'maintenance' ? 'show active' : '' }}" id="maintenance" role="tabpanel">
                                @include('dashboard.vehicles._maintenance', ['vehicle' => $vehicle])
                            </div>

                            {{-- تبويب 6: المخالفات --}}
                            <div class="tab-pane fade {{ $activeTab === 'violations' ? 'show active' : '' }}" id="violations" role="tabpanel">
                                @include('dashboard.vehicles._violations', ['vehicle' => $vehicle])
                            </div>

                            {{-- تبويب 7: سجل النشاط --}}
                            <div class="tab-pane fade {{ $activeTab === 'activity' ? 'show active' : '' }}" id="activity" role="tabpanel">
                                @php
                                    $auditService = app(\App\Services\AuditService::class);
                                    $timeline     = $auditService->getTimelineForVehicle($vehicle->id, 30);
                                @endphp
                                @forelse ($timeline as $entry)
                                    <div class="d-flex align-items-start mb-1 pb-1" style="border-bottom:1px solid #f0f0f0;">
                                        <div class="mr-2 mt-1" style="min-width:28px;">
                                            @switch($entry->action)
                                                @case('created')       <i class="la la-plus-circle text-success font-medium-3"></i>  @break
                                                @case('updated')       <i class="la la-edit text-info font-medium-3"></i>             @break
                                                @case('deleted')       <i class="la la-trash text-danger font-medium-3"></i>          @break
                                                @case('status_changed')<i class="la la-refresh text-warning font-medium-3"></i>       @break
                                                @case('assigned')      <i class="la la-user text-primary font-medium-3"></i>          @break
                                                @case('returned')      <i class="la la-undo text-secondary font-medium-3"></i>        @break
                                                @default               <i class="la la-info-circle text-muted font-medium-3"></i>
                                            @endswitch
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0" style="font-size:0.9rem;">
                                                @switch($entry->action)
                                                    @case('created')
                                                        @switch($entry->model_type)
                                                            @case('App\Models\Vehicle')          تم تسجيل المركبة           @break
                                                            @case('App\Models\VehicleAssignment') تم تعيين مندوب على المركبة @break
                                                            @case('App\Models\VehicleMaintenance') تم إضافة سجل صيانة       @break
                                                            @case('App\Models\VehicleViolation')   تم تسجيل مخالفة          @break
                                                            @default تم الإنشاء
                                                        @endswitch
                                                        @break
                                                    @case('updated')       تم تحديث البيانات @break
                                                    @case('status_changed')
                                                        تم تغيير الحالة
                                                        @if (!empty($entry->old_values['status']) && !empty($entry->new_values['status']))
                                                            من <span class="badge badge-secondary">{{ $entry->old_values['status'] }}</span>
                                                            إلى <span class="badge badge-primary">{{ $entry->new_values['status'] }}</span>
                                                        @endif
                                                        @break
                                                    @case('assigned') تم تعيين مندوب @break
                                                    @case('returned') تم إرجاع المركبة / فصل المندوب @break
                                                    @case('deleted')  تم حذف سجل @break
                                                    @default {{ $entry->action }}
                                                @endswitch
                                            </p>
                                            <small class="text-muted">
                                                {{ $entry->user?->name ?? 'النظام' }}
                                                &nbsp;·&nbsp;
                                                {{ $entry->created_at?->diffForHumans() ?? '—' }}
                                            </small>
                                        </div>
                                    </div>
                                @empty
                                    <div class="text-center text-muted py-4">لا توجد أحداث مسجلة</div>
                                @endforelse
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
