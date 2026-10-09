@extends('shops.layout')

@section('shops-title')
    {{ $shop->name }}
@endsection

@section('shops-content')
    <x-admin-edit title="Shop" :object="$shop" />
    {!! breadcrumbs(['Shops' => 'shops', $shop->name => $shop->url]) !!}

    <h1>
        {{ $shop->name }}
    </h1>

    <div class="text-center">
        @if ($shop->has_image)
            <img src="{{ $shop->shopImageUrl }}" style="max-width:100%" alt="{{ $shop->name }}" />
        @endif
        <p>{!! $shop->parsed_description !!}</p>
        @if (isset($shop->quotes) && $shop->quotes)
            <div class="quote-display card card-body mb-2">
                <span class="quote"></span>
            </div>
        @endif
    </div>

    @foreach ($items as $categoryId => $categoryItems)
        @php
            $visible = '';
            if ($categoryId && !$categories[$categoryId]->is_visible) {
                $visible = '<i class="fas fa-eye-slash mr-1"></i>';
            }
        @endphp
        <div class="card mb-3 inventory-category">
            <h5 class="card-header inventory-header">
                {!! isset($categories[$categoryId]) ? '<a href="' . $categories[$categoryId]->searchUrl . '">' . $visible . $categories[$categoryId]->name . '</a>' : 'Miscellaneous' !!}
            </h5>
            <div class="card-body inventory-body">
                @foreach ($categoryItems->chunk(4) as $chunk)
                    <div class="row mb-3">
                        @foreach ($chunk as $item)
                            <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $item->pivot->id }}">
                                <div class="mb-1">
                                    <a href="#" class="inventory-stack">
                                        <img src="{{ $item->imageUrl }}" alt="{{ $item->name }}" />
                                    </a>
                                </div>
                                <div>
                                    <a href="#" class="inventory-stack inventory-stack-name">
                                        <strong>{{ $item->name }}</strong>
                                    </a>
                                    <div>
                                        <strong>Cost: </strong> {!! $currencies[$item->pivot->currency_id]->display($item->pivot->cost) !!}
                                    </div>
                                    @if ($item->pivot->is_limited_stock)
                                        <div>
                                            Stock: {{ $item->pivot->quantity }}
                                        </div>
                                    @endif
                                    @if ($item->pivot->purchase_limit)
                                        <div class="text-danger">
                                            Max {{ $item->pivot->purchase_limit }} per user
                                        </div>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@endsection

@section('scripts')
    <script>
        $(document).ready(function() {
            $('.inventory-item').on('click', function(e) {
                e.preventDefault();

                loadModal("{{ url('shops/' . $shop->id) }}/" + $(this).data('id'), 'Purchase Item');
            });

            @if (isset($shop->quotes) && $shop->quotes)
                var $quoteDisplay = $(".quote-display .quote");
                var speed = 25;

                function typeWriterEffect(text, i) {
                    if (i < (text.length)) {
                        $quoteDisplay.html(text.substring(0, i + 1));

                        setTimeout(function() {
                            typeWriterEffect(text, i + 1)
                        }, speed);
                    }
                }

                function displayQuote(i, text) {
                    if (typeof text[i] == 'undefined') {
                        setTimeout(function() {
                            displayQuote(0, text);
                        }, 2000);
                    }

                    if (i < text[i].length) {
                        typeWriterEffect(text[i], 0);
                    }
                }

                displayQuote(0, ["{!! $shop->randomQuote !!}"]);
            @endif
        });
    </script>
@endsection
