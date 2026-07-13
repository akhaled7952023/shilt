@extends('layouts.dashboard.app')
@section('title') لوحة الطلبات المالية @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- Breadcrumb --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-8 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.support.tickets.index') }}">التذاكر</a></li>
                            <li class="breadcrumb-item active">الطلبات المالية</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            {{-- Status Tabs --}}
            <ul class="nav nav-tabs mb-3" id="frTabs">
                @php
                    $tabs = [
                        'pending'  => ['label' => 'بانتظار المراجعة', 'badge' => 'badge-secondary'],
                        'approved' => ['label' => 'مُوافق عليها',      'badge' => 'badge-success'],
                        'rejected' => ['label' => 'مرفوضة',            'badge' => 'badge-danger'],
                        'all'      => ['label' => 'الكل',               'badge' => 'badge-dark'],
                    ];
                    $pendingCount = ($statusCounts[\App\Enums\FinancialRequestStatus::Pending->value] ?? 0)
                                 + ($statusCounts[\App\Enums\FinancialRequestStatus::NeedsInfo->value] ?? 0);
                    $approvedCount = $statusCounts[\App\Enums\FinancialRequestStatus::Approved->value] ?? 0;
                    $rejectedCount = $statusCounts[\App\Enums\FinancialRequestStatus::Rejected->value] ?? 0;
                    $allCount = $pendingCount + $approvedCount + $rejectedCount;
                    $tabCounts = [
                        'pending'  => $pendingCount,
                        'approved' => $approvedCount,
                        'rejected' => $rejectedCount,
                        'all'      => $allCount,
                    ];
                @endphp
                @foreach($tabs as $tabKey => $tabDef)
                    <li class="nav-item">
                        <a class="nav-link {{ $tab === $tabKey ? 'active' : '' }}"
                           href="{{ route('dashboard.support.financial-requests.index', ['tab' => $tabKey]) }}">
                            {{ $tabDef['label'] }}
                            <span class="badge {{ $tabDef['badge'] }} ml-1">{{ $tabCounts[$tabKey] }}</span>
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="card">
                <div class="card-body p-0">
                    @if($financialRequests->isEmpty())
                        <div class="text-center py-5 text-muted">
                            <i class="la la-inbox" style="font-size:48px; display:block; margin-bottom:8px;"></i>
                            لا توجد طلبات مالية في هذا التبويب.
                        </div>
                    @else
                        <div class="table-responsive">
                            <table class="table table-hover mb-0">
                                <thead class="thead-light">
                                    <tr>
                                        <th>#</th>
                                        <th>رقم التذكرة</th>
                                        <th>المندوب</th>
                                        <th>نوع الطلب</th>
                                        <th>المبلغ المطلوب</th>
                                        <th>الحالة</th>
                                        <th>تاريخ الطلب</th>
                                        <th>المراجع</th>
                                        <th>الإجراء</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($financialRequests as $fr)
                                    @php
                                        $rowBadge = match($fr->status) {
                                            \App\Enums\FinancialRequestStatus::Approved  => 'badge-success',
                                            \App\Enums\FinancialRequestStatus::Rejected  => 'badge-danger',
                                            \App\Enums\FinancialRequestStatus::NeedsInfo => 'badge-warning',
                                            default                                      => 'badge-secondary',
                                        };
                                    @endphp
                                    <tr>
                                        <td class="small text-muted">{{ $fr->id }}</td>
                                        <td>
                                            @if($fr->ticket)
                                                <a href="{{ route('dashboard.support.tickets.show', $fr->ticket) }}"
                                                   class="font-weight-bold small">
                                                    {{ $fr->ticket->ticket_number }}
                                                </a>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td class="small">{{ $fr->delegate?->name ?? '—' }}</td>
                                        <td class="small">{{ $fr->request_category->label() }}</td>
                                        <td class="small font-weight-bold font-variant-numeric">
                                            {{ number_format($fr->requested_amount, 2) }} ريال
                                        </td>
                                        <td>
                                            <span class="badge {{ $rowBadge }}">{{ $fr->status->label() }}</span>
                                        </td>
                                        <td class="small text-muted" title="{{ $fr->created_at?->format('Y-m-d H:i') }}">
                                            {{ $fr->created_at?->diffForHumans() }}
                                        </td>
                                        <td class="small">
                                            @if($fr->reviewer)
                                                {{ $fr->reviewer->name }}<br>
                                                <span class="text-muted">{{ $fr->reviewed_at?->format('Y-m-d') }}</span>
                                            @else
                                                <span class="text-muted">—</span>
                                            @endif
                                        </td>
                                        <td>
                                            @if($fr->ticket)
                                                <a href="{{ route('dashboard.support.tickets.show', $fr->ticket) }}"
                                                   class="btn btn-xs btn-outline-primary">
                                                    <i class="la la-eye"></i> التذكرة
                                                </a>
                                            @endif
                                        </td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>

                        <div class="px-3 py-2">
                            {{ $financialRequests->appends(request()->query())->links() }}
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
