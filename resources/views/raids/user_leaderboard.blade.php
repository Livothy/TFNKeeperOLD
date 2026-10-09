@extends('raids.layout')

@section('raids-title')
    {!! $raid->name !!} Leaderboard
@endsection

@section('content')
    {!! breadcrumbs([ucfirst(__('raids.raids')) . ' Index' => __('raids.raids'), 'Current ' . ucfirst(__('raids.raid')) . ($currentRaid ? ' - ' . $currentRaid->name : '') => __('raids.raids') . '/current']) !!}

    <h2 class="text-center mb-1">
        {!! $raid->displayName !!} Leaderboard
    </h2>
    <div class="card mb-3">
        <div class="card-body text-center">
            This leaderboard displays the users that dealt the top 10 highest total damage in this {{ __('raids.raid') }}, {!! $raid->name !!}.
        </div>
    </div>

    @include('raids.widgets._leaderboard_table', ['entries' => $entries, 'raid' => $raid, 'limit' => 10])
@endsection
