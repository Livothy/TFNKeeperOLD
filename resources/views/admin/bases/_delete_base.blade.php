@if ($base)
    {!! Form::open(['url' => 'admin/data/base/delete/' . $base->id]) !!}

    <p>You are about to delete the base <strong>{{ $base->name }}</strong>. This is not reversible. If characters possessing this base exist, you will not be able to delete this base.</p>
    <p>Are you sure you want to delete <strong>{{ $base->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Base', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid base selected.
@endif
