@if ($raid)
    {!! Form::open(['url' => 'admin/data/' . __('raids.raids') . '/end/' . $raid->id]) !!}
    <p>
        The {{ __('raids.raid') }} <strong>{{ $raid->name }}</strong> is currently ongoing!
        @if (isset($raid->end_at) && $raid->end_at < Carbon\Carbon::now())
            It is set to end {!! pretty_date($raid->end_at) !!}
        @elseif (!isset($raid->end_at))
            It does not have a set end time.
        @endif
        @if ($raid->continue_raid)
            It is set to keep continuing even after the {{ __('raids.boss') }}'s health is depleted, if applicable.
        @endif
    </p>
    <p>
        If you'd like to manually end <strong>{{ $raid->name }}</strong>, you may press the button below. <u>This is not reversible.</u>
    </p>

    <div class="text-right">
        {!! Form::submit('End ' . ucfirst(__('raids.raid')), ['class' => 'btn btn-danger']) !!}
    </div>

    {!! Form::close() !!}
@else
    Invalid {{ __('raids.raid') }} selected.
@endif
