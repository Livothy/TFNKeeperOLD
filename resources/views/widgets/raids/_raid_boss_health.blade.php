<div class="rounded raid-boss-health p-2" style="background-color: rgba(255, 255, 255, 0.65);">
    <div>
        {!! $raidBoss->name !!} has taken <b>{{ $raidBoss->damage }} points of damage</b> so far.
    </div>
    @if ($raidBoss->health)
        <div>
            <b>{{ $raidBoss->remainingHealth }} health</b> remains.
        </div>
        <div class="progress font-weight-bold" style="border: 2px solid var(--dark); height: 1.75rem; font-size: 1.15rem;">
            <div class="progress-bar {{ $raidBoss->damage >= $raidBoss->health ? 'text-center' : 'progress-bar-striped progress-bar-animated' }}" role="progressbar" style="width: {{ $raidBoss->remainingHealthPercentage }}%; {!! $raidBoss->barStyling ? $raidBoss->barStyling : '' !!}"
                aria-valuenow="{{ $raidBoss->remainingHealth }}" aria-valuemin="0" aria-valuemax="{{ $raidBoss->health }}">
                {{ $raidBoss->remainingHealth }} / {{ $raidBoss->health }}
            </div>
        </div>
    @endif
</div>
