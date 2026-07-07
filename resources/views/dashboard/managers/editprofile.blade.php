@extends('layouts.dashboard.app')
@section('title')
   Edit Profile
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
                                <li class="breadcrumb-item active">{{__('dashboard.edit_role')}}</li>
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
                            <form class="form" action="{{route('dashboard.managers.updateprofile',$manager->id)}}" method="POST">

                                @csrf
                                @method('PATCH')
                                <div class="form-body">
                                    <h4 class="form-section"><i class="la la-new"></i>{{__('dashboard.edit_role')}}</h4>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="userinput1">{{__('dashboard.admin_name')}}</label>
                                                <input type="text" id="userinput1" class="form-control border-primary"
                                                name="name"  value="{{$manager->name}}">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="userinput1">{{__('dashboard.admin_email')}}</label>
                                                <input type="text" id="userinput1" class="form-control border-primary"
                                                name="email"   value="{{$manager->email}}">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row">
                                        <div class="col-md-6">
                                            <button type="button"  class="btn btn-primary" id="toggle-password-btn" onclick="togglePassword()">
                                                {{ __('dashboard.change_password') }}
                                            </button>

                                            <div class="form-group" id="password-container" style="padding-top: 20px">
                                                <label for="userinput5"></label>
                                                <input type="text" hidden id="userinput5" class="form-control border-primary"
                                                       name="password" placeholder="{{ __('dashboard.password') }}">
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

<script>

function togglePassword() {
    const passwordField = document.getElementById('userinput5');
    const toggleButton = document.getElementById('toggle-password-btn');

    if (passwordField.hasAttribute('hidden')) {
        passwordField.removeAttribute('hidden');
        toggleButton.textContent = "{{ __('dashboard.cancel_password_change') }}";
    } else {
        passwordField.setAttribute('hidden', '');
        toggleButton.textContent = "{{ __('dashboard.change_password') }}";
        passwordField.value = '';
    }
}


</script>

