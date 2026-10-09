@extends('raids.layout')

@section('raids-title')
    {!! $raid->name !!}
@endsection

@section('content')
    {!! breadcrumbs([ucfirst(__('raids.raids')) . ' Index' => __('raids.raids'), $raid->name => __('raids.raids') . '/data/' . $raid->id]) !!}

    <div class="row no-gutters justify-content-center">
        <div class="col-md-11">
            <div class="card mb-3">
                <div class="card-body">
                    <div class="mb-1">
                        @if (!$raid->is_visible)
                            <i class="fas fa-eye-slash" data-toggle="tooltip" title="This {{ __('raids.raid') }} is not visible to regular users."></i>
                        @endif
                        <a href="{{ $raid->idUrl }}" class="h1 mb-0">
                            {!! $raid->name !!}
                        </a>
                    </div>
                    @if ($raid->imageUrl)
                        <div class="my-2 rounded raid-display" style="background-image: url('{{ $raid->imageUrl }}');">
                            @if ($raid->bosses->count())
                                <div class="row no-gutters text-center justify-content-center h-100">
                                    @foreach ($raid->bosses as $boss)
                                        <div class="col-md-3 p-1">
                                            <div class="rounded h-100 d-flex align-items-center justify-content-center" style="background-color: rgba(255, 255, 255, 0.65);">
                                                <div>
                                                    @if ($boss->imageUrl)
                                                        <div class="mb-2">
                                                            <img src="{{ $boss->imageUrl }}" class="img-fluid">
                                                        </div>
                                                    @endif
                                                    <div class="h4 mb-0">
                                                        {!! $boss->displayName !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            @endif
                        </div>
                    @endif
                    <div class="row h6 text-center mb-2">
                        <div class="col-md">
                            <b>Started</b> {!! $raid->start_at ? pretty_date($raid->start_at) : 'an unknown time' !!}.
                        </div>
                        <div class="col-md">
                            @if ($raid->isActive)
                                @if ($raid->end_at && $raid->end_at > Carbon\Carbon::now())
                                    <b>Ends</b> {!! 'at ' . format_date($raid->end_at) !!}.
                                @else
                                    <b>Ends</b> when {{ __('raids.boss') }} is defeated.
                                @endif
                            @else
                                <b>Ended</b> {!! $raid->end_at ? pretty_date($raid->end_at) : 'when ' . __('raids.boss') . ' was defeated' !!}.
                            @endif
                        </div>
                    </div>
                    @if (isset($raid->description) && $raid->description)
                        <div class="card p-3 mb-3">
                            {!! $raid->parsed_description !!}
                        </div>
                    @endif
                    <div class="mb-3">
                        <a href="{{ url('raids/leaderboard/' . $raid->id) }}" class="btn btn-secondary d-block text-center">
                            Leaderboard
                        </a>
                    </div>

                    <hr>

                    <h3 class="mb-1">
                        {{ ucfirst(__('raids.bosses')) }}
                    </h3>
                    <div class="card py-3 px-2">
                        @if ($raid->bosses->count())
                            @foreach ($raid->bosses as $boss)
                                <div class="row no-gutters mb-2 {{ !$loop->last ? 'border-bottom pb-2' : '' }}">
                                    @if ($boss->imageUrl)
                                        <div class="col-md-3 text-center">
                                            <img src="{{ $boss->imageUrl }}" class="img-fluid" alt="{{ $boss->name }}">
                                        </div>
                                    @endif
                                    <div class="{{ $boss->imageUrl ? 'col-md-9 pr-md-2' : 'col-12' }}">
                                        <div class="h4 mb-1">
                                            {!! $boss->displayName !!}
                                            @if (isset($boss->health) && $boss->health)
                                                - {{ $boss->health }} HP
                                            @else
                                                - HP Unknown
                                            @endif
                                        </div>
                                        @if (isset($boss->description) && $boss->description)
                                            <div class="card p-3 mb-1">
                                                {!! $boss->parsed_description !!}
                                            </div>
                                        @endif
                                        <div class="text-right">
                                            {{ !$raid->isActive ? 'Was dealt' : 'Has been dealt' }} a total of <b>{{ $boss->damage }} points of damage.</b>
                                        </div>
                                    </div>
                                </div>
                            @endforeach
                        @endif
                    </div>

                    <hr>

                    <div class="card pb-3 pt-2 px-3">
                        <h3 class="mb-1">
                            Attack Method
                        </h3>
                        @if ($raid->damage)
                            <div>
                                Requires <b>{{ $raid->attackAsset()['quantity'] }} {!! $raid->attackAsset()['asset']->displayName !!} ({{ $raid->damage['type'] }})</b> to attack.
                            </div>
                            <div>
                                @if (isset($raid->damage['max']))
                                    Between <b>{{ $raid->damage['base'] }}</b> to <b>{{ $raid->damage['max'] }}</b> points of damage per attack.
                                @else
                                    <b>{{ $raid->damage['base'] }}</b> points of damage per attack.
                                @endif
                            </div>
                        @endif
                    </div>

                    <hr>

                    <div class="card pb-3 pt-2 px-3">
                        @include('raids._raid_rewards', ['rewards' => $raid->rewards])
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
