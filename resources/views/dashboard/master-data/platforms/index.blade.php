@extends('layouts.dashboard.app')

@section('title') المنصات @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">المنصات</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">

                <div class="card-header">
                    <h4 class="card-title">المنصات</h4>
                </div>

                <div class="card-content">
                    <div class="card-body">

                        <table class="table table-responsive-sm">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th>الاسم</th>
                                    <th>الرمز</th>
                                    <th>الحالة</th>
                                </tr>
                            </thead>
                            <tbody>
                                @forelse ($platforms as $platform)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td>
                                        <td>{{ $platform->name }}</td>
                                        <td><code>{{ $platform->code }}</code></td>
                                        <td>
                                            @include('dashboard.components._status_badge', [
                                                'status' => $platform->is_active ? 'active' : 'inactive'
                                            ])
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center">لا توجد بيانات</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
