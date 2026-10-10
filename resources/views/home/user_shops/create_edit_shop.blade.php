@extends('home.user_shops.layout')

@section('home.user_shops-title')
    {{ $shop->id ? 'Edit' : 'Create' }} Shop
@endsection

@section('home.user_shops-content')
    {!! breadcrumbs([
        'My Shops' => 'user-shops',
        ($shop->id ? 'Edit' : 'Create') . ' Shop' => $shop->id ? 'user-shops/edit/' . $shop->id : 'user-shops/create',
    ]) !!}

    <h1>{{ $shop->id ? 'Edit' : 'Create' }} Shop
        @if ($shop->id)
            ({!! $shop->displayName !!})
            <a href="#" class="btn btn-danger float-right delete-shop-button">Delete Shop</a>
        @endif
    </h1>

    @if ($shop->id && $shop->user_id != Auth::user()->id)
        <div class="alert alert-info">You are editing {!! $shop->user->displayName !!}'s shop as staff.</div>
    @endif

    {!! Form::open(['url' => $shop->id ? 'user-shops/edit/' . $shop->id : 'user-shops/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('name', 'Name') !!}
        {!! Form::text('name', $shop->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('image', 'Shop Image (Optional)') !!} {!! add_help('This image is used on the shop index and on the shop page as a header.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">PNG only, up to 2MB.</div>
        @if ($shop->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('description', 'Description (Optional)') !!}
        {!! Form::textarea('description', $shop->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_active', 1, $shop->id ? $shop->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Open', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the shop is hidden from other players and nothing can be bought from it.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($shop->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($shop->id)
        <h3>Shop Stock</h3>

        @if ($stocks->count())
            <p>Set a price and currency for each item, then mark it visible so other players can buy it. Choosing a removal quantity moves that many back to {{ $shop->user_id == Auth::user()->id ? 'your' : 'the owner\'s' }} inventory.</p>
            {!! Form::open(['url' => 'user-shops/stock/' . $shop->id]) !!}
            @include('widgets._user_shop_select')

            <div class="text-right">
                {!! Form::submit('Edit Stock', ['class' => 'btn btn-primary']) !!}
            </div>
            {!! Form::close() !!}
        @else
            <div class="alert alert-secondary text-center">
                This shop has no stock. Add items from your inventory with <a href="{{ url('inventory/quickstock') }}">Quickstock</a>.
            </div>
        @endif

        <hr>
        <h3>Preview</h3>
        <h1>{{ $shop->name }}</h1>
        <div class="mb-3">
            Owned by {!! $shop->user->displayName !!}
        </div>

        <div class="text-center">
            @if ($shop->has_image)
                <img src="{{ $shop->shopImageUrl }}" style="max-width: 200px !important; max-height: 200px !important;" alt="{{ $shop->name }}" />
            @endif
            <p>{!! $shop->parsed_description !!}</p>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $('.delete-shop-button').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('user-shops/delete') }}/{{ $shop->id }}", 'Delete Shop');
        });
    </script>
@endsection
