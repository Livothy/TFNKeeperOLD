@if ($marking)
    {!! Form::open(['url' => 'admin/data/markings/delete/' . $marking->id]) !!}

    <p>You are about to delete the marking <strong>{{ $marking->name }}</strong>. This is not reversible. If characters possessing this marking exist, you will not be able to delete this marking.</p>
    <p>Are you sure you want to delete <strong>{{ $marking->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Marking', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid marking selected.
@endif
