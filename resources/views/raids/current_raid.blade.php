@extends('raids.layout')

@section('raids-title')
    Current {{ ucfirst(__('raids.raid')) }}{!! $currentRaid ? ' - ' . $currentRaid->name : '' !!}
@endsection

@section('content')
    {!! breadcrumbs([ucfirst(__('raids.raids')) . ' Index' => __('raids.raids'), 'Current ' . ucfirst(__('raids.raid')) . ($currentRaid ? ' - ' . $currentRaid->name : '') => __('raids.raids') . '/current']) !!}

    <h1 class="text-center mb-0">
        Current {{ ucfirst(__('raids.raid')) }}
    </h1>
    @if ($currentRaid)
        <h2 class="text-center mb-1">
            {!! $currentRaid->displayName !!}
        </h2>
        <div class="text-center mb-3">
            @include('widgets.raids._raid_boss_display', ['raid' => $currentRaid, 'raidBoss' => $currentRaid->currentBoss(), 'heading' => 'h3'])
        </div>

        <div class="card mb-3">
            <div class="card-body">
                <div class="h5 mb-1 text-center row no-gutters">
                    <div class="col-md">
                        Started {!! $currentRaid->start_at ? pretty_date($currentRaid->start_at) : 'at an unknown time' !!}
                    </div>
                    <div class="d-none d-md-block col-md-auto px-2">
                        ||
                    </div>
                    <div class="col-md">
                        {{ $currentRaid->end_at && $currentRaid->end_at < Carbon\Carbon::now() ? 'Ended' : 'Ends' }} {!! $currentRaid->end_at ? format_date($currentRaid->end_at) : 'when ' . __('raids.boss') . ' is defeated' !!}
                    </div>
                </div>
                @if (Auth::check())
                    @include('raids._raid_attack', ['raid' => $currentRaid, 'boss' => $currentRaid->currentBoss()])
                @else
                    <div class="text-center">
                        You must be logged in to attack this {{ __('raids.boss') }}.
                    </div>
                @endif
            </div>
        </div>

        <div class="card p-3">
            @include('raids._raid_rewards', ['rewards' => $currentRaid->rewards, 'raid' => $currentRaid])
        </div>
    @endif
@endsection
