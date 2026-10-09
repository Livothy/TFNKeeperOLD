@extends('layouts.app')

@section('title')
    {{ ucfirst(__('character_likes.likes')) }} Leaderboard
@endsection

@section('sidebar')
    @include('browse._sidebar')
@endsection

@section('content')
    {!! breadcrumbs([ucfirst(__('character_likes.likes')) . ' Leaderboard' => __('character_likes.likes') . '-leaderboard']) !!}
    <h1>{{ ucfirst(__('character_likes.likes')) }} Leaderboard</h1>
    <p>Which character has been {{ __('character_likes.liked') }} the most? The least? Find out here!</p>

    <p class="alert alert-warning my-2">Remember to be nice! Harassment over {{ __('character_likes.likes') }} may be subject to punishment according to {{ config('lorekeeper.settings.site_name', 'The site\'s') }} admins.</p>

    <div>
        {!! Form::open(['method' => 'GET']) !!}
        <div class="form-inline justify-content-end">
            <div class="masterlist-search-field">
                {!! Form::label('name', 'Character Name/Code: ', ['class' => 'mr-2']) !!}
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control']) !!}
            </div>
            <div class="masterlist-search-field">
                {!! Form::label('owner', 'Owner Username: ') !!}
                {!! Form::select('owner', $userOptions, Request::get('owner'), ['class' => 'form-control mr-2 userselectize', 'style' => 'width: 250px', 'placeholder' => 'Select a User']) !!}
            </div>
        </div>

        <div class="form-inline justify-content-end mb-3">
            <div class="form-group mr-3">
                {!! Form::label('sort', 'Sort: ', ['class' => 'mr-2']) !!}
                {!! Form::select('sort', ['desc' => ucfirst(__('character_likes.likes')) . ' Descending', 'asc' => ucfirst(__('character_likes.likes')) . ' Ascending'], Request::get('sort'), ['class' => 'form-control']) !!}
            </div>
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    </div>
    <div class="text-center">
        <h4>Random Character</h4>
        <p>Here's a random character that's allowing {{ __('character_likes.likes') }}. {{ ucfirst(__('character_likes.like')) }} them if you like them, maybe?</p>

        @include('home._character', ['character' => $randomcharacter])
    </div>


    {!! $characters->render() !!}

    <div id="listView">
        <table class="table table-sm">
            <thead>
                <tr>
                    <th>Owner</th>
                    <th>Name</th>
                    <th>{{ ucfirst(__('character_likes.likes')) }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($characters as $character)
                    <tr>
                        <td>{!! $character->displayOwner !!}</td>
                        <td>
                            @if (!$character->is_visible)
                                <i class="fas fa-eye-slash"></i>
                            @endif {!! $character->displayName !!}
                        </td>
                        <td>{!! $character->likeTotal !!}</td>
                @endforeach
            </tbody>
        </table>
    </div>
    {!! $characters->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $characters->total() }} result{{ $characters->total() == 1 ? '' : 's' }} found.</div>
@endsection
