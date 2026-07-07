@extends('layouts.dashboard.app')

@section('title') إضافة فترة شهرية @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- Breadcrumb --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.monthly.periods.index') }}">الفترات الشهرية</a>
                            </li>
                            <li class="breadcrumb-item active">إضافة فترة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row justify-content-center">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="la la-calendar-plus-o"></i>
                                إضافة فترة شهرية جديدة
                            </h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">

                                @include('dashboard.includes.validations-errors')

                                <form method="POST"
                                      action="{{ route('dashboard.monthly.periods.store') }}">
                                    @csrf

                                    {{-- Platform --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            المنصة <span class="text-danger">*</span>
                                        </label>
                                        <select name="platform_id"
                                                class="form-control @error('platform_id') is-invalid @enderror">
                                            <option value="">— اختر المنصة —</option>
                                            @foreach ($platforms as $platform)
                                                <option value="{{ $platform->id }}"
                                                        {{ old('platform_id') == $platform->id ? 'selected' : '' }}>
                                                    {{ $platform->name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('platform_id')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Year --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            السنة <span class="text-danger">*</span>
                                        </label>
                                        <select name="year"
                                                class="form-control @error('year') is-invalid @enderror">
                                            <option value="">— اختر السنة —</option>
                                            @foreach ($years as $year)
                                                <option value="{{ $year }}"
                                                        {{ old('year', date('Y')) == $year ? 'selected' : '' }}>
                                                    {{ $year }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('year')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Month --}}
                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            الشهر <span class="text-danger">*</span>
                                        </label>
                                        <select name="month"
                                                class="form-control @error('month') is-invalid @enderror">
                                            <option value="">— اختر الشهر —</option>
                                            @foreach ($months as $num => $name)
                                                <option value="{{ $num }}"
                                                        {{ old('month') == $num ? 'selected' : '' }}>
                                                    {{ $name }}
                                                </option>
                                            @endforeach
                                        </select>
                                        @error('month')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    {{-- Notes --}}
                                    <div class="form-group">
                                        <label>ملاحظات</label>
                                        <textarea name="notes"
                                                  class="form-control @error('notes') is-invalid @enderror"
                                                  rows="3"
                                                  placeholder="ملاحظات اختيارية...">{{ old('notes') }}</textarea>
                                        @error('notes')
                                            <div class="invalid-feedback">{{ $message }}</div>
                                        @enderror
                                    </div>

                                    <div class="d-flex justify-content-between mt-2">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="la la-save"></i> إنشاء الفترة
                                        </button>
                                        <a href="{{ route('dashboard.monthly.periods.index') }}"
                                           class="btn btn-outline-secondary">
                                            <i class="la la-arrow-right"></i> رجوع
                                        </a>
                                    </div>

                                </form>

                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
