@if (!$stock)
    <div class="text-center">Invalid item selected.</div>
@else
    <div class="text-center mb-3">
        <div class="mb-1"><a href="{{ $stock->item->idUrl }}"><img src="{{ $stock->item->imageUrl }}" alt="{{ $stock->item->name }}" /></a></div>
        <div><a href="{{ $stock->item->idUrl }}"><strong>{{ $stock->item->name }}</strong></a></div>
        <div><strong>Cost:</strong> {!! $stock->currency ? $stock->currency->display((int) $stock->cost) : (int) $stock->cost !!}</div>
        <div>Stock: {{ $stock->quantity }}</div>
        <div class="small">Sold by {!! $shop->user->displayName !!}</div>
    </div>

    @if ($stock->item->parsed_description)
        <div class="mb-2">
            <a data-toggle="collapse" href="#itemDescription" class="h5">Description <i class="fas fa-caret-down"></i></a>
            <div class="card collapse show mt-1" id="itemDescription">
                <div class="card-body">
                    {!! $stock->item->parsed_description !!}
                </div>
            </div>
        </div>
    @endif

    @if ($shop->user_id == Auth::user()->id)
        <div class="alert alert-info mb-0">This is your shop, so you can't buy from it.</div>
    @else
        <h5>
            Purchase
            <span class="float-right">
                In Inventory: {{ $userOwned->pluck('count')->sum() }}
            </span>
        </h5>

        {!! Form::open(['url' => 'user-shops/shop/buy']) !!}
        {!! Form::hidden('user_shop_id', $shop->id) !!}
        {!! Form::hidden('stock_id', $stock->id) !!}
        {!! Form::hidden('cost', (int) $stock->cost) !!}
        {!! Form::hidden('currency_id', $stock->currency_id) !!}
        {!! Form::label('quantity', 'Quantity') !!}
        {!! Form::selectRange('quantity', 1, $stock->quantity, 1, ['class' => 'form-control mb-3']) !!}
        <p>This item will be paid for from your account bank.</p>
        <div class="text-right">
            {!! Form::submit('Purchase', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @endif
@endif
