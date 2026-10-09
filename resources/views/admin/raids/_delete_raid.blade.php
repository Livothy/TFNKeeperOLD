@if ($raid)
    {!! Form::open(['url' => 'admin/data/' . __('raids.raids') . '/delete/' . $raid->id]) !!}

    <p>You are about to delete the {{ __('raids.raid') }} <strong>{{ $raid->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $raid->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete ' . ucfirst(__('raids.raid')), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ __('raids.raid') }} selected.
@endif
