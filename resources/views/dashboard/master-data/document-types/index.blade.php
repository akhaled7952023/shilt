@extends('layouts.dashboard.app')
@section('title') أنواع الوثائق @endsection
@section('content')
<div class="app-content content">
    <div class="content-wrapper">
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">أنواع الوثائق</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>
        <div class="content-body">
            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">أنواع الوثائق</h4>
                    <div class="heading-elements">
                        <a href="{{ route('dashboard.master-data.document-types.create') }}" class="btn btn-primary btn-sm">
                            <i class="la la-plus"></i> إضافة نوع وثيقة
                        </a>
                    </div>
                </div>
                <div class="card-content">
                    <div class="card-body">
                        <form method="GET" action="{{ route('dashboard.master-data.document-types.index') }}" class="mb-2">
                            <div class="row">
                                <div class="col-md-4">
                                    <input type="text" name="search" class="form-control" placeholder="بحث بالاسم..."
                                           value="{{ old('search', $filters['search'] ?? '') }}">
                                </div>
                                <div class="col-md-3">
                                    <select name="applies_to" class="form-control">
                                        <option value="">الكل</option>
                                        <option value="delegate" {{ ($filters['applies_to'] ?? '') === 'delegate' ? 'selected' : '' }}>مناديب</option>
                                        <option value="vehicle" {{ ($filters['applies_to'] ?? '') === 'vehicle' ? 'selected' : '' }}>مركبات</option>
                                    </select>
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-secondary">بحث</button>
                                    <a href="{{ route('dashboard.master-data.document-types.index') }}" class="btn btn-light">إعادة تعيين</a>
                                </div>
                            </div>
                        </form>
                        <table class="table table-responsive-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم بالعربية</th>
                                    <th>الاسم بالإنجليزية</th>
                                    <th>يخص</th>
                                    <th>إلزامي</th>
                                    <th>الحالة</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($documentTypes as $documentType)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $documentType->getTranslation('name', 'ar') }}</td>
                                        <td>{{ $documentType->getTranslation('name', 'en') }}</td>
                                        <td>
                                            @include('dashboard.components._status_badge', ['status' => $documentType->applies_to->value])
                                        </td>
                                        <td>{{ $documentType->is_required ? 'نعم' : 'لا' }}</td>
                                        <td>@include('dashboard.components._status_badge', ['status' => $documentType->is_active ? 'active' : 'inactive'])</td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-danger btn-sm dropdown-toggle" type="button" data-toggle="dropdown">الإجراءات</button>
                                                <div class="dropdown-menu">
                                                    <a class="dropdown-item" href="{{ route('dashboard.master-data.document-types.edit', $documentType->id) }}"><i class="la la-edit"></i> تعديل</a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item" href="javascript:void(0)"
                                                       onclick="if(confirm('تغيير الحالة؟')) document.getElementById('toggle-form-{{ $documentType->id }}').submit();">
                                                        <i class="la la-toggle-on"></i> {{ $documentType->is_active ? 'تعطيل' : 'تفعيل' }}
                                                    </a>
                                                    <div class="dropdown-divider"></div>
                                                    <a class="dropdown-item text-danger" href="javascript:void(0)"
                                                       onclick="if(confirm('هل أنت متأكد من الحذف؟')) document.getElementById('delete-form-{{ $documentType->id }}').submit();">
                                                        <i class="la la-trash"></i> حذف
                                                    </a>
                                                </div>
                                            </div>
                                            <form id="toggle-form-{{ $documentType->id }}" method="POST"
                                                  action="{{ route('dashboard.master-data.document-types.toggle', $documentType->id) }}" style="display:none;">
                                                @csrf @method('PATCH')
                                            </form>
                                            <form id="delete-form-{{ $documentType->id }}" method="POST"
                                                  action="{{ route('dashboard.master-data.document-types.destroy', $documentType->id) }}" style="display:none;">
                                                @csrf @method('DELETE')
                                            </form>
                                        </td>
                                    </tr>
                                @empty
                                    <tr><td colspan="7" class="text-center">لا توجد بيانات</td></tr>
                                @endforelse
                            </tbody>
                        </table>
                        {{ $documentTypes->appends($filters)->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
