@if ($user_ads)
    {!! Form::open(['url' => 'admin/user_ads/delete/' . $user_ads->id]) !!}

    <div class="alert alert-info">
        <p>You are about to delete the user ad <strong>{{ $user_ads->text }}</strong>. This is not reversible.</p>
        <p>Are you sure you want to delete <strong>{{ $user_ads->text }}</strong>?</p>
    </div>

    <div class="text-right">
        {!! Form::submit('Delete Ad', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid post selected.
@endif
