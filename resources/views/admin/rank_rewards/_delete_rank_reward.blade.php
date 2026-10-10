@if ($rankReward)
    {!! Form::open(['url' => 'admin/rank-rewards/delete/' . $rankReward->id]) !!}

    <p>You are about to delete the rank reward <strong>{{ $rankReward->name }}</strong>. This is not reversible. Rewards already given out are not affected.</p>
    <p>Are you sure you want to delete <strong>{{ $rankReward->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Rank Reward', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid rank reward selected.
@endif
