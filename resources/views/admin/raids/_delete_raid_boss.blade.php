@if ($raidBoss)
    {!! Form::open(['url' => 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/delete/' . $raidBoss->id]) !!}

    <p>You are about to delete the {{ __('raids.raid') . ' ' . __('raids.boss') }} <strong>{{ $raidBoss->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $raidBoss->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete ' . ucwords(__('raids.raid') . ' ' . __('raids.boss')), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ __('raids.raid') . ' ' . __('raids.boss') }} selected.
@endif
