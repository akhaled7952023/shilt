@extends('layouts.dashboard.app')

@section('title') الفترات الشهرية @endsection

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
                            <li class="breadcrumb-item active">الفترات الشهرية</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-6 col-12 d-flex justify-content-end align-items-center mb-2">
                <a href="{{ route('dashboard.monthly.periods.create') }}" class="btn btn-primary">
                    <i class="la la-plus"></i> إضافة فترة شهرية
                </a>
            </div>
        </div>

        <div class="content-body">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="la la-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="la la-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            <div class="card">
                <div class="card-content">
                    <div class="card-body">

                        @if ($periods->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="la la-calendar font-large-3 d-block mb-2"></i>
                                <p class="mb-3">لا توجد فترات شهرية بعد.</p>
                                <a href="{{ route('dashboard.monthly.periods.create') }}" class="btn btn-primary">
                                    <i class="la la-plus"></i> إنشاء أول فترة
                                </a>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-hover table-sm">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>المنصة</th>
                                            <th>الفترة</th>
                                            <th>الحالة</th>
                                            <th>أُنشئت في</th>
                                            <th class="text-center">الإجراءات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($periods as $period)
                                            <tr>
                                                <td>
                                                    <span class="font-weight-bold">
                                                        {{ $period->platform?->name ?? '—' }}
                                                    </span>
                                                </td>
                                                <td>{{ $period->getDisplayLabel() }}</td>
                                                <td>
                                                    @if ($period->isOpen())
                                                        <span class="badge badge-success">
                                                            <i class="la la-unlock-alt"></i> مفتوح
                                                        </span>
                                                    @elseif ($period->isClosed())
                                                        <span class="badge badge-secondary">
                                                            <i class="la la-lock"></i> مغلق
                                                        </span>
                                                    @else
                                                        <span class="badge badge-warning">
                                                            {{ $period->status?->value }}
                                                        </span>
                                                    @endif
                                                </td>
                                                <td class="text-muted small">
                                                    {{ $period->created_at?->format('Y-m-d') }}
                                                </td>
                                                <td class="text-center" style="white-space:nowrap">
                                                    <a href="{{ route('dashboard.monthly.periods.show', $period) }}"
                                                       class="btn btn-sm btn-primary" title="فتح مساحة العمل">
                                                        <i class="la la-briefcase"></i> مساحة العمل
                                                    </a>
                                                    @if ($period->isOpen())
                                                        <form method="POST"
                                                              action="{{ route('dashboard.monthly.periods.destroy', $period) }}"
                                                              class="d-inline"
                                                              onsubmit="return confirm('هل أنت متأكد من حذف فترة {{ $period->getDisplayLabel() }}؟ هذا الإجراء لا يمكن التراجع عنه.')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" title="حذف">
                                                                <i class="la la-trash"></i>
                                                            </button>
                                                        </form>
                                                    @endif
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
@endsection
