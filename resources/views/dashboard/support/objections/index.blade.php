@extends('layouts.dashboard.app')
@section('title') اعتراضات التسوية @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.support.tickets.index') }}">التذاكر</a></li>
                            <li class="breadcrumb-item active">اعتراضات التسوية</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="alert alert-info small mb-2">
                <i class="la la-info-circle"></i>
                هذه القائمة تعرض فقط تذاكر من نوع «اعتراض على التسوية».
            </div>

            {{-- Reuse the shared ticket queue layout --}}
            <div class="card">
                <div class="card-content">
                    <div class="card-body">

                        @include('dashboard.support.tickets._filters')

                        @if($tickets->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="la la-check-circle font-large-2 text-success"></i>
                                <p class="mt-1">لا توجد اعتراضات مفتوحة</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover table-striped">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>رقم التذكرة</th>
                                            <th>المصدر</th>
                                            <th>الأولوية</th>
                                            <th>المندوب</th>
                                            <th>الموضوع</th>
                                            <th>الحالة</th>
                                            <th>SLA</th>
                                            <th>مُعيَّن إلى</th>
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
                            <div class="mt-2">{{ $tickets->links() }}</div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
