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

<div id="lootRowData" class="hide loot-row-copy">
    <div class="logs-table-row loot-row">
        <div class="row flex-wrap align-items-center">
            <div class="col-6 col-md">
                <div class="logs-table-cell">
                    {!! Form::select('rewardable_type[]', ['Item' => 'Item', 'Currency' => 'Currency'] + ($showLootTables ? ['LootTable' => 'Loot Table'] : []) + ($showRaffles ? ['Raffle' => 'Raffle Ticket'] : []), null, [
                        'class' => 'form-control reward-type',
                        'placeholder' => 'Select Reward Type',
                    ]) !!}
                </div>
            </div>
            <div class="col-6 col-md">
                <div class="logs-table-cell loot-row-select"></div>
            </div>
            <div class="col-6 col-md">
                <div class="logs-table-cell">
                    {!! Form::number('quantity[]', 1, ['class' => 'form-control', 'steps' => '1']) !!}
                </div>
            </div>
            <div class="col">
                <div class="logs-table-cell">
                    {!! Form::number('damage_required[]', 0, ['class' => 'form-control', 'steps' => '1']) !!}
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
    {!! Form::select('rewardable_id[]', $items, null, ['class' => 'form-control item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('rewardable_id[]', $currencies, null, ['class' => 'form-control currency-select', 'placeholder' => 'Select Currency']) !!}
    @if ($showLootTables)
        {!! Form::select('rewardable_id[]', $tables, null, ['class' => 'form-control table-select', 'placeholder' => 'Select Loot Table']) !!}
    @endif
    @if ($showRaffles)
        {!! Form::select('rewardable_id[]', $raffles, null, ['class' => 'form-control raffle-select', 'placeholder' => 'Select Raffle']) !!}
    @endif
</div>

<div class="damage-data hide">
    {!! Form::select('damage_id', $items, null, ['class' => 'form-control damage-item-select', 'placeholder' => 'Select Item']) !!}
    {!! Form::select('damage_id', $currencies, null, ['class' => 'form-control damage-currency-select', 'placeholder' => 'Select Currency']) !!}
</div>
