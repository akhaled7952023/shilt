@extends('layouts.dashboard.app')

@section('title') تعديل سجل الصيانة @endsection

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
                            <li class="breadcrumb-item active">تعديل سجل الصيانة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form action="{{ route('dashboard.vehicles.maintenance.update', [$vehicle, $maintenance]) }}"
                              method="POST">
                            @csrf @method('PUT')

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>التاريخ <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control border-primary"
                                               value="{{ old('date', $maintenance->date?->format('Y-m-d')) }}" required>
                                        @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>التكلفة</label>
                                        <input type="number" name="cost" class="form-control border-primary"
                                               value="{{ old('cost', $maintenance->cost) }}" step="0.01" min="0">
                                        @error('cost') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>الحالة <span class="text-danger">*</span></label>
                                        <select name="status" class="form-control border-primary" required>
                                            <option value="completed"  {{ old('status', $maintenance->status) === 'completed'  ? 'selected' : '' }}>مكتمل</option>
                                            <option value="in_progress"{{ old('status', $maintenance->status) === 'in_progress' ? 'selected' : '' }}>جارٍ</option>
                                            <option value="pending"    {{ old('status', $maintenance->status) === 'pending'    ? 'selected' : '' }}>معلق</option>
                                        </select>
                                        @error('status') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الوصف <span class="text-danger">*</span></label>
                                        <textarea name="description" class="form-control border-primary" rows="3" required>{{ old('description', $maintenance->description) }}</textarea>
                                        @error('description') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الملاحظات</label>
                                        <textarea name="notes" class="form-control border-primary" rows="3">{{ old('notes', $maintenance->notes) }}</textarea>
                                        @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
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
