@extends('layouts.dashboard.app')

@section('title') تعديل منصة @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.master-data.platforms.index') }}">المنصات</a></li>
                            <li class="breadcrumb-item active">تعديل منصة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-10">

            {{-- بيانات المنصة --}}
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">تعديل منصة</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form class="form"
                              action="{{ route('dashboard.master-data.platforms.update', $platform->id) }}"
                              method="POST">
                            @csrf
                            @method('PUT')

                            <div class="form-body">
                                <h4 class="form-section"><i class="la la-server"></i> بيانات المنصة</h4>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الاسم <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control border-primary"
                                                   name="name" value="{{ old('name', $platform->name) }}">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الرمز</label>
                                            <p class="form-control-plaintext font-weight-bold">
                                                <code>{{ $platform->code }}</code>
                                                <small class="text-muted">(الرمز لا يمكن تعديله)</small>
                                            </p>
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الحد الأدنى للمسافة (كم)</label>
                                            <input type="number" class="form-control border-primary"
                                                   name="min_km_threshold"
                                                   value="{{ old('min_km_threshold', $platform->min_km_threshold) }}"
                                                   step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الغرامة لكل كيلومتر (ريال)</label>
                                            <input type="number" class="form-control border-primary"
                                                   name="penalty_per_km"
                                                   value="{{ old('penalty_per_km', $platform->penalty_per_km) }}"
                                                   step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" name="is_active" value="1"
                                                       {{ old('is_active', $platform->is_active) ? 'checked' : '' }}>
                                                نشط
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <a href="{{ route('dashboard.master-data.platforms.index') }}" class="mr-1 btn btn-warning">
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
