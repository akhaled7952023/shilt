@extends('layouts.dashboard.app')

@section('title') تعديل إعداد @endsection

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
                                <a href="{{ route('dashboard.settings.index') }}">إعدادات النظام</a>
                            </li>
                            <li class="breadcrumb-item active">تعديل</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="row">

                {{-- Edit Form ─────────────────────────────────────────────────── --}}
                <div class="col-md-7">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="la la-edit"></i>
                                تعديل الإعداد:
                                <code class="ml-1">{{ $setting->key }}</code>
                            </h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body">

                                @include('dashboard.includes.validations-errors')

                                @if ($setting->description)
                                    <div class="alert alert-info py-2 mb-3">
                                        <i class="la la-info-circle"></i>
                                        {{ $setting->description }}
                                    </div>
                                @endif

                                @php
                                    $typeValue = $setting->type?->value ?? 'string';
                                    $isLogo    = $setting->key === 'company_logo_path';
                                @endphp

                                <form method="POST"
                                      action="{{ route('dashboard.settings.update', $setting->key) }}"
                                      @if($isLogo) enctype="multipart/form-data" @endif>
                                    @csrf
                                    @method('PUT')

                                    <div class="form-group">
                                        <label class="font-weight-bold">
                                            القيمة الحالية
                                        </label>

                                        @if ($isLogo)
                                            {{-- Logo: file upload --}}
                                            @if ($setting->value)
                                                <div class="mb-2">
                                                    <img src="{{ Storage::url($setting->value) }}"
                                                         alt="الشعار الحالي"
                                                         style="max-height:80px;border:1px solid #ddd;padding:4px;border-radius:4px;">
                                                    <p class="text-muted small mt-1">
                                                        الشعار الحالي — سيُستبدل بالملف الجديد عند الحفظ
                                                    </p>
                                                </div>
                                            @endif
                                            <div class="custom-file mt-1">
                                                <input type="file"
                                                       class="custom-file-input @error('value_file') is-invalid @enderror"
                                                       id="value_file"
                                                       name="value_file"
                                                       accept=".png,.jpg,.jpeg">
                                                <label class="custom-file-label" for="value_file">
                                                    اختر صورة (PNG, JPG — بحد أقصى 2 ميجابايت)
                                                </label>
                                                @error('value_file')
                                                    <div class="invalid-feedback">{{ $message }}</div>
                                                @enderror
                                            </div>

                                        @elseif ($typeValue === 'boolean')
                                            {{-- Boolean: select --}}
                                            <select name="value"
                                                    class="form-control @error('value') is-invalid @enderror">
                                                <option value="true"
                                                    {{ filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>
                                                    نعم (true)
                                                </option>
                                                <option value="false"
                                                    {{ ! filter_var($setting->value, FILTER_VALIDATE_BOOLEAN) ? 'selected' : '' }}>
                                                    لا (false)
                                                </option>
                                            </select>
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                        @elseif ($typeValue === 'decimal')
                                            {{-- Decimal: number input --}}
                                            <input type="number"
                                                   name="value"
                                                   step="0.0001"
                                                   min="0"
                                                   class="form-control @error('value') is-invalid @enderror"
                                                   value="{{ old('value', $setting->value) }}">
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                        @elseif ($typeValue === 'integer')
                                            {{-- Integer: number input --}}
                                            <input type="number"
                                                   name="value"
                                                   step="1"
                                                   min="0"
                                                   class="form-control @error('value') is-invalid @enderror"
                                                   value="{{ old('value', $setting->value) }}">
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror

                                        @else
                                            {{-- String / default: text input --}}
                                            <input type="text"
                                                   name="value"
                                                   class="form-control @error('value') is-invalid @enderror"
                                                   value="{{ old('value', $setting->value) }}"
                                                   maxlength="500">
                                            @error('value')
                                                <div class="invalid-feedback">{{ $message }}</div>
                                            @enderror
                                        @endif

                                    </div>

                                    {{-- Metadata row --}}
                                    <div class="row mb-3">
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <strong>النوع:</strong>
                                                <span class="badge badge-secondary">{{ $typeValue }}</span>
                                            </small>
                                        </div>
                                        <div class="col-6">
                                            <small class="text-muted">
                                                <strong>المجموعة:</strong>
                                                {{ $setting->group ?? '—' }}
                                            </small>
                                        </div>
                                    </div>

                                    <div class="d-flex justify-content-between">
                                        <button type="submit" class="btn btn-primary">
                                            <i class="la la-save"></i> حفظ التعديل
                                        </button>
                                        <a href="{{ route('dashboard.settings.index') }}"
                                           class="btn btn-outline-secondary">
                                            <i class="la la-arrow-right"></i> رجوع
                                        </a>
                                    </div>

                                </form>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Change History ─────────────────────────────────────────────── --}}
                <div class="col-md-5">
                    <div class="card">
                        <div class="card-header">
                            <h4 class="card-title">
                                <i class="la la-history"></i> سجل التغييرات
                            </h4>
                        </div>
                        <div class="card-content">
                            <div class="card-body p-0">
                                @if ($history->isEmpty())
                                    <div class="text-center text-muted p-4">
                                        <i class="la la-clock-o font-large-1"></i>
                                        <p class="mt-2 mb-0">لا توجد تغييرات مسجلة لهذا الإعداد.</p>
                                    </div>
                                @else
                                    <div class="table-responsive">
                                        <table class="table table-sm mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th>المستخدم</th>
                                                    <th>القيمة القديمة</th>
                                                    <th>القيمة الجديدة</th>
                                                    <th>التاريخ</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($history as $log)
                                                    <tr>
                                                        <td class="small">
                                                            {{ $log->changedBy?->name ?? '—' }}
                                                        </td>
                                                        <td class="small text-danger">
                                                            {{ mb_strimwidth($log->old_value ?? '—', 0, 30, '...') }}
                                                        </td>
                                                        <td class="small text-success">
                                                            {{ mb_strimwidth($log->new_value ?? '—', 0, 30, '...') }}
                                                        </td>
                                                        <td class="small text-muted">
                                                            {{ $log->changed_at?->format('Y-m-d H:i') }}
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </div>
</div>
@endsection
