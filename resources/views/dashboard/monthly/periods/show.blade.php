@extends('layouts.dashboard.app')

@section('title') مساحة العمل — {{ $period->platform?->name }} — {{ $period->getDisplayLabel() }} @endsection

@php
$platformCode = $period->platform?->code ?? '';

$importRoute = match($platformCode) {
    'the-chefz'    => 'dashboard.monthly.periods.chefz.import',
    'hungerstation' => 'dashboard.monthly.periods.hungerstation.ftr.import',
    default        => null,
};

$settlementRoute = match($platformCode) {
    'hungerstation' => 'dashboard.monthly.periods.hungerstation.ftr.settlement.index',
    'the-chefz'    => 'dashboard.monthly.periods.chefz.settlement.index',
    default        => null,
};
@endphp

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- Breadcrumb --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-8 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.monthly.periods.index') }}">الفترات الشهرية</a>
                            </li>
                            <li class="breadcrumb-item active">
                                {{ $period->platform?->name ?? '—' }} — {{ $period->getDisplayLabel() }}
                            </li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-4 col-12 d-flex justify-content-end align-items-center mb-2">
                @if ($period->isOpen())
                    <button type="button"
                            class="btn btn-danger"
                            data-toggle="modal"
                            data-target="#closeMonthModal">
                        <i class="la la-lock"></i> إغلاق الشهر
                    </button>
                @elseif ($period->isClosed())
                    <button type="button"
                            class="btn btn-outline-success"
                            data-toggle="modal"
                            data-target="#reopenMonthModal">
                        <i class="la la-unlock-alt"></i> إعادة فتح الشهر
                    </button>
                @endif
            </div>
        </div>

        <div class="content-body">

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="la la-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Period summary header --}}
            <div class="card mb-2">
                <div class="card-body py-2">
                    <div class="row align-items-center">
                        <div class="col-md-4">
                            <small class="text-muted d-block">المنصة</small>
                            <strong class="font-medium-2">{{ $period->platform?->name ?? '—' }}</strong>
                        </div>
                        <div class="col-md-4 text-center">
                            <small class="text-muted d-block">الفترة</small>
                            <strong class="font-medium-2">{{ $period->getDisplayLabel() }}</strong>
                        </div>
                        <div class="col-md-4 text-left">
                            <small class="text-muted d-block">الحالة</small>
                            @if ($period->isOpen())
                                <span class="badge badge-success">
                                    <i class="la la-unlock-alt"></i> مفتوح
                                </span>
                            @else
                                <span class="badge badge-secondary">
                                    <i class="la la-lock"></i> مغلق
                                </span>
                                @if ($period->closed_at)
                                    <small class="text-muted d-block mt-1">
                                        أُغلق في {{ $period->closed_at->format('Y-m-d') }}
                                        @if ($period->closedBy)
                                            بواسطة {{ $period->closedBy->name }}
                                        @endif
                                    </small>
                                @endif
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if ($period->isClosed())
                <div class="alert alert-secondary mb-2" role="alert">
                    <i class="la la-lock font-medium-2 mr-1"></i>
                    <strong>هذه الفترة مغلقة</strong> — لا يمكن إجراء أي تعديلات. جميع البيانات في وضع القراءة فقط.
                </div>
            @endif

            {{-- Workspace cards --}}
            <div class="row">

                {{-- Import --}}
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="card h-100" style="border-right: 4px solid #4e73df;">
                        <div class="card-body text-center py-4">
                            <i class="la la-upload font-large-2 text-primary mb-2 d-block"></i>
                            <h5 class="card-title">استيراد {{ $period->platform?->name }}</h5>
                            <p class="text-muted small mb-3">
                                @if($platformCode === 'the-chefz')
                                    رفع ملفات Excel من شيفز وإضافتها إلى بيانات الفترة.
                                @else
                                    رفع ملف Excel الشهري واستيراد بيانات الطلبات.
                                @endif
                            </p>
                            @if($importRoute)
                                @if($period->isOpen())
                                    <a href="{{ route($importRoute, $period) }}"
                                       class="btn btn-primary btn-sm">
                                        <i class="la la-upload"></i> الاستيراد
                                    </a>
                                @else
                                    <a href="{{ route($importRoute, $period) }}"
                                       class="btn btn-outline-secondary btn-sm">
                                        <i class="la la-eye"></i> عرض الاستيراد
                                    </a>
                                @endif
                            @else
                                <span class="badge badge-light border">غير متاح</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Settlement Management --}}
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="card h-100" style="border-right: 4px solid #1cc88a;">
                        <div class="card-body text-center py-4">
                            <i class="la la-calculator font-large-2 text-success mb-2 d-block"></i>
                            <h5 class="card-title">إدارة التسويات</h5>
                            <p class="text-muted small mb-3">
                                التسويات والخصومات والإضافات اليدوية لكل مندوب.
                            </p>
                            @if($settlementRoute)
                                <a href="{{ route($settlementRoute, $period) }}"
                                   class="btn btn-success btn-sm">
                                    <i class="la la-calculator"></i> فتح التسويات
                                </a>
                            @else
                                <span class="badge badge-light border">قادم قريباً</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Company Expenses: HungerStation only --}}
                @if($platformCode === 'hungerstation')
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="card h-100" style="border-right: 4px solid #e74a3b;">
                        <div class="card-body text-center py-4">
                            <i class="la la-money font-large-2 text-danger mb-2 d-block"></i>
                            <h5 class="card-title">مصروفات الشركة</h5>
                            <p class="text-muted small mb-3">
                                تسجيل مصروفات الشركة الشهرية المرتبطة بهذه الفترة.
                            </p>
                            <a href="{{ route('dashboard.monthly.periods.expenses.index', $period) }}"
                               class="btn btn-danger btn-sm">
                                <i class="la la-list"></i> إدارة المصروفات
                            </a>
                        </div>
                    </div>
                </div>
                @endif

                {{-- Financial Dashboard --}}
                <div class="col-md-4 col-sm-6 mb-2">
                    <div class="card h-100" style="border-right: 4px solid #36b9cc;">
                        <div class="card-body text-center py-4">
                            <i class="la la-bar-chart font-large-2 text-info mb-2 d-block"></i>
                            <h5 class="card-title">التقارير والتحليلات</h5>
                            <p class="text-muted small mb-3">
                                اللوحة المالية الشهرية — ملخص الإيرادات والمصروفات والتقارير.
                            </p>
                            <a href="{{ route('dashboard.monthly.periods.financial-dashboard', $period) }}"
                               class="btn btn-info btn-sm">
                                <i class="la la-bar-chart"></i> اللوحة المالية
                            </a>
                        </div>
                    </div>
                </div>

            </div>

        </div>
    </div>
</div>

{{-- Reopen Modal --}}
@if ($period->isClosed())
<div class="modal fade" id="reopenMonthModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title">
                    <i class="la la-unlock-alt"></i> تأكيد إعادة فتح الشهر
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="la la-exclamation-triangle"></i>
                    <strong>تحذير:</strong> إعادة فتح الفترة ستسمح بتعديل البيانات مجدداً.
                </div>
                <table class="table table-sm table-bordered mb-0">
                    <tr>
                        <th class="bg-light" style="width:40%">المنصة</th>
                        <td><strong>{{ $period->platform?->name ?? '—' }}</strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">الفترة</th>
                        <td><strong>{{ $period->getDisplayLabel() }}</strong></td>
                    </tr>
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">إلغاء</button>
                <form method="POST"
                      action="{{ route('dashboard.monthly.periods.reopen', $period) }}"
                      class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-success">
                        <i class="la la-unlock-alt"></i> تأكيد إعادة الفتح
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@if ($period->isOpen())
<div class="modal fade" id="closeMonthModal" tabindex="-1" role="dialog" aria-labelledby="closeMonthModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title" id="closeMonthModalLabel">
                    <i class="la la-lock"></i> تأكيد إغلاق الشهر
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="alert alert-warning mb-3">
                    <i class="la la-exclamation-triangle"></i>
                    <strong>تحذير:</strong> هذا الإجراء لا يمكن التراجع عنه. بعد الإغلاق لن تتمكن من تعديل بيانات هذه الفترة.
                </div>
                <table class="table table-sm table-bordered mb-3">
                    <tr>
                        <th class="bg-light" style="width:40%">المنصة</th>
                        <td><strong>{{ $period->platform?->name ?? '—' }}</strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">الفترة</th>
                        <td><strong>{{ $period->getDisplayLabel() }}</strong></td>
                    </tr>
                    <tr>
                        <th class="bg-light">الحالة الحالية</th>
                        <td><span class="badge badge-success">مفتوح</span></td>
                    </tr>
                    <tr>
                        <th class="bg-light">الحالة بعد الإغلاق</th>
                        <td><span class="badge badge-secondary">مغلق</span></td>
                    </tr>
                </table>
                <p class="text-muted small mb-0">سيتم تسجيل وقت الإغلاق والمستخدم المسؤول تلقائياً.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-dismiss="modal">
                    <i class="la la-times"></i> إلغاء
                </button>
                <form method="POST" action="{{ route('dashboard.monthly.periods.close', $period) }}" class="d-inline">
                    @csrf
                    <button type="submit" class="btn btn-danger">
                        <i class="la la-lock"></i> تأكيد الإغلاق
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endif

@endsection
