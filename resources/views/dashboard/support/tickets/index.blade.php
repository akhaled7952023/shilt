@extends('layouts.dashboard.app')
@section('title') قائمة تذاكر الدعم @endsection

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
                            <li class="breadcrumb-item active">مركز الدعم — التذاكر</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            {{-- Status Tabs --}}
            <ul class="nav nav-tabs mb-2">
                <li class="nav-item">
                    <a class="nav-link {{ empty($filters['status']) ? 'active' : '' }}"
                       href="{{ route('dashboard.support.tickets.index') }}">
                        نشطة
                        @if($statusCounts->except([\App\Enums\TicketStatus::Closed->value])->sum() > 0)
                            <span class="badge badge-primary badge-pill">{{ $statusCounts->except([\App\Enums\TicketStatus::Closed->value])->sum() }}</span>
                        @endif
                    </a>
                </li>
                @foreach(\App\Enums\TicketStatus::cases() as $s)
                    <li class="nav-item">
                        <a class="nav-link {{ ($filters['status'] ?? '') === $s->value ? 'active' : '' }}"
                           href="{{ route('dashboard.support.tickets.index', array_merge($filters, ['status' => $s->value])) }}">
                            {{ $s->label() }}
                            @if($statusCounts->get($s->value))
                                <span class="badge {{ $s->badgeClass() }} badge-pill">{{ $statusCounts->get($s->value) }}</span>
                            @endif
                        </a>
                    </li>
                @endforeach
            </ul>

            <div class="card">
                <div class="card-content">
                    <div class="card-body">

                        @include('dashboard.support.tickets._filters')

                        @if($tickets->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="la la-ticket font-large-2"></i>
                                <p class="mt-1">لا توجد تذاكر تطابق هذه الفلاتر</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>رقم التذكرة</th>
                                            <th>المصدر</th>
                                            <th>التصنيف</th>
                                            <th>الأولوية</th>
                                            <th>المندوب</th>
                                            <th>الموضوع</th>
                                            <th>الحالة</th>
                                            <th>SLA</th>
                                            <th>آخر نشاط</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($tickets as $ticket)
                                            @include('dashboard.support.tickets._ticket_row', compact('ticket'))
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>

                            <div class="mt-2">
                                {{ $tickets->links() }}
                            </div>
                        @endif

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
