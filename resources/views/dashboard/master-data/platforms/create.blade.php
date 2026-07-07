@extends('layouts.dashboard.app')

@section('title') إضافة منصة @endsection

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
                            <li class="breadcrumb-item active">إضافة منصة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-10">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">إضافة منصة جديدة</h4>
                </div>
                <div class="card-content collapse show">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form class="form" action="{{ route('dashboard.master-data.platforms.store') }}" method="POST">
                            @csrf

                            <div class="form-body">
                                <h4 class="form-section"><i class="la la-server"></i> بيانات المنصة</h4>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الاسم <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control border-primary"
                                                   name="name" value="{{ old('name') }}" placeholder="اسم المنصة">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الرمز <span class="text-danger">*</span></label>
                                            <input type="text" class="form-control border-primary"
                                                   name="code" value="{{ old('code') }}" placeholder="مثال: JAHEZ">
                                        </div>
                                    </div>
                                </div>

                                <h4 class="form-section"><i class="la la-calculator"></i> إعدادات التسوية</h4>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الحد الأدنى للمسافة (كم) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control border-primary"
                                                   name="min_km_threshold" value="{{ old('min_km_threshold', 0) }}"
                                                   step="0.01" min="0">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الغرامة لكل كيلومتر (ريال) <span class="text-danger">*</span></label>
                                            <input type="number" class="form-control border-primary"
                                                   name="penalty_per_km" value="{{ old('penalty_per_km', 0) }}"
                                                   step="0.01" min="0">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>
                                                <input type="checkbox" name="is_active" value="1"
                                                       {{ old('is_active', '1') ? 'checked' : '' }}>
                                                نشط
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="form-actions right">
                                <button type="button" class="mr-1 btn btn-warning" onclick="window.history.back();">
                                    <i class="ft-x"></i> إلغاء
                                </button>
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
