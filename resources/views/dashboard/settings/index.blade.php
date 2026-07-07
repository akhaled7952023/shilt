@extends('layouts.dashboard.app')

@section('title') إعدادات النظام @endsection

@section('content')
<div class="app-content content">
    <div class="content-wrapper">

        {{-- Breadcrumb --}}
        <div class="content-header row">
            <div class="mb-2 content-header-left col-md-6 col-12 breadcrumb-new">
                <div class="row breadcrumbs-top d-inline-block">
                    <div class="breadcrumb-wrapper col-12">
                        <ol class="breadcrumb">
                            <li class="breadcrumb-item">
                                <a href="{{ route('dashboard.welcome') }}">الرئيسية</a>
                            </li>
                            <li class="breadcrumb-item active">إعدادات النظام</li>
                        </ol>
                    </div>
                </div>
            </div>
        </div>

        <div class="content-body">

            {{-- Flash messages --}}
            @if (session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="la la-check-circle"></i> {{ session('success') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            @if (session('error'))
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="la la-exclamation-triangle"></i> {{ session('error') }}
                    <button type="button" class="close" data-dismiss="alert">
                        <span>&times;</span>
                    </button>
                </div>
            @endif

            <div class="card">
                <div class="card-header">
                    <h4 class="card-title">
                        <i class="la la-cog"></i> إعدادات النظام
                    </h4>
                    <a class="heading-elements-toggle">
                        <i class="la la-ellipsis-v font-medium-3"></i>
                    </a>
                    <div class="heading-elements">
                        <ul class="mb-0 list-inline">
                            <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                            <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                            <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                        </ul>
                    </div>
                </div>

                <div class="card-content">
                    <div class="card-body p-0">

                        @php
                            $groupLabels = [
                                'financial'     => ['label' => 'المالية',           'icon' => 'la-money'],
                                'import'        => ['label' => 'الاستيراد',         'icon' => 'la-upload'],
                                'company'       => ['label' => 'الشركة',            'icon' => 'la-building'],
                                'portal'        => ['label' => 'بوابة المندوب',     'icon' => 'la-user-circle'],
                                'notifications' => ['label' => 'الإشعارات',         'icon' => 'la-bell'],
                                'general'       => ['label' => 'عام',               'icon' => 'la-cog'],
                            ];

                            $activeGroup = request('group', $grouped->keys()->first());
                        @endphp

                        {{-- Tab navigation --}}
                        <ul class="nav nav-tabs" id="settingsTabs" role="tablist">
                            @foreach ($grouped as $group => $items)
                                @php
                                    $meta  = $groupLabels[$group] ?? ['label' => $group, 'icon' => 'la-cog'];
                                    $isActive = $activeGroup === $group;
                                @endphp
                                <li class="nav-item">
                                    <a class="nav-link {{ $isActive ? 'active' : '' }}"
                                       id="tab-{{ $group }}"
                                       data-toggle="tab"
                                       href="#pane-{{ $group }}"
                                       role="tab">
                                        <i class="la {{ $meta['icon'] }}"></i>
                                        {{ $meta['label'] }}
                                        <span class="badge badge-secondary badge-pill ml-1">
                                            {{ $items->count() }}
                                        </span>
                                    </a>
                                </li>
                            @endforeach
                        </ul>

                        {{-- Tab content --}}
                        <div class="tab-content p-3" id="settingsTabContent">
                            @foreach ($grouped as $group => $items)
                                @php $isActive = $activeGroup === $group; @endphp
                                <div class="tab-pane fade {{ $isActive ? 'show active' : '' }}"
                                     id="pane-{{ $group }}"
                                     role="tabpanel">

                                    <div class="table-responsive">
                                        <table class="table table-hover table-sm mb-0">
                                            <thead class="thead-light">
                                                <tr>
                                                    <th style="width:25%">المفتاح</th>
                                                    <th style="width:15%">النوع</th>
                                                    <th style="width:30%">القيمة الحالية</th>
                                                    <th style="width:20%">الوصف</th>
                                                    <th style="width:10%" class="text-center">إجراء</th>
                                                </tr>
                                            </thead>
                                            <tbody>
                                                @foreach ($items as $setting)
                                                    <tr>
                                                        <td>
                                                            <code class="text-dark">{{ $setting->key }}</code>
                                                        </td>
                                                        <td>
                                                            @php
                                                                $typeLabels = [
                                                                    'decimal' => ['text' => 'عشري',        'class' => 'badge-info'],
                                                                    'integer' => ['text' => 'رقم صحيح',    'class' => 'badge-primary'],
                                                                    'boolean' => ['text' => 'منطقي',        'class' => 'badge-warning'],
                                                                    'json'    => ['text' => 'JSON',         'class' => 'badge-dark'],
                                                                    'string'  => ['text' => 'نص',           'class' => 'badge-secondary'],
                                                                ];
                                                                $typeMeta = $typeLabels[$setting->type?->value] ?? ['text' => $setting->type?->value, 'class' => 'badge-secondary'];
                                                            @endphp
                                                            <span class="badge {{ $typeMeta['class'] }}">
                                                                {{ $typeMeta['text'] }}
                                                            </span>
                                                        </td>
                                                        <td>
                                                            @if ($setting->key === 'company_logo_path')
                                                                @if ($setting->value)
                                                                    <img src="{{ Storage::url($setting->value) }}"
                                                                         alt="شعار الشركة"
                                                                         style="height:32px;object-fit:contain;">
                                                                @else
                                                                    <span class="text-muted small">لم يُرفع بعد</span>
                                                                @endif
                                                            @elseif ($setting->type?->value === 'boolean')
                                                                @if (filter_var($setting->value, FILTER_VALIDATE_BOOLEAN))
                                                                    <span class="badge badge-success">نعم</span>
                                                                @else
                                                                    <span class="badge badge-danger">لا</span>
                                                                @endif
                                                            @else
                                                                <span class="text-dark">
                                                                    {{ mb_strimwidth($setting->value ?? '—', 0, 60, '...') }}
                                                                </span>
                                                            @endif
                                                        </td>
                                                        <td class="text-muted small">
                                                            {{ $setting->description ?? '—' }}
                                                        </td>
                                                        <td class="text-center">
                                                            @can('update', App\Models\SystemSetting::class)
                                                                <a href="{{ route('dashboard.settings.edit', $setting->key) }}"
                                                                   class="btn btn-sm btn-outline-primary"
                                                                   title="تعديل">
                                                                    <i class="la la-edit"></i>
                                                                </a>
                                                            @endcan
                                                        </td>
                                                    </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>

                                </div>
                            @endforeach
                        </div>

                    </div>
                </div>
            </div>

        </div>
    </div>
</div>
@endsection
