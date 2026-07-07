@extends('layouts.dashboard.app')

@section('title') تعديل مركبة @endsection

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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.vehicles.show', $vehicle) }}">{{ $vehicle->plate_number }}</a></li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-12">
            <div class="card">
                <div class="card-header">
                    <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>
                    <div class="heading-elements">
                        <ul class="mb-0 list-inline">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                            <li><a data-action="close"><i class="ft-x"></i></a></li>
                        </ul>
                    </div>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form class="form"
                              action="{{ route('dashboard.vehicles.update', $vehicle) }}"
                              method="POST"
                              enctype="multipart/form-data">
                            @csrf @method('PUT')

                            <div class="form-body">

                                {{-- المعلومات العامة --}}
                                <h4 class="form-section"><i class="la la-car"></i> المعلومات العامة</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>رقم اللوحة <span class="text-danger">*</span></label>
                                            <input type="text" name="plate_number" class="form-control border-primary"
                                                   value="{{ old('plate_number', $vehicle->plate_number) }}">
                                            @error('plate_number') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>نوع المركبة</label>
                                            <select name="vehicle_type_id" class="form-control border-primary">
                                                <option value="">اختر النوع</option>
                                                @foreach ($vehicleTypes as $type)
                                                    <option value="{{ $type->id }}"
                                                        {{ old('vehicle_type_id', $vehicle->vehicle_type_id) == $type->id ? 'selected' : '' }}>
                                                        {{ $type->getTranslation('name', 'ar') }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('vehicle_type_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>الماركة <span class="text-danger">*</span></label>
                                            <input type="text" name="make" class="form-control border-primary"
                                                   value="{{ old('make', $vehicle->make) }}">
                                            @error('make') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>الموديل <span class="text-danger">*</span></label>
                                            <input type="text" name="model" class="form-control border-primary"
                                                   value="{{ old('model', $vehicle->model) }}">
                                            @error('model') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>سنة الصنع</label>
                                            <input type="number" name="year" class="form-control border-primary"
                                                   value="{{ old('year', $vehicle->year) }}" min="1900" max="2100">
                                            @error('year') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>اللون</label>
                                            <input type="text" name="color" class="form-control border-primary"
                                                   value="{{ old('color', $vehicle->color) }}">
                                            @error('color') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>رقم الهيكل <small class="text-muted">(اختياري)</small></label>
                                            <input type="text" name="chassis_number" class="form-control border-primary"
                                                   value="{{ old('chassis_number', $vehicle->chassis_number) }}">
                                            @error('chassis_number') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>الحالة</label>
                                            <select name="status" class="form-control border-primary">
                                                <option value="available"    {{ old('status', $vehicle->status?->value) === 'available'    ? 'selected' : '' }}>متاح</option>
                                                <option value="maintenance"  {{ old('status', $vehicle->status?->value) === 'maintenance'  ? 'selected' : '' }}>صيانة</option>
                                                <option value="retired"      {{ old('status', $vehicle->status?->value) === 'retired'      ? 'selected' : '' }}>متقاعد</option>
                                            </select>
                                            @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- صور المركبة --}}
                                <h4 class="form-section"><i class="la la-camera"></i> صور المركبة</h4>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>صورة المركبة <small class="text-muted">(اختياري)</small></label>
                                            <input type="file" name="vehicle_image" class="dropify" accept="image/*"
                                                   @if($vehicle->vehicle_image) data-default-file="{{ Storage::url($vehicle->vehicle_image) }}" @endif>
                                            @error('vehicle_image') <span class="text-danger d-block mt-1 small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>صورة الاستمارة <small class="text-muted">(اختياري)</small></label>
                                            <input type="file" name="registration_image" class="dropify" accept="image/*"
                                                   @if($vehicle->registration_image) data-default-file="{{ Storage::url($vehicle->registration_image) }}" @endif>
                                            @error('registration_image') <span class="text-danger d-block mt-1 small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>صورة التأمين <small class="text-muted">(اختياري)</small></label>
                                            <input type="file" name="insurance_image" class="dropify" accept="image/*"
                                                   @if($vehicle->insurance_image) data-default-file="{{ Storage::url($vehicle->insurance_image) }}" @endif>
                                            @error('insurance_image') <span class="text-danger d-block mt-1 small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                {{-- الوثائق --}}
                                <h4 class="form-section"><i class="la la-file-text"></i> الوثائق</h4>

                                <p class="text-muted font-weight-bold mb-1">الاستمارة</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>رقم الاستمارة</label>
                                            <input type="text" name="registration_number" class="form-control border-primary"
                                                   value="{{ old('registration_number', $vehicle->registration_number) }}">
                                            @error('registration_number') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>تاريخ الإصدار</label>
                                            <input type="date" name="registration_issue_date" class="form-control border-primary"
                                                   value="{{ old('registration_issue_date', $vehicle->registration_issue_date?->format('Y-m-d')) }}">
                                            @error('registration_issue_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>تاريخ الانتهاء</label>
                                            <input type="date" name="registration_expiry_date" class="form-control border-primary"
                                                   value="{{ old('registration_expiry_date', $vehicle->registration_expiry_date?->format('Y-m-d')) }}">
                                            @error('registration_expiry_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <p class="text-muted font-weight-bold mb-1">التأمين</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>شركة التأمين</label>
                                            <input type="text" name="insurance_company" class="form-control border-primary"
                                                   value="{{ old('insurance_company', $vehicle->insurance_company) }}">
                                            @error('insurance_company') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>رقم الوثيقة</label>
                                            <input type="text" name="insurance_policy_number" class="form-control border-primary"
                                                   value="{{ old('insurance_policy_number', $vehicle->insurance_policy_number) }}">
                                            @error('insurance_policy_number') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>تاريخ البداية</label>
                                            <input type="date" name="insurance_start_date" class="form-control border-primary"
                                                   value="{{ old('insurance_start_date', $vehicle->insurance_start_date?->format('Y-m-d')) }}">
                                            @error('insurance_start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="form-group">
                                            <label>تاريخ الانتهاء</label>
                                            <input type="date" name="insurance_expiry_date" class="form-control border-primary"
                                                   value="{{ old('insurance_expiry_date', $vehicle->insurance_expiry_date?->format('Y-m-d')) }}">
                                            @error('insurance_expiry_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <p class="text-muted font-weight-bold mb-1">الفحص الدوري</p>
                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>رقم الفحص <small class="text-muted">(اختياري)</small></label>
                                            <input type="text" name="inspection_number" class="form-control border-primary"
                                                   value="{{ old('inspection_number', $vehicle->inspection_number) }}">
                                            @error('inspection_number') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>تاريخ الإصدار</label>
                                            <input type="date" name="inspection_issue_date" class="form-control border-primary"
                                                   value="{{ old('inspection_issue_date', $vehicle->inspection_issue_date?->format('Y-m-d')) }}">
                                            @error('inspection_issue_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>تاريخ الانتهاء</label>
                                            <input type="date" name="inspection_expiry_date" class="form-control border-primary"
                                                   value="{{ old('inspection_expiry_date', $vehicle->inspection_expiry_date?->format('Y-m-d')) }}">
                                            @error('inspection_expiry_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('dashboard.vehicles.show', $vehicle) }}" class="mr-1 btn btn-warning">
                                    <i class="ft-x"></i> إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-check-square-o"></i> حفظ التعديلات
                                </button>
                            </div>

                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
