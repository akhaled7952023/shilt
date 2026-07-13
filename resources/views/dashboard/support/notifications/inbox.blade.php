@extends('layouts.dashboard.app')
@section('title') صندوق الإشعارات @endsection

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
                            <li class="breadcrumb-item active">الإشعارات</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            @php
                $tabs = [
                    'all'         => 'الكل',
                    'settlements' => 'التسويات',
                    'financial'   => 'الطلبات المالية',
                    'support'     => 'مركز الدعم',
                    'documents'   => 'المستندات',
                    'leaves'      => 'الإجازات',
                ];
            @endphp

            {{-- Filter tabs — outside the card, above it, matching project nav-tabs pattern --}}
            <div style="overflow-x:auto; -webkit-overflow-scrolling:touch;">
                <ul class="nav nav-tabs mb-2" style="flex-wrap:nowrap; min-width:max-content;">
                    @foreach($tabs as $key => $label)
                        <li class="nav-item" style="white-space:nowrap;">
                            <a class="nav-link {{ $activeFilter === $key ? 'active' : '' }}"
                               href="{{ route('dashboard.support.notifications.inbox', ['filter' => $key]) }}">
                                {{ $label }}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>

            {{-- Notification card --}}
            <div class="card">
                <div class="card-header d-flex align-items-center justify-content-between" style="flex-wrap:wrap; gap:10px;">
                    <h4 class="card-title mb-0">
                        <i class="la la-bell mr-1"></i>
                        صندوق الإشعارات
                        @if($notifications->total() > 0)
                            <span class="badge badge-secondary badge-pill ml-1">{{ $notifications->total() }}</span>
                        @endif
                    </h4>

                    @if($notifications->isNotEmpty())
                        <form method="POST" action="{{ route('dashboard.support.notifications.read-all') }}">
                            @csrf
                            <button type="submit" class="btn btn-sm btn-outline-secondary">
                                <i class="la la-check-double"></i> تعليم الكل كمقروء
                            </button>
                        </form>
                    @endif
                </div>

                <div class="card-content">
                    <div class="card-body p-0">

                        @if($notifications->isEmpty())
                            <div class="text-center py-5 text-muted">
                                <i class="la la-bell-slash" style="font-size:52px; display:block; margin-bottom:12px; opacity:.35;"></i>
                                <p class="mb-0">لا توجد إشعارات</p>
                            </div>
                        @else
                            <ul class="list-group list-group-flush mb-0">
                                @foreach($notifications as $n)
                                    @php
                                        $isUnread  = $n->isUnread();
                                        $actionUrl = $n->action_url;
                                    @endphp
                                    <li class="list-group-item {{ $isUnread ? 'bg-blue-grey bg-lighten-5' : '' }}"
                                        style="{{ $isUnread ? 'border-right:3px solid #2563eb;' : 'border-right:3px solid transparent;' }} padding:14px 20px;">
                                        <div class="d-flex align-items-start">

                                            {{-- Icon --}}
                                            <div class="mr-3 mt-1" style="flex-shrink:0;">
                                                <span style="display:inline-flex; align-items:center; justify-content:center;
                                                             width:36px; height:36px; border-radius:9px;
                                                             background:{{ $n->typeBg() }}; color:{{ $n->typeColor() }};
                                                             font-size:18px;">
                                                    <i class="la {{ $n->typeIcon() }}"></i>
                                                </span>
                                            </div>

                                            {{-- Body --}}
                                            <div style="flex:1; min-width:0;">
                                                <div style="font-size:14px; font-weight:{{ $isUnread ? '700' : '500' }}; margin-bottom:3px;">
                                                    {{ $n->title }}
                                                </div>
                                                @if($n->body)
                                                    <div style="font-size:13px; color:#64748b; margin-bottom:4px;">
                                                        {{ $n->body }}
                                                    </div>
                                                @endif
                                                <div style="font-size:12px; color:#94a3b8;">
                                                    <i class="la la-clock-o mr-1"></i>
                                                    {{ $n->created_at->diffForHumans() }}
                                                    @if($actionUrl)
                                                        &nbsp;&middot;&nbsp;
                                                        <a href="{{ $actionUrl }}" style="color:#2563eb; font-weight:600;">
                                                            <i class="la la-external-link"></i> عرض
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>

                                            {{-- Unread dot --}}
                                            @if($isUnread)
                                                <div class="mr-2 mt-1" style="flex-shrink:0;">
                                                    <span style="display:inline-block; width:8px; height:8px;
                                                                 border-radius:50%; background:#2563eb;"></span>
                                                </div>
                                            @endif

                                        </div>
                                    </li>
                                @endforeach
                            </ul>
                        @endif

                    </div>
                </div>

                @if($notifications->hasPages())
                    <div class="card-footer">
                        {{ $notifications->links() }}
                    </div>
                @endif
            </div>

        </div>
    </div>
</div>
@endsection
