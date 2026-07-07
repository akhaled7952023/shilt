@extends('layouts.dashboard.app')
@section('title')
    Create Manager
@endsection
@section('content')
    <div class="app-content content">
        <div class="content-wrapper">
            <div class="content-header row">
                <div class="content-header-left col-md-6 col-12 mb-2 breadcrumb-new">
                    <div class="row breadcrumbs-top d-inline-block">
                        <div class="breadcrumb-wrapper col-12">
                            <ol class="breadcrumb">
                                <li class="breadcrumb-item"><a href="{{route('dashboard.welcome')}}">{{__('dashboard.dashboard')}}</a></li>
                                <li class="breadcrumb-item"><a href="{{route('dashboard.managers.index')}}">{{__('dashboard.managers')}}</a></li>
                                <li class="breadcrumb-item active">{{__('dashboard.add_newadmin')}}</li>
                            </ol>
                        </div>
                    </div>
                </div>

            </div>
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <a class="heading-elements-toggle"><i class="la la-ellipsis-v font-medium-3"></i></a>

                        <div class="heading-elements">
                            <ul class="list-inline mb-0">
                                <li><a data-action="collapse"><i class="ft-minus"></i></a></li>
                                <li><a data-action="reload"><i class="ft-rotate-cw"></i></a></li>
                                <li><a data-action="expand"><i class="ft-maximize"></i></a></li>
                                <li><a data-action="close"><i class="ft-x"></i></a></li>
                            </ul>
                        </div>
                    </div>
                    <div class="card-content collapse show">
                        <div class="card-body">
                            @include('dashboard.includes.validations-errors')
                            <form class="form" action="{{route('dashboard.managers.store')}}" method="POST">
                                @csrf
                                <div class="form-body">
                                    <h4 class="form-section"><i class="la la-new"></i>{{__('dashboard.add_newadmin')}}</h4>
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="userinput1">{{__('dashboard.admin_name')}}</label>
                                                <input type="text" id="userinput1" class="form-control border-primary"
                                                name="name"  placeholder="{{__('dashboard.name')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="userinput1">{{__('dashboard.admin_email')}}</label>
                                                <input type="text" id="userinput1" class="form-control border-primary"
                                                name="email"   placeholder="{{__('dashboard.email')}}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="userinput1">{{__('dashboard.password')}}</label>
                                                <input type="text" id="userinput1" class="form-control border-primary"
                                                name="password"  placeholder="{{__('dashboard.password')}}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="role_id">{{ __('dashboard.role_name_ar') }}</label>
                                                <select name="role_id" id="role_id" class="form-control border-primary">
                                                    <option disabled selected>{{ __('dashboard.select_role') }}</option>
                                                    @foreach($roles as $role)
                                                        <option value="{{ $role->id }}" {{ old('role_id') == $role->id ? 'selected' : '' }}>
                                                            {{ $role->name }}
                                                        </option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>

                                    </div>

                                <div class="form-actions right">
                                    <button type="button" class="btn btn-warning mr-1" onclick="window.history.back();">
                                        <i class="ft-x"></i> {{(__('dashboard.cancle'))}}
                                    </button>
                                    <button type="submit" class="btn btn-primary">
                                        <i class="la la-check-square-o"></i> {{(__('dashboard.save'))}}
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
