@extends('layouts.dashboard.app')
@section('title') سجل النشاط @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        <div class="content-header row">
            <div class="mb-2 content-header-left col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item"><a href="{{ route('dashboard.welcome') }}">الرئيسية</a></li>
                            <li class="breadcrumb-item active">سجل النشاط</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">
            <div class="card">
                <div class="card-content">
                    <div class="card-body">

                        {{-- Quick filters --}}
                        <form method="GET" action="{{ route('dashboard.support.activity.index') }}" class="mb-3">
                            <div class="row">
                                <div class="col-md-3">
                                    <select name="actor_type" class="form-control form-control-sm">
                                        <option value="">كل الأنواع</option>
                                        <option value="admin"    {{ $actorType === 'admin'    ? 'selected' : '' }}>مسؤول</option>
                                        <option value="delegate" {{ $actorType === 'delegate' ? 'selected' : '' }}>مندوب</option>
                                        <option value="system"   {{ $actorType === 'system'   ? 'selected' : '' }}>النظام</option>
                                    </select>
                                </div>
                                <div class="col-md-3">
                                    <input type="text" name="action" class="form-control form-control-sm"
                                           placeholder="الإجراء (ticket_resolved...)"
                                           value="{{ $action ?? '' }}">
                                </div>
                                <div class="col-md-2">
                                    <button type="submit" class="btn btn-primary btn-sm">
                                        <i class="la la-search"></i> بحث
                                    </button>
                                    <a href="{{ route('dashboard.support.activity.index') }}" class="btn btn-secondary btn-sm">
                                        <i class="la la-times"></i>
                                    </a>
                                </div>
                            </div>
                        </form>

                        @if($entries->isEmpty())
                            <div class="text-center py-4 text-muted">
                                <i class="la la-list font-large-2"></i>
                                <p>لا توجد إدخالات في السجل</p>
                            </div>
                        @else
                            <div class="table-responsive">
                                <table class="table table-sm table-hover">
                                    <thead class="thead-light">
                                        <tr>
                                            <th>الوقت</th>
                                            <th>الفاعل</th>
                                            <th>النوع</th>
                                            <th>الإجراء</th>
                                            <th>الوصف</th>
                                            <th>الموضوع</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach($entries as $entry)
                                            @php
                                                $actorClass = match($entry->actor_type?->value) {
                                                    'admin'    => 'badge-primary',
                                                    'delegate' => 'badge-info',
                                                    default    => 'badge-secondary',
                                                };
                                            @endphp
                                            <tr>
                                                <td class="small text-muted" title="{{ $entry->created_at?->format('Y-m-d H:i:s') }}">
                                                    {{ $entry->created_at?->diffForHumans() }}
                                                </td>
                                                <td class="small font-weight-bold">{{ $entry->actor_label }}</td>
                                                <td>
                                                    <span class="badge badge-sm {{ $actorClass }}">
                                                        {{ $entry->actor_type?->value }}
                                                    </span>
                                                </td>
                                                <td><code class="small">{{ $entry->action }}</code></td>
                                                <td class="small">{{ $entry->description }}</td>
                                                <td class="small text-muted">{{ $entry->subject_label }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            <div class="mt-2">{{ $entries->links() }}</div>
                        @endif

                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
