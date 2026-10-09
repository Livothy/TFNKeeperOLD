@extends('admin.layout')

@section('admin-title')
    {{ $shop->id ? 'Edit' : 'Create' }} Shop
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Shops' => 'admin/data/shops', ($shop->id ? 'Edit' : 'Create') . ' Shop' => $shop->id ? 'admin/data/shops/edit/' . $shop->id : 'admin/data/shops/create']) !!}

    <h1>{{ $shop->id ? 'Edit' : 'Create' }} Shop
        @if ($shop->id)
            ({!! $shop->displayName !!})
            <a href="#" class="btn btn-danger float-right delete-shop-button">Delete Shop</a>
        @endif
    </h1>

    {!! Form::open(['url' => $shop->id ? 'admin/data/shops/edit/' . $shop->id : 'admin/data/shops/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $shop->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Shop Image (Optional)') !!} {!! add_help('This image is used on the shop index and on the shop page as a header.') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: None (Choose a standard size for all shop images)</div>
        @if ($shop->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $shop->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <h5>
        Shop Dialogue Quotes
    </h5>
    <p class="mb-0">
        Optional. You can add individual quotes here: every time a user visits the shop a random one from this data will be selected to be displayed.
    </p>
    <div class="text-right mb-2">
        <a class="btn btn-primary" id="addQuote" href="#">Add Quote</a>
    </div>
    <div id="quotesBody">
        @if ($shop->quotes)
            @foreach ($shop->quotes as $quote)
                <div class="row mb-2">
                    <div class="col">
                        {!! Form::text('quotes[]', $quote, ['class' => 'form-control']) !!}
                    </div>
                    <div class="col-auto text-right">
                        <a href="#" class="btn btn-danger remove-quote"><i class="fas fa-times"></i></a>
                    </div>
                </div>
            @endforeach
        @endif
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_active', 1, $shop->id ? $shop->is_active : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_active', 'Set Active', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the shop will not be visible to regular users.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($shop->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    <div class="row mb-2 quote-row hide">
        <div class="col">
            {!! Form::text('quotes[]', null, ['class' => 'form-control']) !!}
        </div>
        <div class="col-auto text-right">
            <a href="#" class="btn btn-danger remove-quote"><i class="fas fa-times"></i></a>
        </div>
    </div>

    @if ($shop->id)
        <h3>Shop Stock</h3>
        {!! Form::open(['url' => 'admin/data/shops/stock/' . $shop->id]) !!}
        <div class="text-right mb-3">
            <a href="#" class="add-stock-button btn btn-outline-primary">Add Stock</a>
        </div>
        <div id="shopStock">
            @foreach ($shop->stock as $key => $stock)
                @include('admin.shops._stock', ['stock' => $stock, 'key' => $key])
            @endforeach
        </div>
        <div class="text-right">
            {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}
        <div class="" id="shopStockData">
            @include('admin.shops._stock', ['stock' => null, 'key' => 0])
        </div>
    @endif

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            var $shopStock = $('#shopStock');
            var $stock = $('#shopStockData').find('.stock');

            $('.delete-shop-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/shops/delete') }}/{{ $shop->id }}", 'Delete Shop');
            });
            $('.add-stock-button').on('click', function(e) {
                e.preventDefault();

                var clone = $stock.clone();
                $shopStock.append(clone);
                clone.removeClass('hide');
                attachStockListeners(clone);
                refreshStockFieldNames();
            });

            attachStockListeners($('#shopStock .stock'));

            function attachStockListeners(stock) {
                stock.find('.stock-toggle').bootstrapToggle();
                stock.find('.stock-limited').on('change', function(e) {
                    var $this = $(this);
                    if ($this.is(':checked')) {
                        $this.parent().parent().parent().parent().find('.stock-limited-quantity').removeClass('hide');
                    } else {
                        $this.parent().parent().parent().parent().find('.stock-limited-quantity').addClass('hide');
                    }
                });
                stock.find('.remove-stock-button').on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().parent().remove();
                    refreshStockFieldNames();
                });
                stock.find('.card-body [data-toggle=tooltip]').tooltip({
                    html: true
                });
            }

            function refreshStockFieldNames() {
                $('.stock').each(function(index) {
                    var $this = $(this);
                    var key = index;
                    $this.find('.stock-field').each(function() {
                        $(this).attr('name', $(this).data('name') + '[' + key + ']');
                    });
                });
            }

            var $quotes = $('#quotesBody');
            var $quoteRow = $('.quote-row');

            attachQuoteRemoveListener($('#quotesBody .remove-quote'));

            $('#addQuote').on('click', function(e) {
                e.preventDefault();
                var $clone = $quoteRow.clone();
                $quotes.append($clone);
                $clone.removeClass('quote-row hide');
                attachQuoteRemoveListener($clone.find('.remove-quote'));
            });

            function attachQuoteRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
            }
        });
    </script>
@endsection
