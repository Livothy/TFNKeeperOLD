@if ($carrier)
    {!! Form::open(['url' => 'admin/data/carrier/delete/' . $carrier->id]) !!}

    <p>You are about to delete the carrier <strong>{{ $carrier->name }}</strong>. This is not reversible. If characters possessing this carrier exists, you will not be able to delete this carrier.</p>
    <p>Are you sure you want to delete <strong>{{ $carrier->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Carrier', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid carrier selected.
@endif
