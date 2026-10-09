<ul class="w-100">
    <li class="sidebar-header"><a href="{{ url(__('raids.raids')) }}" class="card-link">{{ ucfirst(__('raids.raids')) }}</a></li>

    @if ($currentRaid)
        <li class="sidebar-section text-center">
            <div class="sidebar-section-header h5 mb-0">Current {{ ucfirst(__('raids.raid')) }}</div>
            <div class="sidebar-item">
                <a href="{{ url(__('raids.raids') . '/current') }}" class="font-weight-bold {{ set_active(__('raids.raids') . '/current*') }}">
                    {!! $currentRaid->name !!}
                </a>
            </div>
            @if ($currentRaid->start_at)
                <div class="sidebar-item small">
                    Started {!! pretty_date($currentRaid->start_at) !!}
                </div>
            @endif
            <div class="sidebar-item small">
                @if ($currentRaid->end_at && $currentRaid->end_at < Carbon\Carbon::now())
                    Ended {!! format_date($currentRaid->end_at) !!}
                @else
                    Until {!! $currentRaid->end_at ? format_date($currentRaid->end_at) : __('raids.boss') . ' defeated' !!}
                @endif
            </div>
        </li>
    @endif

    <li class="sidebar-section">
        <div class="sidebar-section-header">{{ ucfirst(__('raids.raid')) }} Data</div>
        <div class="sidebar-item"><a href="{{ url(__('raids.raids')) }}" class="{{ set_active(__('raids.raids')) }}">All {{ ucfirst(__('raids.raids')) }}</a></div>
        <div class="sidebar-item"><a href="{{ url(__('raids.raids') . '/' . __('raids.bosses')) }}" class="{{ set_active(__('raids.raids') . '/' . __('raids.bosses') . '*') }}">{{ ucwords(__('raids.raid') . ' ' . __('raids.bosses')) }}</a></div>
    </li>
</ul>
