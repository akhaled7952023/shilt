@extends('layouts.dashboard.app')
@section('title') تذكرة {{ $ticket->ticket_number }} @endsection

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
                            <li class="breadcrumb-item active">{{ $ticket->ticket_number }}</li>
                        </ol>
                    </div>
                </div>
            </div>
            <div class="content-header-right col-md-4 col-12 text-right mb-2">
                <a href="{{ url()->previous() }}" class="btn btn-sm btn-outline-secondary">
                    <i class="la la-arrow-right"></i> رجوع
                </a>
            </div>
        </div>

        <div class="content-body">

            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif

            {{-- Header card --}}
            @include('dashboard.support.tickets._header_card', compact('ticket'))

            {{-- Action bar (priority + reopen) --}}
            @include('dashboard.support.tickets._action_bar', compact('ticket'))

            <div class="row">

                {{-- Main column: reply thread + unified composer --}}
                <div class="col-md-8">
                    @include('dashboard.support.tickets._reply_thread', ['ticket' => $ticket, 'unreadReplyIds' => $unreadReplyIds])
                    @if(! $ticket->isClosedPermanently())
                        @include('dashboard.support.tickets._reply_composer', compact('ticket'))
                    @else
                        <div class="card mb-2">
                            <div class="card-body py-3 text-center text-muted small">
                                <i class="la la-lock mr-1"></i>
                                هذه التذكرة مغلقة نهائياً — لا يمكن إضافة ردود جديدة.
                            </div>
                        </div>
                    @endif
                </div>

                {{-- Sidebar: financial panel + audit timeline --}}
                <div class="col-md-4">
                    @include('dashboard.support.tickets._financial_request_panel', compact('ticket'))
                    @include('dashboard.support.tickets._audit_timeline', compact('ticket'))
                </div>

            </div>

        </div>
    </div>
</div>
@endsection
