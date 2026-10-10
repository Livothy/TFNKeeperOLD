@extends('layouts.app')

@section('title')
    Home
@endsection

@if (Settings::get('site_weather') || Settings::get('site_season'))
    @section('sidebar')
        @include('widgets._current_weather')
    @endsection
@endif

@section('content')
    @if (Auth::check())
        @include('pages._dashboard')
    @else
        @include('pages._logged_out')
    @endif
@endsection
