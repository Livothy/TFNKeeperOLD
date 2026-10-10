@extends('admin.layout')

@section('admin-title')
    {{ $rankReward->id ? 'Edit' : 'Create' }} Rank Reward
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        'Rank Rewards' => 'admin/rank-rewards',
        ($rankReward->id ? 'Edit' : 'Create') . ' Rank Reward' => $rankReward->id ? 'admin/rank-rewards/edit/' . $rankReward->id : 'admin/rank-rewards/create',
    ]) !!}

    <h1>{{ $rankReward->id ? 'Edit' : 'Create' }} Rank Reward
        @if ($rankReward->id)
            <a href="#" class="btn btn-danger float-right delete-rank-reward-button">Delete Rank Reward</a>
        @endif
    </h1>

    {!! Form::open(['url' => $rankReward->id ? 'admin/rank-rewards/edit/' . $rankReward->id : 'admin/rank-rewards/create']) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('name', 'Name') !!} {!! add_help('Internal name for admin purposes only.') !!}
        {!! Form::text('name', $rankReward->name, ['class' => 'form-control']) !!}
    </div>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('rank_id', 'Rank') !!}
                {!! Form::select('rank_id', $ranks, $rankReward->rank_id, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('reward_time', 'Give Reward') !!}
                {!! Form::select('reward_time', \App\Models\RankReward::$intervals, $rankReward->reward_time ?? 2, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_active', 1, $rankReward->id ? $rankReward->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Is Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If inactive, this reward will not be given out.') !!}
    </div>

    @if ($rankReward->last_rewarded_at)
        <p class="text-muted">Last given out {!! pretty_date($rankReward->last_rewarded_at) !!}.</p>
    @endif

    <h3>Rewards</h3>
    <p>Rewards are credited to every user in this rank (except banned or deactivated users) when the interval comes up. Rewards run once a day with the site scheduler.</p>
    @include('widgets._loot_select', ['loots' => $rankReward->rewards, 'showLootTables' => true, 'showRaffles' => true])

    <div class="text-right">
        {!! Form::submit($rankReward->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @include('widgets._loot_select_row', ['showLootTables' => true, 'showRaffles' => true])
@endsection

@section('scripts')
    @parent
    @include('js._loot_js', ['showLootTables' => true, 'showRaffles' => true])
    <script>
        $(document).ready(function() {
            $('.delete-rank-reward-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/rank-rewards/delete') }}/{{ $rankReward->id }}", 'Delete Rank Reward');
            });
        });
    </script>
@endsection
