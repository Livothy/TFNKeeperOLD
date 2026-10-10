@if($fetchquest)
    {!! Form::open(['url' => 'admin/data/fetch-quests/delete/'.$fetchquest->id]) !!}

    <p>You are about to delete the fetch quest <strong>{{ $fetchquest->name }}</strong>. This is not reversible.</p>
    <p>Are you sure you want to delete <strong>{{ $fetchquest->name }}</strong>?</p>

    <div class="text-right">
        {!! Form::submit('Delete Fetch Quest', ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else 
    Invalid fetch quest selected.
@endif