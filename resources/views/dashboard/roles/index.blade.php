@extends('layouts.dashboard.app')

@section('title')
    الصلاحيات
@endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- العنوان والمسار --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item active">الصلاحيات</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        {{-- المحتوى --}}
        <div class="content-body">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">الصلاحيات</h4>
                </div>

                <div class="card-content">
                    <div class="card-body">

                        {{-- زر إضافة --}}
                        <a href="{{ route('dashboard.roles.create') }}" class="btn btn-primary">
                            إضافة صلاحية جديدة
                        </a>

                        <br><br>

                        <table class="table table-responsive-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>اسم الصلاحية</th>
                                    <th>الأذونات</th>
                                    <th>الإجراءات</th>
                                </tr>
                            </thead>

                            <tbody>

                                @forelse ($roles as $role)

                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $role->name }}</td>

                                        <td>
                                            @foreach ($role->permissions as $perm)
                                                @foreach (config('permissions_ar') as $key => $value)
                                                    {{ $key == $perm ? $value . ' ، ' : '' }}
                                                @endforeach
                                            @endforeach
                                        </td>

                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-danger dropdown-toggle" type="button"
                                                    data-toggle="dropdown">
                                                    الإجراءات
                                                </button>

                                                <div class="dropdown-menu">

                                                    <a class="dropdown-item"
                                                       href="{{ route('dashboard.roles.edit', $role->id) }}">
                                                        <i class="la la-edit"></i> تعديل
                                                    </a>

                                                    @if($role->admins->count() > 0)

                                                        <div class="dropdown-divider"></div>

                                                        <a class="dropdown-item"
                                                            href="javascript:void(0)"
                                                            onclick="alert('لا يمكن حذف هذه الصلاحية لأنها مرتبطة بمستخدمين')">
                                                            <i class="la la-trash"></i> حذف
                                                        </a>

                                                    @else

                                                        <div class="dropdown-divider"></div>

                                                        <a class="dropdown-item"
                                                           href="javascript:void(0)"
                                                           onclick="if(confirm('هل أنت متأكد من حذف هذه الصلاحية؟')) document.getElementById('delete-form-{{ $role->id }}').submit();">
                                                            <i class="la la-trash"></i> حذف
                                                        </a>

                                                    @endif

                                                </div>
                                            </div>
                                        </td>
                                    </tr>

                                    {{-- حذف --}}
                                    <form id="delete-form-{{ $role->id }}"
                                        method="POST"
                                        action="{{ route('dashboard.roles.destroy', $role->id) }}">
                                        @csrf
                                        @method('DELETE')
                                    </form>

                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">
                                            لا توجد بيانات
                                        </td>
                                    </tr>
                                @endforelse

                            </tbody>
                        </table>

                        {{ $roles->links() }}

                    </div>
                </div>

            </div>
        </div>

    </div>
</div>
@endsection
