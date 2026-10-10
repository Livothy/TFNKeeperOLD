@extends('home.user_shops.layout')

@section('home.user_shops-title')
    User Shop Search
@endsection

@section('home.user_shops-content')
    {!! breadcrumbs(['User Shops' => 'user-shops/shop-index', 'Item Search' => 'user-shops/item-search']) !!}

    <h1>User Shop Item Search</h1>

    <p>Select the items you're looking to buy from other players to see which shops are stocking them and at what price.</p>
    <p>Selecting a category also searches every item in that category.</p>

    {!! Form::open(['method' => 'GET', 'class' => '']) !!}
    <div class="form-inline justify-content-end">
        <div class="form-group ml-3 mb-3">
            {!! Form::select('item_ids[]', $items, Request::get('item_ids'), [
                'id' => 'itemList',
                'class' => 'form-control',
                'placeholder' => 'Select Items',
                'style' => 'width: 25em; max-width: 100%;',
                'multiple',
            ]) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::select('item_category_id', $categories, Request::get('item_category_id'), ['class' => 'form-control', 'placeholder' => 'Search by Category']) !!}
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
        </div>
    </div>
    {!! Form::close() !!}

    @if ($searched_items)
        <h3>Search Results</h3>
        <p><b>Searching for:</b> {{ $searched_items->pluck('name')->implode(', ') }}</p>
        @if ($category)
            <p><b>Category:</b> {!! $category->displayName !!}</p>
        @endif
        @if ($shopItems && $shopItems->count())
            <div class="row ml-md-2">
                <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
                    <div class="col col-md-3 font-weight-bold">Item</div>
                    <div class="col col-md-3 font-weight-bold">Shop</div>
                    <div class="col col-md-2 font-weight-bold">Shop Owner</div>
                    <div class="col col-md-2 font-weight-bold">Quantity</div>
                    <div class="col col-md-2 font-weight-bold">Cost</div>
                </div>
                @foreach ($shopItems as $itemStock)
                    <div class="d-flex row flex-wrap col-12 mt-1 pt-1 px-0 ubt-top">
                        <div class="col col-md-3">{!! $itemStock->item->displayName !!}</div>
                        <div class="col col-md-3">{!! $itemStock->shop->displayName !!}</div>
                        <div class="col col-md-2">{!! $itemStock->shop->user->displayName !!}</div>
                        <div class="col col-md-2">{{ $itemStock->quantity }}</div>
                        <div class="col col-md-2">{!! $itemStock->currency ? $itemStock->currency->display((int) $itemStock->cost) : (int) $itemStock->cost !!}</div>
                    </div>
                @endforeach
            </div>
        @else
            <p>No shops are currently stocking the selected items.</p>
        @endif
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('#itemList').selectize({
                maxItems: 10
            });
        });
    </script>
@endsection
