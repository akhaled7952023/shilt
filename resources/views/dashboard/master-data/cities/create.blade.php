@extends('layouts.dashboard.app')

@section('title') إضافة مدينة @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.master-data.cities.index') }}">المدن</a>
                            </li>
                            <li class="breadcrumb-item active">إضافة مدينة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-8">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">إضافة مدينة جديدة</h4>
                </div>

                <div class="card-content collapse show">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form class="form" action="{{ route('dashboard.master-data.cities.store') }}" method="POST">
                            @csrf

                            <div class="form-body">
                                <h4 class="form-section"><i class="la la-map-marker"></i> بيانات المدينة</h4>

                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الاسم بالعربية <span class="text-danger">*</span></label>
                                            <input type="text"
                                                   class="form-control border-primary"
                                                   name="name[ar]"
                                                   value="{{ old('name.ar') }}"
                                                   placeholder="أدخل اسم المدينة بالعربية">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label>الاسم بالإنجليزية</label>
                                            <input type="text"
                                                   class="form-control border-primary"
                                                   name="name[en]"
                                                   value="{{ old('name.en') }}"
                                                   placeholder="Enter city name in English">
                                        </div>
                                    </div>
                                </div>

                                <div class="row">
                                    <div class="col-md-4">
                                        <div class="form-group">
                                            <label>
                                                <input type="hidden" name="is_active" value="0">
                                                <input type="checkbox"
                                                       name="is_active"
                                                       value="1"
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
