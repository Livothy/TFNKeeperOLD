@extends('user_ads.layout')

@section('title')
    Create User Ad
@endsection

@section('user_ads-content')
    {!! breadcrumbs(['User Ads' => 'user_ads', 'Create Ad' => 'user_ads/new']) !!}

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('user_ads') }}">
            <i class="fas fa-plus"></i> Back to User Ads Index
        </a>
    </div>

    <h1>Create User Ad</h1>

    <div class="alert alert-warning">
        Please note: User ads cannot be edited once they are posted. You are able to delete your own ad.
    </div>
    <div class="alert alert-info">
        <h2>User Ad Rules</h2>
        <ol>
            <li>You only have up to 250 characters (including spaces) to post an ad.</li>
            <li>You cannot use any hyperlinks or any other tags. It will just clean your ad and make it without any markdown.</li>
    </div>

    {!! Form::open(['url' => 'user_ads/create', 'method' => 'POST', 'files' => true]) !!}

    <div class="form-group">
        {!! Form::label('text', 'Ad Content') !!}
        {!! Form::text('text', null, ['class' => 'w-100']) !!}
    </div>

    <div class="text-right">
        {!! Form::submit('Create Ad', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection
