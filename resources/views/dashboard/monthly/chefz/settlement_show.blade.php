@extends('layouts.dashboard.app')

@section('title') تسوية شيفز — {{ $settlement->delegate?->name ?? '—' }} — {{ $period->getDisplayLabel() }} @endsection

@php
$isReadOnly = $settlement->is_locked || $period->isClosed();

$statusMeta = [
    'locked'           => ['label' => 'مقفل',          'class' => 'badge-dark'],
    'calculated'       => ['label' => 'محسوب',          'class' => 'badge-success'],
    'needs_manual_data'=> ['label' => 'يحتاج تعديلات', 'class' => 'badge-warning'],
    'incomplete'       => ['label' => 'غير مكتمل',     'class' => 'badge-secondary'],
][$computedStatus] ?? ['label' => $computedStatus, 'class' => 'badge-light'];

$payoutLabel = $settlement->payout_number == 1 ? 'الدفعة الأولى' : 'الدفعة الثانية';
// Mahmoud's approved formula: Driver Base = Gross − VAT; Company Share applied on Subtotal (after additions)
$driverBase = (float)$settlement->gross_delivery_fees - (float)$settlement->chefz_tax_amount;
$subtotal   = (float)$settlement->commission_total;  // subtotal before company share
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
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.monthly.periods.chefz.settlement.index', $period) }}">تسوية شيفز</a>
                            </li>
                            <li class="breadcrumb-item active">{{ $settlement->delegate?->name ?? '—' }}</li>
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

            {{-- Payout navigation --}}
            <div class="d-flex align-items-center mb-2" style="gap:.5rem;">
                <span class="badge badge-{{ $settlement->payout_number == 1 ? 'primary' : 'info' }} font-medium-1">
                    {{ $payoutLabel }}
                </span>
                @if ($otherPayout)
                    <a href="{{ route('dashboard.monthly.periods.chefz.settlement.show', [$period, $otherPayout]) }}"
                       class="btn btn-sm btn-outline-{{ $settlement->payout_number == 1 ? 'info' : 'primary' }}">
                        <i class="la la-exchange"></i>
                        عرض {{ $settlement->payout_number == 1 ? 'الدفعة الثانية' : 'الدفعة الأولى' }}
                    </a>
                @elseif ($isMonthComplete === false)
                    <span class="text-muted small">
                        <i class="la la-exclamation-triangle text-warning"></i>
                        {{ $settlement->payout_number == 1 ? 'الدفعة الثانية غير مستوردة بعد' : 'الدفعة الأولى غير مستوردة بعد' }}
                    </span>
                @endif
            </div>

            {{-- Delegate summary header --}}
            <div class="card mb-2" style="border-right: 4px solid #1cc88a;">
                <div class="card-body py-3">
                    <div class="row align-items-center">
                        <div class="col-md-3">
                            <small class="text-muted d-block">المندوب</small>
                            <strong class="font-medium-2">{{ $settlement->delegate?->name ?? '—' }}</strong>
                            <br><span class="text-monospace text-muted small">{{ $settlement->delegate?->national_id ?? '—' }}</span>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block">عدد الطلبات</small>
                            <strong class="font-medium-2">{{ number_format($settlement->total_orders) }}</strong>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block">رسوم التوصيل</small>
                            <strong class="font-medium-2 text-primary">{{ number_format($settlement->gross_delivery_fees, 2) }}</strong>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block">ضريبة + حصة الشركة</small>
                            <strong class="font-medium-2 text-danger">
                                {{ number_format((float)$settlement->chefz_tax_amount + (float)$settlement->company_share_amount, 2) }}
                            </strong>
                        </div>
                        <div class="col-md-2 text-center">
                            <small class="text-muted d-block">صافي الراتب</small>
                            <strong class="font-medium-2 {{ $settlement->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                {{ number_format($settlement->net_salary, 2) }}
                            </strong>
                        </div>
                        <div class="col-md-1 text-center">
                            <small class="text-muted d-block">الحالة</small>
                            <span class="badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span>
                        </div>
                    </div>
                </div>
            </div>

            {{-- 3-tab workspace --}}
            <div class="card">
                <div class="card-body p-0">

                    <ul class="nav nav-tabs border-bottom" id="settlementTabs" role="tablist">
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'imported' ? 'active' : '' }}"
                               data-toggle="tab" href="#tab-imported" role="tab">
                                <i class="la la-database"></i> بيانات الاستيراد
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'calculation' ? 'active' : '' }}"
                               data-toggle="tab" href="#tab-calculation" role="tab">
                                <i class="la la-calculator"></i> الحساب التفصيلي
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link {{ $activeTab === 'history' ? 'active' : '' }}"
                               data-toggle="tab" href="#tab-history" role="tab">
                                <i class="la la-history"></i> السجل
                            </a>
                        </li>
                    </ul>

                    <div class="tab-content p-3">

                        {{-- ════════════════════════════════════════════════════════════════ --}}
                        {{-- TAB 1: IMPORTED DATA --}}
                        {{-- ════════════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade {{ $activeTab === 'imported' || $activeTab === 'overview' ? 'show active' : '' }}"
                             id="tab-imported" role="tabpanel">

                            <div class="alert alert-light border mb-3">
                                <i class="la la-info-circle text-primary"></i>
                                <strong>{{ $payoutLabel }}</strong> — {{ $period->getDisplayLabel() }}
                                — البيانات من ملف الاستيراد. للقراءة فقط.
                            </div>

                            <div class="row">
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm mb-4">
                                        <thead class="thead-light">
                                            <tr><th colspan="2" class="text-center">هوية المندوب</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th class="bg-light" style="width:45%">اسم المندوب</th>
                                                <td>{{ $settlement->delegate?->name ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">رقم المعرف (Driver ID)</th>
                                                <td class="text-monospace">{{ $settlement->delegate?->national_id ?? '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                                <div class="col-md-6">
                                    <table class="table table-bordered table-sm mb-4">
                                        <thead class="thead-light">
                                            <tr><th colspan="2" class="text-center">معلومات الاستيراد</th></tr>
                                        </thead>
                                        <tbody>
                                            <tr>
                                                <th class="bg-light" style="width:45%">الدفعة</th>
                                                <td>
                                                    <span class="badge badge-{{ $settlement->payout_number == 1 ? 'primary' : 'info' }}">
                                                        {{ $payoutLabel }}
                                                    </span>
                                                </td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">الفترة</th>
                                                <td>{{ $period->getDisplayLabel() }}</td>
                                            </tr>
                                            <tr>
                                                <th class="bg-light">المنصة</th>
                                                <td>{{ $period->platform?->name ?? '—' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <table class="table table-bordered table-sm">
                                <thead class="thead-light">
                                    <tr>
                                        <th colspan="2" class="text-center bg-primary text-white">البيانات المجمّعة من المنصة</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <tr>
                                        <th class="bg-light" style="width:45%">إجمالي الطلبات</th>
                                        <td><strong>{{ number_format($settlement->total_orders) }}</strong> طلب</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">رسوم التوصيل (شاملة الضريبة)</th>
                                        <td class="text-success font-weight-bold">
                                            {{ number_format($settlement->gross_delivery_fees, 2) }} ريال
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">خصومات المنصة</th>
                                        <td class="text-danger">
                                            {{ number_format($settlement->platform_deductions, 2) }} ريال
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">تعويضات المنصة</th>
                                        <td class="text-info">
                                            {{ number_format($settlement->platform_compensations, 2) }} ريال
                                        </td>
                                    </tr>
                                    @if ((float)$settlement->bonus_total != 0)
                                        <tr>
                                            <th class="bg-light">
                                                مجموع المكافآت
                                                <small class="text-muted">(قد يشمل مكافآت ملغاة)</small>
                                            </th>
                                            <td class="{{ (float)$settlement->bonus_total >= 0 ? 'text-success' : 'text-warning' }}">
                                                {{ number_format($settlement->bonus_total, 2) }} ريال
                                            </td>
                                        </tr>
                                    @endif
                                    @if ((float)$settlement->positive_bonus > 0)
                                        <tr>
                                            <th class="bg-light">
                                                المكافآت الصالحة فقط
                                                <small class="text-muted">(تُضاف للراتب)</small>
                                            </th>
                                            <td class="text-success">
                                                {{ number_format($settlement->positive_bonus, 2) }} ريال
                                            </td>
                                        </tr>
                                    @endif
                                </tbody>
                            </table>

                        </div>

                        {{-- ════════════════════════════════════════════════════════════════ --}}
                        {{-- TAB 2: CALCULATION --}}
                        {{-- ════════════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade {{ $activeTab === 'calculation' ? 'show active' : '' }}"
                             id="tab-calculation" role="tabpanel">

                            <div class="row justify-content-center">
                                <div class="col-md-8">
                                    <table class="table table-bordered mb-0">
                                        <thead>
                                            <tr class="bg-primary text-white">
                                                <th colspan="3" class="text-center">
                                                    تفاصيل الحساب — {{ $settlement->delegate?->name ?? '—' }}
                                                    ({{ $payoutLabel }})
                                                </th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            {{-- Gross --}}
                                            <tr class="table-active">
                                                <td colspan="3" class="font-weight-bold text-success">
                                                    <i class="la la-arrow-up"></i> الإيرادات الإجمالية
                                                </td>
                                            </tr>
                                            <tr>
                                                <td style="width:55%">رسوم التوصيل (شاملة الضريبة)</td>
                                                <td class="text-center text-success">+</td>
                                                <td class="text-left font-weight-bold text-success">
                                                    {{ number_format($settlement->gross_delivery_fees, 2) }} ريال
                                                </td>
                                            </tr>

                                            {{-- VAT --}}
                                            <tr class="table-active">
                                                <td colspan="3" class="font-weight-bold text-danger">
                                                    <i class="la la-arrow-down"></i> تطبيق الضريبة
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    ضريبة القيمة المضافة
                                                    <small class="text-muted">
                                                        ({{ number_format((float)$settlement->chefz_tax_rate * 100, 0) }}% من رسوم التوصيل)
                                                    </small>
                                                </td>
                                                <td class="text-center text-danger">−</td>
                                                <td class="text-left text-danger">
                                                    {{ number_format($settlement->chefz_tax_amount, 2) }} ريال
                                                </td>
                                            </tr>
                                            <tr class="font-weight-bold" style="background:#f0f8ff;">
                                                <td>قاعدة السائق (رسوم التوصيل − الضريبة)</td>
                                                <td class="text-center">=</td>
                                                <td class="text-left text-primary">
                                                    {{ number_format($driverBase, 2) }} ريال
                                                </td>
                                            </tr>

                                            {{-- Additions --}}
                                            <tr class="table-active">
                                                <td colspan="3" class="font-weight-bold text-success">
                                                    <i class="la la-plus"></i> الإضافات
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>تعويضات المنصة</td>
                                                <td class="text-center text-success">+</td>
                                                <td class="text-left text-success">
                                                    {{ number_format($settlement->platform_compensations, 2) }} ريال
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>المكافآت الصالحة (positive bonus)</td>
                                                <td class="text-center text-success">+</td>
                                                <td class="text-left text-success">
                                                    {{ number_format($settlement->positive_bonus, 2) }} ريال
                                                </td>
                                            </tr>
                                            <tr class="font-weight-bold" style="border-top:2px solid #dee2e6; background:#f0f8ff;">
                                                <td>الإجمالي قبل حصة الشركة</td>
                                                <td class="text-center">=</td>
                                                <td class="text-left text-primary font-weight-bold">
                                                    {{ number_format($subtotal, 2) }} ريال
                                                </td>
                                            </tr>

                                            {{-- Company share --}}
                                            <tr class="table-active">
                                                <td colspan="3" class="font-weight-bold text-danger">
                                                    <i class="la la-arrow-down"></i> حصة الشركة
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>
                                                    حصة الشركة
                                                    <small class="text-muted">
                                                        ({{ number_format((float)$settlement->company_share_rate * 100, 0) }}% من الإجمالي قبل حصة الشركة)
                                                    </small>
                                                </td>
                                                <td class="text-center text-danger">−</td>
                                                <td class="text-left text-danger">
                                                    {{ number_format($settlement->company_share_amount, 2) }} ريال
                                                </td>
                                            </tr>

                                            {{-- Deductions --}}
                                            <tr class="table-active">
                                                <td colspan="3" class="font-weight-bold text-danger">
                                                    <i class="la la-minus"></i> الخصومات
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>خصومات المنصة</td>
                                                <td class="text-center text-danger">−</td>
                                                <td class="text-left text-danger">
                                                    {{ number_format($settlement->platform_deductions, 2) }} ريال
                                                </td>
                                            </tr>
                                            <tr>
                                                <td>الخصومات اليدوية</td>
                                                <td class="text-center text-danger">−</td>
                                                <td class="text-left text-danger">
                                                    {{ number_format($settlement->deductions_total - (float)$settlement->platform_deductions, 2) }} ريال
                                                </td>
                                            </tr>

                                            {{-- Net --}}
                                            <tr style="border-top:3px solid #dee2e6; background:#f8f9fc;">
                                                <td class="font-weight-bold font-medium-2">صافي الراتب النهائي</td>
                                                <td class="text-center font-weight-bold">=</td>
                                                <td class="text-left font-weight-bold font-medium-3
                                                    {{ $settlement->net_salary < 0 ? 'text-danger' : 'text-success' }}">
                                                    {{ number_format($settlement->net_salary, 2) }} ريال
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>

                                    @if ((float)$settlement->bonus_total < 0)
                                        <div class="alert alert-info border mt-3 small">
                                            <i class="la la-info-circle"></i>
                                            <strong>مكافأة ملغاة:</strong>
                                            بعض المكافآت المستوردة ذات قيمة سالبة (مكافآت ملغاة).
                                            إجمالي المكافآت = {{ number_format($settlement->bonus_total, 2) }} ريال.
                                            فقط المكافآت الموجبة ({{ number_format($settlement->positive_bonus, 2) }} ريال) تُضاف للراتب.
                                            المكافآت الملغاة لا تُحسم من الراتب.
                                        </div>
                                    @endif

                                    <div class="alert alert-light border mt-3 small text-muted">
                                        <strong>صيغة الحساب (شيفز):</strong><br>
                                        قاعدة السائق = الرسوم الإجمالية − الضريبة − حصة الشركة (من الوعاء بعد الضريبة)<br>
                                        صافي الراتب = قاعدة السائق + تعويضات + مكافآت صالحة − خصومات<br>
                                        <em>النسب من إعدادات النظام. حصة الشركة تُحسب من (الرسوم − الضريبة) وليس من الرسوم الإجمالية مباشرة.</em>
                                    </div>
                                </div>
                            </div>

                        </div>

                        {{-- ════════════════════════════════════════════════════════════════ --}}
                        {{-- TAB 3: AUDIT --}}
                        {{-- ════════════════════════════════════════════════════════════════ --}}
                        <div class="tab-pane fade {{ $activeTab === 'history' ? 'show active' : '' }}"
                             id="tab-history" role="tabpanel">

                            <table class="table table-bordered table-sm" style="max-width:600px;">
                                <tbody>
                                    <tr>
                                        <th class="bg-light" style="width:40%">تاريخ الإنشاء</th>
                                        <td>{{ $settlement->created_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">أنشئ بواسطة</th>
                                        <td>{{ $settlement->createdBy?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">آخر تعديل</th>
                                        <td>{{ $settlement->updated_at?->format('Y-m-d H:i') ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">عدّل بواسطة</th>
                                        <td>{{ $settlement->updatedBy?->name ?? '—' }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">الدفعة</th>
                                        <td>
                                            <span class="badge badge-{{ $settlement->payout_number == 1 ? 'primary' : 'info' }}">
                                                {{ $payoutLabel }}
                                            </span>
                                        </td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">حالة التسوية</th>
                                        <td><span class="badge {{ $statusMeta['class'] }}">{{ $statusMeta['label'] }}</span></td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">الفترة الشهرية</th>
                                        <td>{{ $period->getDisplayLabel() }}</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">نسبة الضريبة المطبّقة</th>
                                        <td>{{ number_format((float)$settlement->chefz_tax_rate * 100, 0) }}%</td>
                                    </tr>
                                    <tr>
                                        <th class="bg-light">نسبة حصة الشركة المطبّقة</th>
                                        <td>{{ number_format((float)$settlement->company_share_rate * 100, 0) }}%</td>
                                    </tr>
                                </tbody>
                            </table>

                        </div>

                    </div>{{-- end tab-content --}}
                </div>{{-- end card-body --}}
            </div>{{-- end card --}}

            <div class="mt-3">
                <a href="{{ route('dashboard.monthly.periods.chefz.settlement.index', [$period, 'payout' => $settlement->payout_number]) }}"
                   class="btn btn-outline-secondary">
                    <i class="la la-arrow-right"></i> العودة لقائمة التسويات
                </a>
            </div>

        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    const params = new URLSearchParams(window.location.search);
    const tabParam = params.get('tab');
    if (tabParam) {
        const tabLink = document.querySelector(`[href="#tab-${tabParam}"]`);
        if (tabLink) tabLink.click();
    }
});
</script>
@endsection
