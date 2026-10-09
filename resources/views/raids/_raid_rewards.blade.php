<h3 class="mb-1">
    Rewards
</h3>
@if ($rewards)
    <div class="logs-table">
        <div class="logs-table-header">
            <div class="row">
                <div class="col-12 col-md-5">
                    <div class="logs-table-cell">Reward</div>
                </div>
                <div class="col-6 col-md">
                    <div class="logs-table-cell">Quantity</div>
                </div>
                <div class="col-6 col-md">
                    <div class="logs-table-cell">Damage Required</div>
                </div>
            </div>
        </div>
        <div class="logs-table-body">
            @foreach ($rewards->sortBy('damage_required') as $reward)
                <div class="logs-table-row">
                    <div class="row flex-wrap">
                        <div class="col-12 col-md-5">
                            <div class="logs-table-cell">
                                {!! $reward->reward->displayName !!} ({{ $reward->rewardable_type }})
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="logs-table-cell">
                                x{{ $reward->quantity }}
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="logs-table-cell">
                                {{ $reward->damage_required }}
                                @if (isset($raid) && $raid->userDamage(Auth::user() ?? null) && $raid->userDamage(Auth::user() ?? null) >= $reward->damage_required)
                                    <i class="text-success fas fa-check ml-1" data-toggle="tooltip" title="You have dealt enough damage to receive this reward!"></i>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endif
