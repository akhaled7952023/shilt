@extends('layouts.dashboard.app')

@section('title')
    تعديل الصلاحية
@endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- المسار --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.roles.index') }}">الصلاحيات</a>
                            </li>
                            <li class="breadcrumb-item active">
                                تعديل الصلاحية
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- المحتوى --}}
        <div class="col-md-12">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">تعديل الصلاحية</h4>
                </div>

                <div class="card-content collapse show">
                    <div class="card-body">

                        @include('dashboard.includes.validations-errors')

                        <form class="form" action="{{ route('dashboard.roles.update', $role->id) }}" method="POST">
                            @csrf
                            @method('PUT')

                            <input type="hidden" name="id" value="{{ $role->id }}">

                            <div class="form-body">

                                <h4 class="form-section">
                                    <i class="la la-edit"></i>
                                    تعديل بيانات الصلاحية
                                </h4>

                                {{-- اسم الصلاحية --}}
                                <div class="row">

                                    <div class="col-md-12">
                                        <div class="form-group">
                                            <label>اسم الصلاحية</label>
                                            <input type="text"
                                                   class="form-control border-primary"
                                                   name="name"
                                                   value="{{ $role->name }}"
                                                   placeholder="أدخل اسم الصلاحية">
                                        </div>
                                    </div>

                                </div>

                                {{-- الأذونات --}}
                                <div class="row">

                                    @foreach (config('permissions_ar') as $key => $value)
                                        <div class="col-md-2">
                                            <input type="checkbox"
                                                   class="checkbox"
                                                   name="permissions[]"
                                                   value="{{ $key }}"
                                                   @checked(in_array($key, $role->permissions))>
                                            <label>{{ $value }}</label>
                                        </div>
                                    @endforeach

                                </div>

                            </div>

                            {{-- الأزرار --}}
                            <div class="form-actions right">

                                <button type="button"
                                        class="mr-1 btn btn-warning"
                                        onclick="window.history.back();">
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
