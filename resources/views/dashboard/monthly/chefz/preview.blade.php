@extends('layouts.dashboard.app')

@section('title') مراجعة استيراد شيفز — {{ $period->getDisplayLabel() }} @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-10 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.index') }}">الفترات الشهرية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.show', $period) }}">{{ $period->getDisplayLabel() }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.chefz.import', $period) }}">استيراد شيفز</a></li>
                            <li class="breadcrumb-item active">مراجعة البيانات</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            {{-- Payout context bar --}}
            <div class="alert alert-{{ $dto->isReplace ? 'warning' : 'info' }} mb-3">
                @if ($dto->isReplace)
                    <i class="la la-exclamation-triangle"></i>
                    <strong>وضع الاستبدال:</strong>
                    تأكيد الاستيراد سيحذف جميع بيانات
                    <strong>{{ $dto->payoutNumber == 1 ? 'الدفعة الأولى' : 'الدفعة الثانية' }}</strong>
                    الحالية ويستبدلها بهذا الملف.
                    بيانات الدفعة الأخرى لن تتأثر.
                @else
                    <i class="la la-info-circle"></i>
                    استيراد جديد لـ
                    <strong>{{ $dto->payoutNumber == 1 ? 'الدفعة الأولى' : 'الدفعة الثانية' }}</strong>
                    — {{ $period->getDisplayLabel() }}
                @endif
            </div>

            {{-- Stat cards --}}
            <div class="row mb-3">
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card text-center" style="border-top:3px solid #4e73df;">
                        <div class="card-body py-3">
                            <div class="font-large-2 font-weight-bold text-primary">{{ number_format($dto->newRows) }}</div>
                            <div class="text-muted small">طلب في الملف</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card text-center" style="border-top:3px solid #fd7e14;">
                        <div class="card-body py-3">
                            <div class="font-large-2 font-weight-bold" style="color:#fd7e14;">{{ number_format($dto->inFileDuplicates) }}</div>
                            <div class="text-muted small">مكرر داخل الملف (تجاهل)</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card text-center" style="border-top:3px solid #1cc88a;">
                        <div class="card-body py-3">
                            <div class="font-large-2 font-weight-bold text-success">{{ number_format($dto->uniqueDelegates) }}</div>
                            <div class="text-muted small">مندوب</div>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-2">
                    <div class="card text-center" style="border-top:3px solid #6f42c1;">
                        <div class="card-body py-3">
                            <div class="font-large-2 font-weight-bold text-purple">{{ number_format($dto->willCreateCount) }}</div>
                            <div class="text-muted small">مندوب سيُنشأ تلقائياً</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- File info --}}
            <div class="card mb-3">
                <div class="card-body py-2">
                    <div class="row">
                        <div class="col-md-5">
                            <small class="text-muted d-block">الملف</small>
                            <strong>{{ $dto->originalFilename }}</strong>
                        </div>
                        <div class="col-md-3">
                            <small class="text-muted d-block">الفترة</small>
                            <strong>{{ $period->getDisplayLabel() }}</strong>
                        </div>
                        <div class="col-md-4">
                            <small class="text-muted d-block">الدفعة</small>
                            <span class="badge badge-{{ $dto->payoutNumber == 1 ? 'primary' : 'info' }} font-medium-1">
                                {{ $dto->payoutNumber == 1 ? 'الدفعة الأولى' : 'الدفعة الثانية' }}
                            </span>
                            @if ($dto->isReplace)
                                <span class="badge badge-warning ml-1">استبدال</span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>

            @if(!empty($dto->errors))
                <div class="card border-danger mb-3">
                    <div class="card-header bg-danger text-white py-2">
                        <strong><i class="la la-times-circle"></i> أخطاء تمنع الاستيراد</strong>
                    </div>
                    <div class="card-body p-0">
                        <ul class="list-group list-group-flush">
                            @foreach($dto->errors as $error)
                                <li class="list-group-item list-group-item-danger py-2">{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if(!empty($dto->warnings))
                <div class="card border-warning mb-3">
                    <div class="card-header bg-warning py-2">
                        <strong><i class="la la-exclamation-triangle"></i> تحذيرات ({{ count($dto->warnings) }})</strong>
                    </div>
                    <div class="card-body p-0" style="max-height:200px;overflow-y:auto;">
                        <ul class="list-group list-group-flush">
                            @foreach($dto->warnings as $warning)
                                <li class="list-group-item list-group-item-warning py-1 small">{{ $warning }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            @endif

            @if($dto->newRows > 0)
                <div class="card mb-3">
                    <div class="card-header py-2 bg-light">
                        <strong><i class="la la-balance-scale"></i> ملخص الطلبات</strong>
                    </div>
                    <div class="card-body p-0">
                        <table class="table table-sm mb-0">
                            <tbody>
                                <tr>
                                    <td class="bg-light" style="width:45%">عدد الطلبات</td>
                                    <td><strong>{{ number_format($dto->reconciliation['total_rows']) }}</strong></td>
                                </tr>
                                <tr>
                                    <td class="bg-light">إجمالي رسوم التوصيل (شاملة الضريبة)</td>
                                    <td class="text-success font-weight-bold">{{ number_format($dto->reconciliation['gross_fees'], 2) }} ريال</td>
                                </tr>
                                <tr>
                                    <td class="bg-light">إجمالي خصومات المنصة</td>
                                    <td class="text-danger">{{ number_format($dto->reconciliation['deductions'], 2) }} ريال</td>
                                </tr>
                                <tr>
                                    <td class="bg-light">إجمالي تعويضات المنصة</td>
                                    <td class="text-info">{{ number_format($dto->reconciliation['compensations'], 2) }} ريال</td>
                                </tr>
                                @if (($dto->reconciliation['bonus_total'] ?? 0) != 0)
                                    <tr>
                                        <td class="bg-light">إجمالي المكافآت (قد يشمل ملغاة)</td>
                                        <td class="{{ $dto->reconciliation['bonus_total'] >= 0 ? 'text-success' : 'text-warning' }}">
                                            {{ number_format($dto->reconciliation['bonus_total'], 2) }} ريال
                                        </td>
                                    </tr>
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            @if(!empty($dto->perDelegateSummary))
                <div class="card mb-3">
                    <div class="card-header py-2">
                        <strong><i class="la la-users"></i> توزيع على المناديب</strong>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive" style="max-height:350px;overflow-y:auto;">
                            <table class="table table-sm mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>المعرف</th>
                                        <th>الاسم</th>
                                        <th class="text-center">طلبات</th>
                                        <th class="text-right">رسوم التوصيل</th>
                                        <th class="text-right">خصومات</th>
                                        <th class="text-center">حالة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dto->perDelegateSummary as $d)
                                        <tr>
                                            <td class="text-monospace small">{{ $d['driver_id'] }}</td>
                                            <td>{{ $d['name'] }}</td>
                                            <td class="text-center">{{ number_format($d['orders']) }}</td>
                                            <td class="text-right text-success">{{ number_format($d['gross_fees'], 2) }}</td>
                                            <td class="text-right text-danger">{{ $d['deductions'] > 0 ? '('.number_format($d['deductions'], 2).')' : '—' }}</td>
                                            <td class="text-center">
                                                @if($d['status'] === 'known')
                                                    <span class="badge badge-success">موجود</span>
                                                @else
                                                    <span class="badge badge-warning">سيُنشأ</span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Action buttons --}}
            <div class="d-flex justify-content-between">
                <form method="POST"
                      action="{{ route('dashboard.monthly.periods.chefz.import.cancel', $period) }}">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary">
                        <i class="la la-times"></i> إلغاء
                    </button>
                </form>

                @if($dto->isConfirmable)
                    <form method="POST"
                          action="{{ route('dashboard.monthly.periods.chefz.import.confirm', $period) }}"
                          id="confirmForm">
                        @csrf
                        <button type="submit" class="btn btn-{{ $dto->isReplace ? 'warning' : 'success' }} btn-lg" id="confirmBtn">
                            <i class="la la-{{ $dto->isReplace ? 'refresh' : 'check-circle' }}"></i>
                            {{ $dto->isReplace ? 'تأكيد الاستبدال' : 'تأكيد الاستيراد' }}
                            ({{ number_format($dto->newRows) }} طلب)
                        </button>
                    </form>
                @else
                    <span class="btn btn-secondary disabled">
                        <i class="la la-ban"></i> لا يمكن الاستيراد
                    </span>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.getElementById('confirmForm')?.addEventListener('submit', function () {
    const btn = document.getElementById('confirmBtn');
    btn.disabled = true;
    btn.innerHTML = '<i class="la la-spinner la-spin"></i> جارٍ الاستيراد...';
});
</script>
@endsection
