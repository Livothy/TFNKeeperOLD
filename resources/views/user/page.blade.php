@extends('user.layout')

@section('profile-title')
    {{ $userPage->title }}
@endsection

@section('profile-content')
    {!! breadcrumbs(['Users' => 'users', $user->name => $user->url, $userPage->title => $userPage->url]) !!}
    @if (Auth::check() && Auth::user()->id == $userPage->user_id)
        <div class="text-right float-right">
            <a href="{{ $userPage->editUrl }}">
                <i class="fas fa-pen" data-toggle="tooltip" title="Edit User Page"></i>
            </a>
        </div>
    @endif

    <h1 class="mb-0">
        @if (!$userPage->is_visible)
            <i class="fas fa-eye-slash" data-toggle="tooltip" title="This page is currently hidden from other users."></i>
        @endif
        {{ $userPage->title }}
    </h1>
    <div class="mb-2 row no-gutters">
        <div class="col-sm">
            <strong>Created:</strong> {!! format_date($userPage->created_at) !!}
        </div>
        <div class="col-sm">
            <strong>Last updated:</strong> {!! format_date($userPage->updated_at) !!}
        </div>
    </div>

    <div class="site-page-content parsed-text">
        {!! $userPage->parsed_text !!}
    </div>

    @if ($userPage->can_comment)
        <hr>
        <div class="container-fluid">
            @comments([
                'model' => $userPage,
                'perPage' => 5,
            ])
        </div>
    @endif
@endsection
