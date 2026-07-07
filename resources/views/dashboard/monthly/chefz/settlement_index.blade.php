@extends('layouts.dashboard.app')

@section('title') تسوية شيفز — {{ $period->getDisplayLabel() }} @endsection

@php
$statusLabels = [
    'locked'           => ['label' => 'مقفل',          'badge' => 'badge-dark'],
    'calculated'       => ['label' => 'محسوب',          'badge' => 'badge-success'],
    'needs_manual_data'=> ['label' => 'يحتاج تعديلات', 'badge' => 'badge-warning'],
    'incomplete'       => ['label' => 'غير مكتمل',     'badge' => 'badge-secondary'],
];

$tabLabel = match((int)$payoutFilter) {
    1 => 'الدفعة الأولى',
    2 => 'الدفعة الثانية',
    default => 'إجمالي الشهر',
};
@endphp

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
                            <li class="breadcrumb-item active">تسوية شيفز</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            @foreach(['success','error','info','warning'] as $flash)
                @if (session($flash))
                    <div class="alert alert-{{ $flash === 'error' ? 'danger' : $flash }} alert-dismissible fade show">
                        {{ session($flash) }}
                        <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                    </div>
                @endif
            @endforeach

            {{-- Missing payout warning --}}
            @if (!$isMonthComplete)
                <div class="alert alert-warning mb-2">
                    <i class="la la-exclamation-triangle"></i>
                    @if (!$payout1Batch && !$payout2Batch)
                        <strong>لم يتم استيراد أي دفعة بعد</strong> لهذه الفترة.
                    @elseif (!$payout1Batch)
                        <strong>⚠ الدفعة الأولى مفقودة</strong> — لم يتم استيرادها بعد.
                    @else
                        <strong>⚠ الدفعة الثانية مفقودة</strong> — الشهر غير مكتمل.
                    @endif
                    <a href="{{ route('dashboard.monthly.periods.chefz.import', $period) }}" class="alert-link mr-2">
                        استيراد الدفعة
                    </a>
                </div>
            @endif

            {{-- Payout tabs --}}
            <ul class="nav nav-tabs mb-0" style="border-bottom:none;">
                <li class="nav-item">
                    <a class="nav-link {{ $payoutFilter == 0 ? 'active' : '' }}"
                       href="{{ route('dashboard.monthly.periods.chefz.settlement.index', [$period, 'payout' => 0]) }}">
                        <i class="la la-calendar-check-o"></i> إجمالي الشهر
                        @if ($isMonthComplete)
                            <span class="badge badge-success badge-sm ml-1"><i class="la la-check"></i></span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $payoutFilter == 1 ? 'active' : '' }}"
                       href="{{ route('dashboard.monthly.periods.chefz.settlement.index', [$period, 'payout' => 1]) }}">
                        الدفعة الأولى
                        @if ($payout1Batch)
                            <span class="badge badge-success badge-sm ml-1"><i class="la la-check"></i></span>
                        @else
                            <span class="badge badge-warning badge-sm ml-1">!</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item">
                    <a class="nav-link {{ $payoutFilter == 2 ? 'active' : '' }}"
                       href="{{ route('dashboard.monthly.periods.chefz.settlement.index', [$period, 'payout' => 2]) }}">
                        الدفعة الثانية
                        @if ($payout2Batch)
                            <span class="badge badge-success badge-sm ml-1"><i class="la la-check"></i></span>
                        @else
                            <span class="badge badge-secondary badge-sm ml-1">—</span>
                        @endif
                    </a>
                </li>
                <li class="nav-item mr-auto">
                    <a href="{{ route('dashboard.monthly.periods.chefz.import', $period) }}"
                       class="btn btn-sm btn-outline-primary mt-1">
                        <i class="la la-upload"></i> استيراد دفعة
                    </a>
                </li>
            </ul>

            {{-- Settlement card --}}
            <div class="card" style="border-top-right-radius:0; border-top-left-radius:0;">
                <div class="card-header d-flex align-items-center justify-content-between bg-light py-2">
                    <h5 class="mb-0">
                        <i class="la la-users text-success"></i>
                        {{ $tabLabel }} — {{ $settlements->count() }} مندوب
                    </h5>
                    @if ($payoutFilter == 0 && !$isMonthComplete)
                        <span class="text-warning small"><i class="la la-exclamation-triangle"></i> الشهر غير مكتمل</span>
                    @elseif ($payoutFilter == 0)
                        <span class="text-success small"><i class="la la-check-circle"></i> الشهر مكتمل</span>
                    @endif
                </div>

                <div class="card-body p-0">
                    @if($settlements->isEmpty())
                        <div class="p-5 text-center text-muted">
                            <i class="la la-inbox" style="font-size:2.5rem;"></i>
                            <p class="mt-2 mb-1">
                                @if($payoutFilter == 0)
                                    لا توجد تسويات لهذه الفترة.
                                @else
                                    لم يتم استيراد هذه الدفعة بعد.
                                @endif
                            </p>
                            <a href="{{ route('dashboard.monthly.periods.chefz.import', $period) }}"
                               class="btn btn-sm btn-primary mt-2">
                                <i class="la la-upload"></i> استيراد بيانات شيفز
                            </a>
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>المندوب</th>
                                        <th>المعرف</th>
                                        <th class="text-center">الطلبات</th>
                                        <th class="text-left">الرسوم</th>
                                        <th class="text-left">الضريبة</th>
                                        <th class="text-left">حصة الشركة</th>
                                        <th class="text-left">صافي الراتب</th>
                                        @if ($payoutFilter != 0)
                                            <th class="text-center">الحالة</th>
                                        @endif
                                        <th class="text-center">إجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($settlements as $settlement)
                                        @php
                                            $status = $settlement->computed_status ?? 'calculated';
                                            $s = $statusLabels[$status] ?? ['label' => $status, 'badge' => 'badge-secondary'];
                                        @endphp
                                        <tr>
                                            <td>
                                                <strong>{{ $settlement->delegate?->name ?? '—' }}</strong>
                                            </td>
                                            <td class="text-monospace small">
                                                {{ $settlement->delegate?->national_id ?? '—' }}
                                            </td>
                                            <td class="text-center">{{ number_format($settlement->total_orders) }}</td>
                                            <td class="text-left">{{ number_format($settlement->gross_delivery_fees, 2) }}</td>
                                            <td class="text-left text-danger">{{ number_format($settlement->chefz_tax_amount, 2) }}</td>
                                            <td class="text-left text-danger">{{ number_format($settlement->company_share_amount, 2) }}</td>
                                            <td class="text-left font-weight-bold {{ $settlement->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                                {{ number_format($settlement->net_salary, 2) }}
                                            </td>
                                            @if ($payoutFilter != 0)
                                                <td class="text-center">
                                                    <span class="badge {{ $s['badge'] }}">{{ $s['label'] }}</span>
                                                </td>
                                            @endif
                                            <td class="text-center">
                                                @if ($payoutFilter == 0)
                                                    {{-- Monthly total — no single settlement to link to; show both payouts --}}
                                                    @php
                                                        $p1 = \App\Models\ChefzDelegateSettlement::where('monthly_period_id', $period->id)
                                                            ->where('delegate_id', $settlement->delegate_id)
                                                            ->where('payout_number', 1)->first();
                                                        $p2 = \App\Models\ChefzDelegateSettlement::where('monthly_period_id', $period->id)
                                                            ->where('delegate_id', $settlement->delegate_id)
                                                            ->where('payout_number', 2)->first();
                                                    @endphp
                                                    @if ($p1)
                                                        <a href="{{ route('dashboard.monthly.periods.chefz.settlement.show', [$period, $p1]) }}"
                                                           class="btn btn-sm btn-outline-primary">د١</a>
                                                    @endif
                                                    @if ($p2)
                                                        <a href="{{ route('dashboard.monthly.periods.chefz.settlement.show', [$period, $p2]) }}"
                                                           class="btn btn-sm btn-outline-info">د٢</a>
                                                    @endif
                                                @else
                                                    <a href="{{ route('dashboard.monthly.periods.chefz.settlement.show', [$period, $settlement]) }}"
                                                       class="btn btn-sm btn-outline-primary">
                                                        <i class="la la-eye"></i>
                                                        {{ $period->isOpen() && !($settlement->is_locked ?? false) ? 'إدارة' : 'عرض' }}
                                                    </a>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                                <tfoot>
                                    <tr class="bg-light font-weight-bold">
                                        <td colspan="2">الإجمالي ({{ $settlements->count() }} مندوب)</td>
                                        <td class="text-center">{{ number_format($totals['total_orders']) }}</td>
                                        <td class="text-left">{{ number_format($totals['gross_delivery_fees'], 2) }}</td>
                                        <td class="text-left text-danger">{{ number_format($totals['chefz_tax_amount'], 2) }}</td>
                                        <td class="text-left text-danger">{{ number_format($totals['company_share_amount'], 2) }}</td>
                                        <td class="text-left {{ $totals['net_salary'] < 0 ? 'text-danger' : 'text-success' }}">
                                            {{ number_format($totals['net_salary'], 2) }} ريال
                                        </td>
                                        <td colspan="{{ $payoutFilter != 0 ? 2 : 1 }}"></td>
                                    </tr>
                                </tfoot>
                            </table>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
