@extends('admin.layout')

@section('admin-title')
    Rank Rewards
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Rank Rewards' => 'admin/rank-rewards']) !!}

    <h1>Rank Rewards</h1>

    <p>Rank rewards are given automatically to every user of a rank on a set interval, as long as the reward is active. Banned and deactivated users are skipped.</p>

    <div class="text-right mb-3">
        <a class="btn btn-primary" href="{{ url('admin/rank-rewards/create') }}"><i class="fas fa-plus"></i> Create New Rank Reward</a>
    </div>

    @if (!count($rankRewards))
        <p>No rank rewards found.</p>
    @else
        {!! $rankRewards->render() !!}

        <div class="mb-4 logs-table">
            <div class="logs-table-header">
                <div class="row">
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Name</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Rank</div>
                    </div>
                    <div class="col-6 col-md-1">
                        <div class="logs-table-cell">Active</div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="logs-table-cell">Given</div>
                    </div>
                </div>
            </div>
            <div class="logs-table-body">
                @foreach ($rankRewards as $rankReward)
                    <div class="logs-table-row">
                        <div class="row flex-wrap">
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">{{ $rankReward->name }}</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">{!! $rankReward->rank ? $rankReward->rank->displayName : 'Deleted Rank' !!}</div>
                            </div>
                            <div class="col-6 col-md-1">
                                <div class="logs-table-cell">{!! $rankReward->is_active ? '<i class="text-success fas fa-check"></i>' : '' !!}</div>
                            </div>
                            <div class="col-6 col-md-3">
                                <div class="logs-table-cell">{{ $rankReward->intervalName }}</div>
                            </div>
                            <div class="col-12 col-md-2 text-right">
                                <div class="logs-table-cell"><a href="{{ url('admin/rank-rewards/edit/' . $rankReward->id) }}" class="btn btn-primary py-0 px-2">Edit</a></div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        {!! $rankRewards->render() !!}

        <div class="text-center mt-4 small text-muted">{{ $rankRewards->total() }} result{{ $rankRewards->total() == 1 ? '' : 's' }} found.</div>
    @endif
@endsection
