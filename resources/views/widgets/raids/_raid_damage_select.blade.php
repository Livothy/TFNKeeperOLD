@php
    $items = \App\Models\Item\Item::orderBy('name')->pluck('name', 'id');
    $currencies = \App\Models\Currency\Currency::where('is_user_owned', 1)
        ->orderBy('name')
        ->pluck('name', 'id');
@endphp
<div class="logs-table" id="damageTable">
    <div class="logs-table-header">
        <div class="row">
            <div class="col-6 col-md">
                <div class="logs-table-cell">Type</div>
            </div>
            <div class="col-6 col-md">
                <div class="logs-table-cell">Attack</div>
            </div>
            <div class="col-4 col-md">
                <div class="logs-table-cell">Qty</div>
            </div>
            <div class="col-4 col-md-2">
                <div class="logs-table-cell">Damage</div>
            </div>
            <div class="col-4 col-md-2">
                <div class="logs-table-cell">Damage (Max)</div>
            </div>
        </div>
    </div>
    <div class="logs-table-body" id="damageTableBody">
        <div class="logs-table-row damage-row">
            <div class="row flex-wrap align-items-center">
                <div class="col-6 col-md">
                    <div class="logs-table-cell">
                        {!! Form::select('damage_type', ['Item' => 'Item', 'Currency' => 'Currency'], $damage['type'] ?? null, [
                            'class' => 'form-control damage-type',
                            'placeholder' => 'Select Damage Type',
                        ]) !!}
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="logs-table-cell damage-row-select">
                        @if ($damage && $damage['type'] == 'Item')
                            {!! Form::select('damage_id', $items, $damage['id'], ['class' => 'form-control damage-item-select selectize', 'placeholder' => 'Select Item']) !!}
                        @elseif($damage && $damage['type'] == 'Currency')
                            {!! Form::select('damage_id', $currencies, $damage['id'], ['class' => 'form-control damage-currency-select selectize', 'placeholder' => 'Select Currency']) !!}
                        @endif
                    </div>
                </div>
                <div class="col-6 col-md">
                    <div class="logs-table-cell">
                        {!! Form::number('damage_quantity', $damage['quantity'] ?? 1, ['class' => 'form-control', 'steps' => '1']) !!}
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">
                        {!! Form::number('damage_base', $damage['base'] ?? 1, ['class' => 'form-control', 'steps' => '1']) !!}
                    </div>
                </div>
                <div class="col-6 col-md-2">
                    <div class="logs-table-cell">
                        {!! Form::number('damage_max', $damage['max'] ?? null, ['class' => 'form-control', 'steps' => '1', 'placeholder' => 'Optional']) !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
