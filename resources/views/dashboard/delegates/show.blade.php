@extends('layouts.dashboard.app')

@section('title') ملف المندوب @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.delegates.index') }}">المناديب</a></li>
                            <li class="breadcrumb-item active">{{ $delegate->name }}</li>
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
                                @if ($delegate->profile_photo)
                                    <img src="{{ Storage::url($delegate->profile_photo) }}"
                                         alt="{{ $delegate->name }}"
                                         style="width:80px;height:80px;object-fit:cover;border-radius:50%;border:3px solid #eee;">
                                @else
                                    <div style="width:80px;height:80px;background:#e0e0e0;border-radius:50%;margin:auto;display:flex;align-items:center;justify-content:center;">
                                        <i class="la la-user font-large-1 text-muted"></i>
                                    </div>
                                @endif
                            </div>
                            <div class="col-md-7">
                                <h4 class="mb-0">{{ $delegate->name }}</h4>
                                <div class="mt-1">
                                    <span class="text-muted small">{{ $delegate->delegate_code ?? '—' }}</span>
                                    &nbsp;·&nbsp;
                                    @if ($delegate->phone)
                                        <span class="text-muted small"><i class="la la-phone"></i> {{ $delegate->phone }}</span>
                                    @endif
                                    @if ($delegate->city)
                                        &nbsp;·&nbsp;
                                        <span class="text-muted small"><i class="la la-map-marker"></i> {{ $delegate->city->getTranslation('name', 'ar') }}</span>
                                    @endif
                                </div>
                                <div class="mt-1">
                                    @include('dashboard.components._status_badge', ['status' => $delegate->status->value])
                                    @if ($delegate->platform)
                                        <span class="badge badge-info mr-1">{{ $delegate->platform->name }}</span>
                                    @endif
                                </div>
                            </div>
                            <div class="col-md-4 text-left">
                                @can('update', $delegate)
                                    <a href="{{ route('dashboard.delegates.edit', $delegate) }}"
                                       class="btn btn-primary btn-sm mb-1">
                                        <i class="la la-edit"></i> تعديل
                                    </a>
                                @endcan
                                @can('updateStatus', $delegate)
                                    <form action="{{ route('dashboard.delegates.update-status', $delegate) }}"
                                          method="POST" class="d-inline-block mb-1">
                                        @csrf @method('PATCH')
                                        <select name="status" class="form-control form-control-sm d-inline-block"
                                                style="width:auto;" onchange="this.form.submit()">
                                            <option value="active"     {{ $delegate->status->value === 'active'     ? 'selected' : '' }}>نشط</option>
                                            <option value="inactive"   {{ $delegate->status->value === 'inactive'   ? 'selected' : '' }}>غير نشط</option>
                                            <option value="suspended"  {{ $delegate->status->value === 'suspended'  ? 'selected' : '' }}>موقوف</option>
                                            <option value="terminated" {{ $delegate->status->value === 'terminated' ? 'selected' : '' }}>منتهي</option>
                                        </select>
                                    </form>
                                @endcan
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- التبويبات --}}
            <div class="card">
                <div class="card-content">
                    <div class="card-body p-0">

                        @php $activeTab = request('tab', session('tab', 'general')); @endphp

                        <ul class="nav nav-tabs" id="delegateTabs" role="tablist">
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
                                <a class="nav-link {{ $activeTab === 'vehicle' ? 'active' : '' }}"
                                   id="tab-vehicle" data-toggle="tab" href="#vehicle" role="tab">
                                    <i class="la la-car"></i> المركبة الحالية
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'vehicles' ? 'active' : '' }}"
                                   id="tab-vehicles" data-toggle="tab" href="#vehicles" role="tab">
                                    <i class="la la-history"></i> سجل المركبات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'leaves' ? 'active' : '' }}"
                                   id="tab-leaves" data-toggle="tab" href="#leaves" role="tab">
                                    <i class="la la-calendar"></i> الإجازات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'records' ? 'active' : '' }}"
                                   id="tab-records" data-toggle="tab" href="#records" role="tab">
                                    <i class="la la-list-alt"></i> السجلات
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link {{ $activeTab === 'portal' ? 'active' : '' }}"
                                   id="tab-portal" data-toggle="tab" href="#portal" role="tab">
                                    <i class="la la-mobile"></i> بوابة المندوب
                                    @if($delegate->portal_enabled)
                                        <span class="badge badge-success badge-pill ml-1" style="font-size:9px;">مفعّل</span>
                                    @endif
                                </a>
                            </li>
                        </ul>

                        <div class="tab-content p-3" id="delegateTabContent">

                            {{-- تبويب 1: المعلومات العامة --}}
                            <div class="tab-pane fade {{ $activeTab === 'general' ? 'show active' : '' }}" id="general" role="tabpanel">
                                <div class="row">
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th style="width:40%">كود المندوب</th>
                                                <td><strong>{{ $delegate->delegate_code ?? '—' }}</strong></td>
                                            </tr>
                                            <tr>
                                                <th>الاسم</th>
                                                <td>{{ $delegate->name }}</td>
                                            </tr>
                                            <tr>
                                                <th>رقم الهوية</th>
                                                <td>{{ $delegate->national_id ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>رقم الجوال</th>
                                                <td>{{ $delegate->phone ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>المدينة</th>
                                                <td>{{ $delegate->city?->getTranslation('name', 'ar') ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>الحالة</th>
                                                <td>@include('dashboard.components._status_badge', ['status' => $delegate->status->value])</td>
                                            </tr>
                                        </table>
                                    </div>
                                    <div class="col-md-6">
                                        <table class="table table-borderless table-sm">
                                            <tr>
                                                <th style="width:40%">المنصة</th>
                                                <td>{{ $delegate->platform?->name ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>اسم البنك</th>
                                                <td>{{ $delegate->bank_name ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>رقم الآيبان (IBAN)</th>
                                                <td>{{ $delegate->iban ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>تاريخ الإضافة</th>
                                                <td>{{ $delegate->created_at?->format('Y-m-d') ?? '—' }}</td>
                                            </tr>
                                        </table>
                                    </div>
                                </div>
                            </div>

                            {{-- تبويب 2: الوثائق --}}
                            <div class="tab-pane fade {{ $activeTab === 'documents' ? 'show active' : '' }}" id="documents" role="tabpanel">
                                @include('dashboard.delegates._documents', [
                                    'delegate'    => $delegate,
                                    'iqamaType'   => $iqamaType,
                                    'licenseType' => $licenseType,
                                ])
                            </div>

                            {{-- تبويب 3: المركبة الحالية --}}
                            <div class="tab-pane fade {{ $activeTab === 'vehicle' ? 'show active' : '' }}" id="vehicle" role="tabpanel">
                                @php
                                    $activeAssignment = $delegate->vehicleAssignments->firstWhere('is_active', true);
                                @endphp
                                @if ($activeAssignment)
                                    <div class="d-flex align-items-center p-3 border rounded">
                                        <div class="mr-3">
                                            <i class="la la-car font-large-2 text-primary"></i>
                                        </div>
                                        <div>
                                            <h5 class="mb-1">
                                                <a href="{{ route('dashboard.vehicles.show', $activeAssignment->vehicle) }}">
                                                    {{ $activeAssignment->vehicle?->plate_number }}
                                                </a>
                                            </h5>
                                            <p class="mb-0 text-muted">
                                                {{ $activeAssignment->vehicle?->make }}
                                                {{ $activeAssignment->vehicle?->model }}
                                                @if ($activeAssignment->vehicle?->vehicleType)
                                                    &mdash; {{ $activeAssignment->vehicle->vehicleType->getTranslation('name', 'ar') }}
                                                @endif
                                            </p>
                                            <small class="text-muted">
                                                تاريخ التعيين: {{ $activeAssignment->assigned_at?->format('Y-m-d') ?? '—' }}
                                            </small>
                                        </div>
                                    </div>
                                @else
                                    <div class="text-center text-muted py-5">
                                        <i class="la la-car font-large-2"></i>
                                        <p class="mt-2">لا توجد مركبة تابعة للشركة</p>
                                    </div>
                                @endif
                            </div>

                            {{-- تبويب 4: سجل المركبات --}}
                            <div class="tab-pane fade {{ $activeTab === 'vehicles' ? 'show active' : '' }}" id="vehicles" role="tabpanel">
                                @php
                                    $vehicleHistory = $delegate->vehicleAssignments->where('is_active', false)->sortByDesc('assigned_at');
                                @endphp
                                @if ($vehicleHistory->isEmpty())
                                    <p class="text-muted text-center py-4">لا يوجد سجل مركبات سابق</p>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm">
                                            <thead>
                                                <tr>
                                                    <th>المركبة</th>
                                                    <th>الصنع / الموديل</th>
                                                    <th>تاريخ التعيين</th>
                                                    <th>تاريخ الإرجاع</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($vehicleHistory as $assignment)
                                                    <tr>
                                                        <td>
                                                            @if ($assignment->vehicle)
                                                                <a href="{{ route('dashboard.vehicles.show', $assignment->vehicle) }}">
                                                                    {{ $assignment->vehicle->plate_number }}
                                                                </a>
                                                            @else
                                                                <span class="text-muted">—</span>
                                                            @endif
                                                        </td>
                                                        <td>{{ $assignment->vehicle?->make }} {{ $assignment->vehicle?->model }}</td>
                                                        <td>{{ $assignment->assigned_at?->format('Y-m-d') ?? '—' }}</td>
                                                        <td>{{ $assignment->returned_at?->format('Y-m-d') ?? '—' }}</td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>

                            {{-- تبويب 5: الإجازات --}}
                            <div class="tab-pane fade {{ $activeTab === 'leaves' ? 'show active' : '' }}" id="leaves" role="tabpanel">
                                @include('dashboard.delegates._leaves')
                            </div>

                            {{-- تبويب 6: السجلات --}}
                            <div class="tab-pane fade {{ $activeTab === 'records' ? 'show active' : '' }}" id="records" role="tabpanel">
                                @php
                                    $auditService = app(\App\Services\AuditService::class);
                                    $timeline     = $auditService->getTimelineForDelegate($delegate->id, 30);
                                @endphp
                                @forelse ($timeline as $entry)
                                    <div class="d-flex align-items-start mb-1 pb-1" style="border-bottom:1px solid #f0f0f0;">
                                        <div class="mr-2 mt-1" style="min-width:28px;">
                                            @switch($entry->action)
                                                @case('created') <i class="la la-plus-circle text-success font-medium-3"></i> @break
                                                @case('updated') <i class="la la-edit text-info font-medium-3"></i> @break
                                                @case('deleted') <i class="la la-trash text-danger font-medium-3"></i> @break
                                                @case('status_changed') <i class="la la-refresh text-warning font-medium-3"></i> @break
                                                @case('assigned') <i class="la la-car text-primary font-medium-3"></i> @break
                                                @case('returned') <i class="la la-undo text-secondary font-medium-3"></i> @break
                                                @case('password_changed') <i class="la la-lock text-warning font-medium-3"></i> @break
                                                @default <i class="la la-info-circle text-muted font-medium-3"></i>
                                            @endswitch
                                        </div>
                                        <div class="flex-grow-1">
                                            <p class="mb-0" style="font-size:0.9rem;">
                                                @switch($entry->action)
                                                    @case('created')
                                                        @switch($entry->model_type)
                                                            @case('App\Models\Delegate') تم تسجيل المندوب @break
                                                            @case('App\Models\DelegateDocument') تم رفع وثيقة @break
                                                            @case('App\Models\VehicleAssignment') تم تعيين مركبة للمندوب @break
                                                            @default تم الإنشاء
                                                        @endswitch
                                                        @break
                                                    @case('updated') تم تحديث البيانات @break
                                                    @case('status_changed')
                                                        تم تغيير الحالة
                                                        @if (!empty($entry->old_values['status']) && !empty($entry->new_values['status']))
                                                            من <span class="badge badge-secondary">{{ $entry->old_values['status'] }}</span>
                                                            إلى <span class="badge badge-primary">{{ $entry->new_values['status'] }}</span>
                                                        @endif
                                                        @break
                                                    @case('assigned') تم تعيين مركبة @break
                                                    @case('returned') تم إرجاع المركبة @break
                                                    @case('deleted') تم حذف سجل @break
                                                    @case('password_changed') تم تغيير كلمة المرور @break
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


                            {{-- تبويب البوابة --}}
                            <div class="tab-pane fade {{ $activeTab === 'portal' ? 'show active' : '' }}" id="portal" role="tabpanel">
                                @php
                                    $generatedPw = session('portal_generated_password');
                                @endphp

                                @if($generatedPw)
                                    <div class="alert alert-warning border-warning mb-4" style="border-right:4px solid #f6c23e;">
                                        <h6 class="font-weight-bold mb-2">
                                            <i class="la la-key text-warning"></i>
                                            كلمة المرور المُنشأة — احفظها الآن، لن تظهر مرة أخرى
                                        </h6>
                                        <div class="d-flex align-items-center">
                                            <code class="mr-3 px-3 py-2 bg-white border rounded font-large-1"
                                                  id="generated-pw">{{ $generatedPw }}</code>
                                            <button onclick="navigator.clipboard.writeText('{{ $generatedPw }}').then(()=>alert('تم النسخ'))"
                                                    class="btn btn-sm btn-outline-warning">
                                                <i class="la la-copy"></i> نسخ
                                            </button>
                                        </div>
                                        <small class="text-muted d-block mt-2">
                                            أعطِ هذه الكلمة للمندوب. سيُطلب منه تغييرها عند أول تسجيل دخول.
                                        </small>
                                    </div>
                                @endif

                                <div class="row">
                                    <div class="col-md-6">

                                        {{-- Status card --}}
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body">
                                                <h6 class="font-weight-bold mb-3">
                                                    <i class="la la-info-circle text-primary"></i> حالة بوابة المندوب
                                                </h6>
                                                <table class="table table-borderless table-sm mb-0">
                                                    <tr>
                                                        <th style="width:45%">الحالة</th>
                                                        <td>
                                                            @if($delegate->portal_enabled)
                                                                <span class="badge badge-success">مفعّل</span>
                                                            @else
                                                                <span class="badge badge-secondary">معطّل</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>كلمة المرور</th>
                                                        <td>
                                                            @if($delegate->portal_password)
                                                                <span class="text-success"><i class="la la-check"></i> مُعيَّنة</span>
                                                                @if($delegate->portal_first_login)
                                                                    <span class="badge badge-warning badge-pill ml-1">لم تُغيَّر بعد</span>
                                                                @endif
                                                            @else
                                                                <span class="text-muted"><i class="la la-times"></i> لم تُنشأ بعد</span>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                    <tr>
                                                        <th>رقم الدخول</th>
                                                        <td><code>{{ $delegate->delegate_code ?? '—' }}</code></td>
                                                    </tr>
                                                    <tr>
                                                        <th>آخر دخول</th>
                                                        <td>{{ $delegate->last_portal_login?->format('Y-m-d H:i') ?? 'لم يسجّل دخولاً بعد' }}</td>
                                                    </tr>
                                                </table>
                                            </div>
                                        </div>

                                    </div>
                                    <div class="col-md-6">

                                        {{-- Actions --}}
                                        <div class="card border-0 shadow-sm mb-3">
                                            <div class="card-body">
                                                <h6 class="font-weight-bold mb-3">
                                                    <i class="la la-cogs text-primary"></i> إجراءات البوابة
                                                </h6>

                                                {{-- Generate credentials --}}
                                                @if(!$delegate->portal_password)
                                                    <form method="POST"
                                                          action="{{ route('dashboard.delegates.portal.generate', $delegate) }}"
                                                          class="mb-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-primary btn-block">
                                                            <i class="la la-key"></i>
                                                            إنشاء بيانات الدخول
                                                        </button>
                                                    </form>
                                                @else
                                                    {{-- Enable/Disable --}}
                                                    @if($delegate->portal_enabled)
                                                        <form method="POST"
                                                              action="{{ route('dashboard.delegates.portal.disable', $delegate) }}"
                                                              class="mb-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-warning btn-block"
                                                                    onclick="return confirm('هل أنت متأكد من تعطيل بوابة هذا المندوب؟')">
                                                                <i class="la la-ban"></i>
                                                                تعطيل البوابة
                                                            </button>
                                                        </form>
                                                    @else
                                                        <form method="POST"
                                                              action="{{ route('dashboard.delegates.portal.enable', $delegate) }}"
                                                              class="mb-2">
                                                            @csrf
                                                            <button type="submit" class="btn btn-success btn-block">
                                                                <i class="la la-check-circle"></i>
                                                                تفعيل البوابة
                                                            </button>
                                                        </form>
                                                    @endif

                                                    {{-- Reset password --}}
                                                    <form method="POST"
                                                          action="{{ route('dashboard.delegates.portal.reset-password', $delegate) }}"
                                                          class="mb-2">
                                                        @csrf
                                                        <button type="submit" class="btn btn-outline-danger btn-block"
                                                                onclick="return confirm('هل أنت متأكد من إعادة تعيين كلمة مرور البوابة؟')">
                                                            <i class="la la-refresh"></i>
                                                            إعادة تعيين كلمة المرور
                                                        </button>
                                                    </form>
                                                @endif

                                                @if(!$delegate->portal_password)
                                                    {{-- Alternative: enable then generate --}}
                                                @endif

                                                <hr>
                                                <p class="text-muted small mb-0">
                                                    <i class="la la-info-circle"></i>
                                                    رقم الدخول هو كود المندوب:
                                                    <code>{{ $delegate->delegate_code ?? '—' }}</code>
                                                </p>
                                            </div>
                                        </div>

                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
