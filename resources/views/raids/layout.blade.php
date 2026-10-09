@extends('layouts.app')

@section('title')
    {{ ucfirst(__('raids.raids')) }}{!! View::hasSection('raids-title') ? ' :: ' . trim(View::getSection('raids-title')) : '' !!}
@endsection

@section('sidebar')
    @include('raids._sidebar')
@endsection

@section('content')
    @yield('raids-content')
@endsection

@section('scripts')
    @parent
@endsection
