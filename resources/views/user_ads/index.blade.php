@extends('user_ads.layout')

@section('title')
    User Ads
@endsection

@section('user_ads-content')
    {!! breadcrumbs(['User Ads' => 'user_ads']) !!}

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('user_ads/new') }}">
            <i class="fas fa-plus"></i> Create New User Ad
        </a>
        @if (Auth::check() && Auth::user()->hasPower('manage_user_ads'))
            <a class="btn btn-primary" href="{{ url('admin/user_ads') }}">
                <i class="fas fa-crown"></i> Ad Moderation
            </a>
        @endif
    </div>

    <h1>User Ads</h1>

    <ul class="list-group">
        @if (count($user_ads))
            {!! $user_ads->render() !!}
            @foreach ($user_ads as $ad)
                @include('user_ads._user_ads', ['user_ads' => $ad, 'page' => false])
            @endforeach
            {!! $user_ads->render() !!}
        @else
            <div>No ads have been posted yet.</div>
        @endif
    </ul>
@endsection
