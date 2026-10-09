@if ($raidBoss && $bossImage)
    {!! Form::open(['url' => 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/' . $raidBoss->id . '/image/delete/' . $bossImage->id]) !!}

    <p>You are about to delete an image for the {{ __('raids.raid') . ' ' . __('raids.boss') }} <strong>{{ $raidBoss->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete an image for <strong>{{ $raidBoss->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete ' . ucfirst(__('raids.boss')) . ' Image', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ __('raids.boss') }} image selected.
@endif
