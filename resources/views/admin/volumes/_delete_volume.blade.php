@if($volume)
    {!! Form::open(['url' => 'admin/data/volumes/delete/'.$volume->id]) !!}

    <p>You are about to delete the {{ __('volumes.volume') }} <strong>{{ $volume->name }}</strong>. This is not reversible. If this {{ __('volumes.volume') }} exists in at least one user's possession, you will not be able to delete this {{ __('volumes.volume') }}.</p>
    <p>Are you sure you want to delete <strong>{{ $volume->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete '.ucfirst(__('volumes.volume')), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid {{ __('volumes.volume') }} selected.
@endif