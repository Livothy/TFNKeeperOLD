<div id="stockTable">
    <div class="row border-bottom font-weight-bold">
        <div class="col-6 col-md-3">Item</div>
        <div class="col-6 col-md-2 order-3 order-md-2">Visible?</div>
        <div class="col-12 col-md-4 order-2 order-md-3">Cost / Currency</div>
        <div class="col-6 col-md-3 order-4">Remove</div>
    </div>

    {!! $stocks->render() !!}
    @foreach ($stocks as $stock)
        <div class="row flex-wrap border-bottom py-2">
            {!! Form::hidden('stock_id[]', $stock->id) !!}

            <div class="col-6 col-md-3">
                @if ($stock->item)
                    @if (isset($stock->item->image_url))
                        <img class="small-icon" src="{{ $stock->item->image_url }}" alt="{{ $stock->item->name }}">
                    @endif
                    {{ $stock->item->name }}
                    <a href="{{ url('user-shops/item-search?item_ids[]=' . $stock->item->id) }}" data-toggle="tooltip" title="Compare prices"><i class="fas fa-search"></i></a>
                    @if (!$stock->item->canUserSell)
                        <div class="small text-danger">This item can no longer be sold. Remove it to your inventory.</div>
                    @endif
                @else
                    (Deleted Item)
                @endif
            </div>

            <div class="col-6 col-md-2 order-3 order-md-2">
                {!! Form::checkbox('is_visible[' . $stock->id . ']', 1, $stock->is_visible, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
            </div>

            <div class="col-12 col-md-4 order-2 order-md-3">
                <div class="row no-gutters">
                    <div class="col-4 pr-1">
                        {!! Form::number('cost[' . $stock->id . ']', (int) $stock->cost, ['class' => 'form-control', 'min' => 0, 'step' => 1]) !!}
                    </div>
                    <div class="col-8">
                        {!! Form::select('currency_id[' . $stock->id . ']', $currencies, $stock->currency_id, ['class' => 'form-control', 'placeholder' => 'Select Currency']) !!}
                    </div>
                </div>
            </div>

            <div class="col-6 col-md-3 order-4">
                {!! Form::selectRange('quantity[' . $stock->id . ']', 0, $stock->quantity, 0, ['class' => 'quantity-select', 'style' => 'min-width:40px;']) !!} / {{ $stock->quantity }}
            </div>
        </div>
    @endforeach
    {!! $stocks->render() !!}
</div>
