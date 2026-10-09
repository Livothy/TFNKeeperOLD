@if ($raid)
    {!! Form::open(['url' => 'admin/data/' . __('raids.raids') . '/reward/' . $raid->id]) !!}
    <p>
        The {{ __('raids.raid') }} <strong>{{ $raid->name }}</strong> has concluded and rewards are awaiting distribution.
    </p>
    <p>
        There were a total of {{ $raid->participantCount }} {{ $raid->participantCount == 1 ? 'participant' : 'participants' }} in this {{ __('raids.raid') }}. All participants will be rewarded based on damage dealt.<br>
        Click the button below when you are ready to distribute rewards. <u>This is not reversible.</u>
    </p>

    <div class="text-right">
        {!! Form::submit('Distribute Rewards', ['class' => 'btn btn-success']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ __('raids.raid') }} selected.
@endif
