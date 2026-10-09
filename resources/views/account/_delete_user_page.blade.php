@if ($userPage)
    {!! Form::open(['url' => 'account/user-pages/delete/' . $userPage->id]) !!}
    <p>
        You are about to delete your user page <strong>{{ $userPage->title }}</strong>. This is not reversible.
    </p>
    <p>
        Are you sure you want to delete <strong>{{ $userPage->title }}</strong>?
    </p>

    <div class="text-right">
        {!! Form::submit('Delete User Page', ['class' => 'btn btn-danger']) !!}
    </div>
    {!! Form::close() !!}
@else
    Invalid user page selected.
@endif
