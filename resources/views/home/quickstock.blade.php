@extends('home.layout')

@section('home-title')
    Quickstock
@endsection

@section('home-content')
    {!! breadcrumbs(['Inventory' => 'inventory', 'Quickstock' => 'inventory/quickstock']) !!}

    <h1>
        Quickstock
    </h1>

    <p>Move items from your inventory into one of your shops. New stock stays hidden until you set its price and make it visible on the shop's edit page.</p>
    <p>Only transferable items from categories that allow user selling can be stocked. Items held in trades, design updates or submissions can't be stocked.</p>
    @if (count($shopOptions))
        {!! Form::open(['url' => 'inventory/quickstock-items']) !!}
        <div class="form-group">
            {!! Form::select('shop_id', $shopOptions, null, [
                'class' => 'form-control mr-2 default shop-select',
                'placeholder' => 'Select Shop',
            ]) !!}
        </div>
        @include('widgets._inventory_select', [
            'user' => Auth::user(),
            'inventory' => $inventory,
            'categories' => $categories,
            'selected' => [],
            'page' => $page,
        ])

        <div class="text-right">
            {!! Form::submit('Submit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
    @else
        <div class="alert alert-warning text-center">
            You can't stock a shop if you <a href="{{ url('user-shops/create') }}">don't have one...</a>
        </div>
    @endif
@endsection

@section('scripts')
    @parent

    @include('widgets._inventory_select_js', ['readOnly' => true])
@endsection
