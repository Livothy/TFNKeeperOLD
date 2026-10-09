@php
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
        ->orderBy('name')
        ->pluck('name', 'id');
    if ($showLootTables) {
        $tables = \App\Models\Loot\LootTable::orderBy('name')->pluck('name', 'id');
    }
    if ($showRaffles) {
        $raffles = \App\Models\Raffle\Raffle::where('rolled_at', null)
            ->where('is_active', 1)
            ->orderBy('name')
            ->pluck('name', 'id');
    }
@endphp

<div class="text-right mb-3">
    <a href="#" class="btn btn-outline-info" id="addLoot">Add Reward</a>
</div>
<div class="logs-table" id="lootTable">
    <div class="logs-table-header">
        <div class="row">
            <div class="col-6 col-md">
                <div class="logs-table-cell">Type</div>
            </div>
            <div class="col-6 col-md">
                <div class="logs-table-cell">Reward</div>
            </div>
            <div class="col-6 col-md">
                <div class="logs-table-cell">Qty</div>
            </div>
            <div class="col">
                <div class="logs-table-cell">Damage Req.</div>
            </div>
        </div>
    </div>
    <div class="logs-table-body" id="lootTableBody">
        @if ($loots)
            @foreach ($loots as $loot)
                <div class="logs-table-row loot-row">
                    <div class="row flex-wrap align-items-center">
                        <div class="col-6 col-md">
                            <div class="logs-table-cell">
                                {!! Form::select('rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency'] + ($showLootTables ? ['LootTable' => 'Loot Table'] : []) + ($showRaffles ? ['Raffle' => 'Raffle Ticket'] : []), $loot->rewardable_type, [
                                    'class' => 'form-control reward-type',
                                    'placeholder' => 'Select Reward Type',
                                ]) !!}
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="logs-table-cell loot-row-select">
                                @if ($loot->rewardable_type == 'Item')
                                    {!! Form::select('rewardable_id[]', $items, $loot->rewardable_id, ['class' => 'form-control item-select selectize', 'placeholder' => 'Select Item']) !!}
                                @elseif($loot->rewardable_type == 'Currency')
                                    {!! Form::select('rewardable_id[]', $currencies, $loot->rewardable_id, ['class' => 'form-control currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                                @elseif($showLootTables && $loot->rewardable_type == 'LootTable')
                                    {!! Form::select('rewardable_id[]', $tables, $loot->rewardable_id, ['class' => 'form-control table-select selectize', 'placeholder' => 'Select Loot Table']) !!}
                                @elseif($showRaffles && $loot->rewardable_type == 'Raffle')
                                    {!! Form::select('rewardable_id[]', $raffles, $loot->rewardable_id, ['class' => 'form-control raffle-select selectize', 'placeholder' => 'Select Raffle']) !!}
                                @endif
                            </div>
                        </div>
                        <div class="col-6 col-md">
                            <div class="logs-table-cell">
                                {!! Form::number('quantity[]', $loot->quantity ?? 1, ['class' => 'form-control', 'steps' => '1']) !!}
                            </div>
                        </div>
                        <div class="col">
                            <div class="logs-table-cell">
                                {!! Form::number('damage_required[]', $loot->damage_required ?? 0, ['class' => 'form-control', 'steps' => '1']) !!}
                            </div>
                        </div>
                        <div class="col-auto text-right">
                            <div class="logs-table-cell">
                                <a href="#" class="btn btn-danger btn-sm remove-loot-button">
                                    <i class="fas fa-times"></i>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            @endforeach
        @endif
    </div>
</div>
