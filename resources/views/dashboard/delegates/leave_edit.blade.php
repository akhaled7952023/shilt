@extends('layouts.dashboard.app')

@section('title') تعديل إجازة @endsection

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
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.delegates.show', $delegate) }}?tab=leaves">{{ $delegate->name }}</a>
                            </li>
                            <li class="breadcrumb-item active">تعديل إجازة</li>
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

                        <form action="{{ route('dashboard.delegates.leaves.update', [$delegate, $leave]) }}"
                              method="POST">
                            @csrf @method('PUT')

                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group">
                                        <label>نوع الإجازة <span class="text-danger">*</span></label>
                                        <select name="leave_type_id" class="form-control border-primary" required>
                                            <option value="">اختر النوع</option>
                                            @foreach ($leaveTypes as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ old('leave_type_id', $leave->leave_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->getTranslation('name', 'ar') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('leave_type_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>تاريخ البداية <span class="text-danger">*</span></label>
                                        <input type="date" name="start_date" class="form-control border-primary"
                                               value="{{ old('start_date', $leave->start_date?->format('Y-m-d')) }}" required>
                                        @error('start_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>تاريخ النهاية <span class="text-danger">*</span></label>
                                        <input type="date" name="end_date" class="form-control border-primary"
                                               value="{{ old('end_date', $leave->end_date?->format('Y-m-d')) }}" required>
                                        @error('end_date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                @if ($leave->start_date && $leave->end_date)
                                    <div class="col-md-2 d-flex align-items-center">
                                        <div class="text-muted small mt-1">
                                            <i class="la la-calendar-check-o"></i>
                                            {{ $leave->start_date->diffInDays($leave->end_date) + 1 }} يوم
                                        </div>
                                    </div>
                                @endif
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>ملاحظات</label>
                                        <textarea name="notes" class="form-control border-primary" rows="3">{{ old('notes', $leave->notes) }}</textarea>
                                        @error('notes') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('dashboard.delegates.show', $delegate) }}?tab=leaves"
                                   class="mr-1 btn btn-warning">
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
