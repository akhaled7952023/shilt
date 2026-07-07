@extends('layouts.dashboard.app')

@section('title') تعديل مخالفة @endsection

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
                            <li class="breadcrumb-item active">تعديل مخالفة</li>
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

                        @php
                            $warningTypes = app(\App\Services\Dashboard\MasterData\WarningTypes\IWarningTypeService::class)->getAllActive();
                            $delegates    = app(\App\Services\Dashboard\Delegates\IDelegateService::class)->getActive();
                        @endphp

                        <form action="{{ route('dashboard.vehicles.violations.update', [$vehicle, $violation]) }}"
                              method="POST">
                            @csrf @method('PUT')

                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>نوع المخالفة</label>
                                        <select name="warning_type_id" class="form-control border-primary">
                                            <option value="">اختر النوع</option>
                                            @foreach ($warningTypes as $type)
                                                <option value="{{ $type->id }}"
                                                    {{ old('warning_type_id', $violation->warning_type_id) == $type->id ? 'selected' : '' }}>
                                                    {{ $type->getTranslation('name', 'ar') }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('warning_type_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>المندوب</label>
                                        <select name="delegate_id" class="form-control border-primary">
                                            <option value="">اختر المندوب</option>
                                            @foreach ($delegates as $d)
                                                <option value="{{ $d->id }}"
                                                    {{ old('delegate_id', $violation->delegate_id) == $d->id ? 'selected' : '' }}>
                                                    {{ $d->name }} — {{ $d->delegate_code }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('delegate_id') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>التاريخ <span class="text-danger">*</span></label>
                                        <input type="date" name="date" class="form-control border-primary"
                                               value="{{ old('date', $violation->date?->format('Y-m-d')) }}" required>
                                        @error('date') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>القيمة</label>
                                        <input type="number" name="amount" class="form-control border-primary"
                                               value="{{ old('amount', $violation->amount) }}" step="0.01" min="0">
                                        @error('amount') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                                <div class="col-md-2">
                                    <div class="form-group">
                                        <label>المكان</label>
                                        <input type="text" name="location" class="form-control border-primary"
                                               value="{{ old('location', $violation->location) }}">
                                        @error('location') <span class="text-danger small">{{ $message }}</span> @enderror
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label>الملاحظات</label>
                                        <textarea name="notes" class="form-control border-primary" rows="3">{{ old('notes', $violation->notes) }}</textarea>
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
