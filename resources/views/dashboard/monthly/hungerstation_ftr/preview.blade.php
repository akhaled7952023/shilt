@extends('layouts.dashboard.app')

@section('title') معاينة الاستيراد — {{ $period->getDisplayLabel() }} @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- Breadcrumb --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-10 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.index') }}">الفترات الشهرية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.show', $period) }}">{{ $period->getDisplayLabel() }}</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.monthly.periods.hungerstation.ftr.import', $period) }}">استيراد هنقرستيشن</a></li>
                            <li class="breadcrumb-item active">معاينة</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            @if ($dto->isReplace)
                <div class="alert alert-warning">
                    <i class="la la-exclamation-triangle mr-1"></i>
                    <strong>وضع الاستبدال:</strong> تأكيد الاستيراد سيحذف بيانات الاستيراد السابقة لهذه الفترة.
                </div>
            @endif

            @if ($dto->warnings)
                <div class="alert alert-info">
                    <strong>تنبيهات:</strong>
                    <ul class="mb-0 mt-1">
                        @foreach ($dto->warnings as $warning)
                            <li>{{ $warning }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{-- Summary cards --}}
            <div class="row mb-2">
                <div class="col-md-3 col-sm-6 mb-1">
                    <div class="card text-center" style="border-right:4px solid #4e73df;">
                        <div class="card-body py-2">
                            <div class="font-large-1 font-weight-bold">{{ $dto->totalRiders }}</div>
                            <small class="text-muted">إجمالي الركاب في الملف</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-1">
                    <div class="card text-center" style="border-right:4px solid #1cc88a;">
                        <div class="card-body py-2">
                            <div class="font-large-1 font-weight-bold text-success">{{ $dto->matchedCount }}</div>
                            <small class="text-muted">مطابق (سيُستورد)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-1">
                    <div class="card text-center" style="border-right:4px solid #e74a3b;">
                        <div class="card-body py-2">
                            <div class="font-large-1 font-weight-bold text-danger">{{ $dto->unmatchedCount }}</div>
                            <small class="text-muted">غير مطابق (سيُتجاهل)</small>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 col-sm-6 mb-1">
                    <div class="card text-center" style="border-right:4px solid #f6c23e;">
                        <div class="card-body py-2">
                            <div class="font-large-1 font-weight-bold">
                                {{ number_format($dto->totals['distance_payment'], 2) }}
                            </div>
                            <small class="text-muted">مجموع مدفوعات المسافات</small>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Financial totals --}}
            <div class="card mb-2">
                <div class="card-header">
                    <h4 class="card-title">الملخص المالي (المطابقون فقط)</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4">
                            <table class="table table-sm table-borderless">
                                <tr>
                                    <td class="text-muted">الدفع الأساسي (إيرادات الشركة)</td>
                                    <td class="font-weight-bold text-right">
                                        {{ number_format($dto->totals['basic_payment'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">مدفوعات المسافات (أساس الراتب)</td>
                                    <td class="font-weight-bold text-right">
                                        {{ number_format($dto->totals['distance_payment'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">رصيد المحفظة</td>
                                    <td class="font-weight-bold text-right text-warning">
                                        {{ number_format($dto->totals['rider_balance'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">خصم الطلبات المتعددة (تتحمله الشركة)</td>
                                    <td class="font-weight-bold text-right text-info">
                                        {{ number_format($dto->totals['stacking'], 2) }}
                                    </td>
                                </tr>
                                <tr>
                                    <td class="text-muted">مجموع غرامات المنصة</td>
                                    <td class="font-weight-bold text-right text-danger">
                                        {{ number_format($dto->totals['penalties'], 2) }}
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Matched riders table --}}
            @if ($dto->matchedRiders)
                <div class="card mb-2">
                    <div class="card-header">
                        <h4 class="card-title text-success">
                            <i class="la la-check-circle"></i>
                            الركاب المطابقون ({{ $dto->matchedCount }})
                        </h4>
                    </div>
                    <div class="card-body p-0">
                        <div class="table-responsive">
                            <table class="table table-sm table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>المعرف</th>
                                        <th>المندوب</th>
                                        <th class="text-right">مدفوعات المسافات</th>
                                        <th class="text-right">غرامات المنصة</th>
                                        <th class="text-right">رصيد المحفظة</th>
                                        <th class="text-right">صافي تقديري</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($dto->matchedRiders as $rider)
                                        <tr>
                                            <td>
                                                <code>{{ $rider['rider_id_platform'] }}</code>
                                            </td>
                                            <td>{{ $rider['delegate_name'] }}</td>
                                            <td class="text-right">{{ number_format($rider['distance_payment'], 2) }}</td>
                                            <td class="text-right text-danger">
                                                @if ($rider['total_penalties'] > 0)
                                                    ({{ number_format($rider['total_penalties'], 2) }})
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-right text-warning">
                                                @if ($rider['rider_balance'] > 0)
                                                    ({{ number_format($rider['rider_balance'], 2) }})
                                                @else
                                                    —
                                                @endif
                                            </td>
                                            <td class="text-right font-weight-bold
                                                {{ $rider['estimated_net'] < 0 ? 'text-danger' : '' }}">
                                                {{ number_format($rider['estimated_net'], 2) }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            @endif

            {{-- Unmatched riders --}}
            @if ($dto->unmatchedRiders)
                <div class="card mb-2">
                    <div class="card-header">
                        <h4 class="card-title text-danger">
                            <i class="la la-exclamation-circle"></i>
                            ركاب غير مطابقون — سيُتجاهلون ({{ $dto->unmatchedCount }})
                        </h4>
                    </div>
                    <div class="card-body">
                        <p class="text-muted mb-1">
                            لا يوجد مندوب مسجل بهذه المعرفات في النظام.
                            أضف المعرف للمندوبين المعنيين من صفحة تعديل المندوب ثم أعد الاستيراد.
                        </p>
                        <div class="d-flex flex-wrap">
                            @foreach ($dto->unmatchedRiders as $riderId)
                                <span class="badge badge-light border mr-1 mb-1 p-1">{{ $riderId }}</span>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endif

            {{-- Action buttons --}}
            <div class="card">
                <div class="card-body d-flex align-items-center">
                    @if ($dto->isConfirmable)
                        <form method="POST"
                              action="{{ route('dashboard.monthly.periods.hungerstation.ftr.import.confirm', $period) }}"
                              id="confirm-form">
                            @csrf
                            <button type="submit" class="btn btn-success mr-1" id="confirm-btn">
                                <i class="la la-check"></i>
                                {{ $dto->isReplace ? 'تأكيد الاستبدال' : 'تأكيد الاستيراد' }}
                                ({{ $dto->matchedCount }} مندوب)
                            </button>
                        </form>
                    @else
                        <button class="btn btn-success mr-1" disabled>
                            <i class="la la-check"></i> لا يوجد مندوبون مطابقون
                        </button>
                    @endif

                    <form method="POST"
                          action="{{ route('dashboard.monthly.periods.hungerstation.ftr.import.cancel', $period) }}">
                        @csrf
                        <button type="submit" class="btn btn-outline-secondary">
                            <i class="la la-times"></i> إلغاء والعودة
                        </button>
                    </form>
                </div>
            </div>

        </div>
    </div>
</div>

@push('scripts')
<script>
document.getElementById('confirm-form')?.addEventListener('submit', function () {
    var btn = document.getElementById('confirm-btn');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<i class="la la-spinner la-spin"></i> جارٍ الحفظ...';
    }
});
</script>
@endpush
@endsection
