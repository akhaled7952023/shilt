@extends('layouts.dashboard.app')
@section('content')
<div class="app-content content">
    <div class="content-wrapper">
        <p>{{ Route::currentRouteName() }} — قيد الإنشاء</p>
    </div>
</div>
@endsection
