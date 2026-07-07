@extends('layouts.dashboard.app')

@section('title') إضافة إيجار @endsection

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
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.vehicles.rentals.index', $vehicle) }}">الإيجارات</a></li>
                            <li class="breadcrumb-item active">إضافة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">إضافة إيجار — {{ $vehicle->plate_number }}</h4>
                </div>
                <div class="card-content">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form action="{{ route('dashboard.vehicles.rentals.store', $vehicle) }}"
                              method="POST">
                            @csrf

                            <div class="form-body">
                                <h4 class="form-section">بيانات الإيجار</h4>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>الفترة الشهرية <span class="text-danger">*</span></label>
                                            <select name="monthly_period_id" class="form-control border-primary">
                                                <option value="">اختر الفترة...</option>
                                                @foreach ($periods as $period)
                                                    <option value="{{ $period->id }}"
                                                        {{ old('monthly_period_id') == $period->id ? 'selected' : '' }}>
                                                        {{ $period->year }}/{{ str_pad($period->month, 2, '0', STR_PAD_LEFT) }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('monthly_period_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-3">
                                        <div class="form-group">
                                            <label>المبلغ (ر.س) <span class="text-danger">*</span></label>
                                            <input type="number" name="amount" step="0.01" min="0"
                                                   class="form-control border-primary"
                                                   value="{{ old('amount') }}" placeholder="0.00">
                                            @error('amount') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                    <div class="col-md-5">
                                        <div class="form-group">
                                            <label>الجهة الدافعة <span class="text-danger">*</span></label>
                                            <div class="mt-1">
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="payment_by"
                                                           id="pay_company" value="company"
                                                           {{ old('payment_by', 'company') === 'company' ? 'checked' : '' }}
                                                           onchange="document.getElementById('delegate_row').style.display='none'">
                                                    <label class="form-check-label" for="pay_company">الشركة</label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <input class="form-check-input" type="radio" name="payment_by"
                                                           id="pay_delegate" value="delegate"
                                                           {{ old('payment_by') === 'delegate' ? 'checked' : '' }}
                                                           onchange="document.getElementById('delegate_row').style.display='block'">
                                                    <label class="form-check-label" for="pay_delegate">مندوب</label>
                                                </div>
                                            </div>
                                            @error('payment_by') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row" id="delegate_row"
                                     style="{{ old('payment_by') === 'delegate' ? '' : 'display:none;' }}">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>المندوب <span class="text-danger">*</span></label>
                                            <select name="delegate_id" class="form-control border-primary">
                                                <option value="">اختر المندوب...</option>
                                                @foreach ($delegates as $delegate)
                                                    <option value="{{ $delegate->id }}"
                                                        {{ old('delegate_id') == $delegate->id ? 'selected' : '' }}>
                                                        {{ $delegate->name }}
                                                    </option>
                                                @endforeach
                                            </select>
                                            @error('delegate_id') <span class="text-danger">{{ $message }}</span> @enderror
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>ملاحظات</label>
                                            <textarea name="notes" rows="3" class="form-control border-primary"
                                                      placeholder="ملاحظات اختيارية...">{{ old('notes') }}</textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('dashboard.vehicles.rentals.index', $vehicle) }}"
                                   class="btn btn-warning mr-1">
                                    <i class="la la-times"></i> إلغاء
                                </a>
                                <button type="submit" class="btn btn-primary">
                                    <i class="la la-check-square-o"></i> حفظ
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
