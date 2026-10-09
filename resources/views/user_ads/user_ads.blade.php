@extends('user_ads.layout')

@section('user_ads-content')
    {!! breadcrumbs(['User Ads' => 'user_ads']) !!}
    @include('user_ads._user_ads', ['user_ads' => $user_ads, 'page' => true])
@endsection
