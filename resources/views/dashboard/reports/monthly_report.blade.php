@extends('layouts.dashboard.app')

@section('title') التقرير الشهري للرواتب @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-10 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">التقرير الشهري للرواتب</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            {{-- Filter Form --}}
            <div class="card mb-3">
                <div class="card-body py-3">
                    <form method="GET" action="{{ route('dashboard.reports.monthly') }}" class="form-inline flex-wrap">
                        <div class="form-group mr-3 mb-2">
                            <label class="mr-2 font-weight-bold">السنة:</label>
                            <select name="year" class="form-control form-control-sm">
                                @foreach ($availableYears as $yr)
                                    <option value="{{ $yr }}" {{ $yr == $selectedYear ? 'selected' : '' }}>
                                        {{ $yr }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group mr-3 mb-2">
                            <label class="mr-2 font-weight-bold">الأشهر:</label>
                            <div class="d-flex flex-wrap">
                                @foreach ($allMonths as $m)
                                    <div class="form-check mr-2 mb-1">
                                        <input class="form-check-input" type="checkbox"
                                               name="months[]" value="{{ $m }}" id="month-{{ $m }}"
                                               {{ in_array($m, $selectedMonths) ? 'checked' : '' }}>
                                        <label class="form-check-label small" for="month-{{ $m }}">
                                            {{ $arabicMonths[$m] }}
                                        </label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="form-group mb-2">
                            <button type="submit" class="btn btn-sm btn-primary">
                                <i class="la la-search"></i> عرض
                            </button>
                            <a href="{{ route('dashboard.reports.monthly') }}" class="btn btn-sm btn-outline-secondary mr-1">
                                <i class="la la-refresh"></i> إعادة ضبط
                            </a>
                        </div>
                    </form>
                </div>
            </div>

            @if ($periods->isEmpty())
                <div class="alert alert-info">
                    <i class="la la-info-circle mr-1"></i>
                    لا توجد فترات شهرية للسنة {{ $selectedYear }}
                    للأشهر المحددة. يرجى إنشاء الفترات الشهرية أولاً.
                </div>
            @else

                {{-- Periods summary --}}
                <div class="alert alert-secondary py-2 mb-3">
                    <strong>الفترات المحددة:</strong>
                    @foreach ($periods as $p)
                        <span class="badge badge-info ml-1">{{ $p->getDisplayLabel() }}</span>
                    @endforeach
                </div>

                {{-- HungerStation Settlements --}}
                @if ($hsSettlements->isNotEmpty())
                    <div class="card mb-4">
                        <div class="card-header d-flex justify-content-between align-items-center" style="background:#fff3cd;">
                            <h5 class="mb-0"><i class="la la-building text-warning"></i> هنقرستيشن — تسويات المناديب</h5>
                            <div class="d-flex align-items-center">
                                <span class="badge badge-warning mr-2">{{ $hsSettlements->count() }} مندوب</span>
                                <strong class="text-success">
                                    صافي: {{ number_format($hsSettlements->sum('net_salary'), 2) }} ريال
                                </strong>
                            </div>
                        </div>
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>المندوب</th>
                                            <th>الفترة</th>
                                            <th class="text-right">Distance Pay</th>
                                            <th class="text-right">غرامات</th>
                                            <th class="text-right">خصومات الشركة</th>
                                            <th class="text-right">صافي الراتب</th>
                                            <th>الطلبات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($hsSettlements as $s)
                                            <tr>
                                                <td>
                                                    {{ $s->delegate?->name ?? '—' }}
                                                    <br><small class="text-muted">{{ $s->delegate?->delegate_code ?? '' }}</small>
                                                </td>
                                                <td>
                                                    <small>{{ $s->period?->getDisplayLabel() ?? '—' }}</small>
                                                </td>
                                                <td class="text-right">{{ number_format($s->distance_payment, 2) }}</td>
                                                <td class="text-right text-danger">
                                                    @if ($s->total_platform_penalties > 0)
                                                        ({{ number_format($s->total_platform_penalties, 2) }})
                                                    @else —
                                                    @endif
                                                </td>
                                                <td class="text-right text-danger">
                                                    @if ($s->company_deductions_total > 0)
                                                        ({{ number_format($s->company_deductions_total, 2) }})
                                                    @else —
                                                    @endif
                                                </td>
                                                <td class="text-right font-weight-bold {{ $s->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($s->net_salary, 2) }}
                                                </td>
                                                <td>{{ number_format($s->total_orders) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-warning">
                                        <tr>
                                            <td colspan="2" class="font-weight-bold">الإجمالي</td>
                                            <td class="text-right font-weight-bold">{{ number_format($hsSettlements->sum('distance_payment'), 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">({{ number_format($hsSettlements->sum('total_platform_penalties'), 2) }})</td>
                                            <td class="text-right font-weight-bold text-danger">({{ number_format($hsSettlements->sum('company_deductions_total'), 2) }})</td>
                                            <td class="text-right font-weight-bold text-success">{{ number_format($hsSettlements->sum('net_salary'), 2) }}</td>
                                            <td class="font-weight-bold">{{ number_format($hsSettlements->sum('total_orders')) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                    </div>
                @endif

                {{-- Chefz Settlements --}}
                @php
                    $chefzPeriodIds = $periods->filter(fn($p) => $p->platform?->code === 'the-chefz')->pluck('id');
                    $hasChefzPeriods = $chefzPeriodIds->isNotEmpty();
                @endphp
                @if ($hasChefzPeriods || $chefzSettlements->isNotEmpty())
                    <div class="card mb-4">
                        <div class="card-header" style="background:#d1ecf1;">
                            <div class="d-flex justify-content-between align-items-start flex-wrap gap-2">
                                <h5 class="mb-0"><i class="la la-building text-info"></i> شيفز — تسويات المناديب</h5>
                                <div class="d-flex align-items-center gap-2">
                                    @if($chefzSettlements->isNotEmpty())
                                        <span class="badge badge-info">{{ $chefzSettlements->count() }} مدخل</span>
                                        <strong class="text-success">
                                            صافي: {{ number_format($chefzSettlements->sum('net_salary'), 2) }} ريال
                                        </strong>
                                    @endif
                                </div>
                            </div>
                            {{-- Payout filter tabs --}}
                            @php
                                $reportBaseUrl = url()->current() . '?' . http_build_query(array_merge(request()->except('chefz_payout'), ['year' => $selectedYear, 'months' => $selectedMonths]));
                            @endphp
                            <div class="mt-2 d-flex gap-2 flex-wrap">
                                <a href="{{ $reportBaseUrl }}&chefz_payout=0"
                                   class="btn btn-xs {{ $chefzPayoutFilter === 0 ? 'btn-info' : 'btn-outline-info' }}">
                                    إجمالي الشهر
                                </a>
                                <a href="{{ $reportBaseUrl }}&chefz_payout=1"
                                   class="btn btn-xs {{ $chefzPayoutFilter === 1 ? 'btn-primary' : 'btn-outline-primary' }}">
                                    الدفعة الأولى
                                </a>
                                <a href="{{ $reportBaseUrl }}&chefz_payout=2"
                                   class="btn btn-xs {{ $chefzPayoutFilter === 2 ? 'btn-secondary' : 'btn-outline-secondary' }}">
                                    الدفعة الثانية
                                </a>
                            </div>
                        </div>
                        @if($chefzSettlements->isNotEmpty())
                        <div class="card-body p-0">
                            <div class="table-responsive">
                                <table class="table table-sm table-hover mb-0">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>المندوب</th>
                                            <th>الفترة</th>
                                            @if($chefzPayoutFilter === 0)
                                                <th class="text-center">الدفعة</th>
                                            @else
                                                <th class="text-center">الدفعة</th>
                                            @endif
                                            <th class="text-right">رسوم التوصيل</th>
                                            <th class="text-right">ضريبة</th>
                                            <th class="text-right">حصة الشركة</th>
                                            <th class="text-right">خصومات المنصة</th>
                                            <th class="text-right">تعويضات</th>
                                            <th class="text-right">مكافآت</th>
                                            <th class="text-right">صافي الراتب</th>
                                            <th>الطلبات</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($chefzSettlements as $s)
                                            <tr>
                                                <td>
                                                    {{ $s->delegate?->name ?? '—' }}
                                                    <br><small class="text-muted">{{ $s->delegate?->national_id ?? $s->delegate?->delegate_code ?? '' }}</small>
                                                </td>
                                                <td><small>{{ $s->period?->getDisplayLabel() ?? '—' }}</small></td>
                                                <td class="text-center">
                                                    @php $pn = $s->payout_number ?? 0; @endphp
                                                    @if($pn === 1)
                                                        <span class="badge badge-primary">د١</span>
                                                    @elseif($pn === 2)
                                                        <span class="badge badge-secondary">د٢</span>
                                                    @else
                                                        <span class="badge badge-light text-muted">إجمالي</span>
                                                    @endif
                                                </td>
                                                <td class="text-right">{{ number_format($s->gross_delivery_fees ?? 0, 2) }}</td>
                                                <td class="text-right text-danger">
                                                    @php $vat = ($s->chefz_tax_amount ?? 0); @endphp
                                                    @if($vat > 0)({{ number_format($vat, 2) }})@else —@endif
                                                </td>
                                                <td class="text-right" style="color:#8b5cf6;">
                                                    @php $cs = ($s->company_share_amount ?? 0); @endphp
                                                    @if($cs > 0)({{ number_format($cs, 2) }})@else —@endif
                                                </td>
                                                <td class="text-right text-danger">
                                                    @php $ded = ($s->platform_deductions ?? 0); @endphp
                                                    @if($ded > 0)({{ number_format($ded, 2) }})@else —@endif
                                                </td>
                                                <td class="text-right text-info">
                                                    @php $comp = ($s->platform_compensations ?? 0); @endphp
                                                    @if($comp > 0){{ number_format($comp, 2) }}@else —@endif
                                                </td>
                                                <td class="text-right text-warning">
                                                    @php $bon = ($s->positive_bonus ?? 0); @endphp
                                                    @if($bon > 0){{ number_format($bon, 2) }}@else —@endif
                                                </td>
                                                <td class="text-right font-weight-bold {{ ($s->net_salary ?? 0) < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($s->net_salary ?? 0, 2) }}
                                                </td>
                                                <td>{{ number_format($s->total_orders ?? 0) }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot class="table-info">
                                        <tr>
                                            <td colspan="3" class="font-weight-bold">الإجمالي</td>
                                            <td class="text-right font-weight-bold">{{ number_format($chefzSettlements->sum('gross_delivery_fees'), 2) }}</td>
                                            <td class="text-right font-weight-bold text-danger">({{ number_format($chefzSettlements->sum('chefz_tax_amount'), 2) }})</td>
                                            <td class="text-right font-weight-bold" style="color:#8b5cf6;">({{ number_format($chefzSettlements->sum('company_share_amount'), 2) }})</td>
                                            <td class="text-right font-weight-bold text-danger">({{ number_format($chefzSettlements->sum('platform_deductions'), 2) }})</td>
                                            <td class="text-right font-weight-bold text-info">{{ number_format($chefzSettlements->sum('platform_compensations'), 2) }}</td>
                                            <td class="text-right font-weight-bold text-warning">{{ number_format($chefzSettlements->sum('positive_bonus'), 2) }}</td>
                                            <td class="text-right font-weight-bold text-success">{{ number_format($chefzSettlements->sum('net_salary'), 2) }}</td>
                                            <td class="font-weight-bold">{{ number_format($chefzSettlements->sum('total_orders')) }}</td>
                                        </tr>
                                    </tfoot>
                                </table>
                            </div>
                        </div>
                        @else
                            <div class="card-body">
                                <div class="text-muted text-center py-3">
                                    <i class="la la-info-circle"></i>
                                    لا توجد تسويات شيفز للفترات المحددة بهذه الدفعة. يرجى استيراد البيانات أولاً.
                                </div>
                            </div>
                        @endif
                    </div>
                @endif

                @if ($hsSettlements->isEmpty() && $chefzSettlements->isEmpty())
                    <div class="alert alert-warning">
                        <i class="la la-exclamation-triangle mr-1"></i>
                        لا توجد تسويات في الفترات المحددة. يرجى استيراد البيانات أولاً.
                    </div>
                @endif

            @endif

        </div>
    </div>
</div>
@endsection
