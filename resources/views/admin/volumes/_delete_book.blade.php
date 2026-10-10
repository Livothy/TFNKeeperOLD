@if($book)
    {!! Form::open(['url' => 'admin/data/volumes/books/delete/'.$book->id]) !!}

    <p>You are about to delete the {{ __('volumes.book') }} <strong>{{ $book->name }}</strong>. This is not reversible. If {{ __('volumes.volumes') }} in this {{ __('volumes.book') }} exist, you will not be able to delete this {{ __('volumes.book') }}.</p>
    <p>Are you sure you want to delete <strong>{{ $book->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete '.ucfirst(__('volumes.book')), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid {{ __('volumes.book') }} selected.
@endif