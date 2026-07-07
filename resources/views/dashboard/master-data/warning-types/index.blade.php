@extends('layouts.dashboard.app')
@section('title') أنواع التحذيرات @endsection
@section('content')
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">أنواع التحذيرات</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">أنواع التحذيرات</h4>
                    <div class="heading-elements">
                        <a href="{{ route('dashboard.master-data.warning-types.create') }}" class="btn btn-primary btn-sm">
                            <i class="la la-plus"></i> إضافة نوع تحذير
                        </a>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard.master-data.warning-types.index') }}" class="mb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم..."
                                           value="{{ old('search', $filters['search'] ?? '') }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-secondary">بحث</button>
                                    <a href="{{ route('dashboard.master-data.warning-types.index') }}" class="btn btn-light">إعادة تعيين</a>
                                </div>
                            </div>
                        </form>
                        <table class="table table-responsive-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم بالعربية</th>
                                    <th>الاسم بالإنجليزية</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($warningTypes as $warningType)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $warningType->getTranslation('name', 'ar') }}</td>
                                        <td>{{ $warningType->getTranslation('name', 'en') }}</td>
                                        <td>@include('dashboard.components._status_badge', ['status' => $warningType->is_active ? 'active' : 'inactive'])</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-danger btn-sm dropdown-toggle" type="button" data-toggle="dropdown">الإجراءات</button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('dashboard.master-data.warning-types.edit', $warningType->id) }}"><i class="la la-edit"></i> تعديل</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                       onclick="if(confirm('تغيير الحالة؟')) document.getElementById('toggle-form-{{ $warningType->id }}').submit();">
                                                        <i class="la la-toggle-on"></i> {{ $warningType->is_active ? 'تعطيل' : 'تفعيل' }}
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                       onclick="if(confirm('هل أنت متأكد من الحذف؟')) document.getElementById('delete-form-{{ $warningType->id }}').submit();">
                                                        <i class="la la-trash"></i> حذف
                                                    </a>
                                                </div>
                                            </div>
                                            <form id="toggle-form-{{ $warningType->id }}" method="POST"
                                                  action="{{ route('dashboard.master-data.warning-types.toggle', $warningType->id) }}" style="display:none;">
                                                @csrf @method('PATCH')
                                            </form>
                                            <form id="delete-form-{{ $warningType->id }}" method="POST"
                                                  action="{{ route('dashboard.master-data.warning-types.destroy', $warningType->id) }}" style="display:none;">
                                                @csrf @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="5" class="text-center">لا توجد بيانات</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $warningTypes->appends($filters)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
