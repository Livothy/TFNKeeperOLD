<div class="logs-table">
    <div class="logs-table-header">
        <div class="row">
            <div class="col-2 text-center">
                <div class="logs-table-cell">Rank</div>
            </div>
            <div class="col-10 col-md">
                <div class="logs-table-cell">User</div>
            </div>
            <div class="col-6 col-md-2">
                <div class="logs-table-cell">Total Damage</div>
            </div>
            <div class="col-6 col-md-4">
                <div class="logs-table-cell"># of Attacks</div>
            </div>
        </div>
    </div>
    <div class="logs-table-body">
        @foreach ($entries->take($limit ?? 10) as $log)
            <div class="logs-table-row">
                <div class="row flex-wrap align-items-center">
                    <div class="col-2 text-center">
                        <div class="logs-table-cell">
                            @if ($loop->index + 1 < 4)
                                <span class="btn btn-sm btn-primary font-weight-bold">
                                    {{ $loop->index + 1 }}
                                </span>
                            @else
                                {{ $loop->index + 1 }}
                            @endif
                        </div>
                    </div>
                    <div class="col-10 col-md">
                        <div class="logs-table-cell">
                            {!! $log->user->displayName !!}
                        </div>
                    </div>
                    <div class="col-6 col-md-2">
                        <div class="logs-table-cell">
                            {{ $log->total_damage }}
                        </div>
                    </div>
                    <div class="col-6 col-md-4">
                        <div class="logs-table-cell">
                            {{ $raid->damageLogs()->where('user_id', $log->user_id)->count() ?? '???' }}
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>
