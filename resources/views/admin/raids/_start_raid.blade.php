@if ($raid)
    {!! Form::open(['url' => 'admin/data/' . __('raids.raids') . '/start/' . $raid->id]) !!}
    <p>
        The {{ __('raids.raid') }} <strong>{{ $raid->name }}</strong> hasn't begun yet.
        @if (isset($raid->start_at) && $raid->start_at < Carbon\Carbon::now())
            It is set to start {!! pretty_date($raid->start_at) !!}
        @elseif (!isset($raid->start_at))
            It does not have a set start time.
        @endif
        @if ($raid->continue_raid)
            Once begun, it is set to keep continuing even after the {{ __('raids.boss') }}'s health is depleted, if applicable.
        @endif
    </p>
    <p>
        If you'd like to manually start <strong>{{ $raid->name }}</strong>, you may press the button below. <u>This is not reversible.</u>
    </p>

    <div class="text-right">
        {!! Form::submit('Start ' . ucfirst(__('raids.raid')), ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ __('raids.raid') }} selected.
@endif
