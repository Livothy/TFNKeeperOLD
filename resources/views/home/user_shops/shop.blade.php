@extends('home.user_shops.layout')

@section('home.user_shops-title')
    {{ $shop->name }}
@endsection

@section('home.user_shops-content')
    {!! breadcrumbs(['User Shops' => 'user-shops/shop-index', e($shop->name) => 'user-shops/shop/' . $shop->id]) !!}

    @if ($shop->canManage(Auth::user()))
        <a data-toggle="tooltip" title="Edit Shop" href="{{ $shop->editUrl }}" class="mb-2 float-right">
            <h3><i class="fas fa-pencil-alt"></i></h3>
        </a>
    @endif

    <h1>
        {{ $shop->name }}
        <a href="{{ url('reports/new?url=' . urlencode($shop->url)) }}"><i class="fas fa-exclamation-triangle fa-xs" data-toggle="tooltip" title="Click here to report this shop." style="opacity: 50%; font-size:0.5em;"></i></a>
    </h1>
    <div class="mb-3">
        Owned by {!! $shop->user->displayName !!}
    </div>

    @if (!$shop->isOpen)
        <div class="alert alert-warning">This shop is closed. Other players can't see it or buy from it.</div>
    @endif

    <div class="text-center">
        @if ($shop->has_image)
            <img src="{{ $shop->shopImageUrl }}" style="max-width:50%;" alt="{{ $shop->name }}" />
        @endif
        <p>{!! $shop->parsed_description !!}</p>
    </div>

    @if (count($items))
        <div class="card mb-3 inventory-category">
            <div class="card-header">
                <ul class="nav nav-tabs card-header-tabs">
                    @foreach ($items as $categoryId => $categoryItems)
                        <li class="nav-item">
                            <a class="nav-link {{ $loop->first ? 'active' : '' }}" id="categoryTab-{{ isset($categories[$categoryId]) ? $categoryId : 'misc' }}" data-toggle="tab" href="#category-{{ isset($categories[$categoryId]) ? $categoryId : 'misc' }}" role="tab">
                                {!! isset($categories[$categoryId]) ? $categories[$categoryId]->name : 'Miscellaneous' !!}
                            </a>
                        </li>
                    @endforeach
                </ul>
            </div>
            <div class="card-body tab-content">
                @foreach ($items as $categoryId => $categoryItems)
                    <div class="tab-pane fade {{ $loop->first ? 'show active' : '' }}" id="category-{{ isset($categories[$categoryId]) ? $categoryId : 'misc' }}">
                        @foreach ($categoryItems->chunk(4) as $chunk)
                            <div class="row mb-3">
                                @foreach ($chunk as $item)
                                    <div class="col-sm-3 col-6 text-center inventory-item" data-id="{{ $item->pivot->id }}">
                                        <div class="mb-1">
                                            <a href="#" class="inventory-stack"><img src="{{ $item->imageUrl }}" alt="{{ $item->name }}" /></a>
                                        </div>
                                        <div>
                                            <a href="#" class="inventory-stack inventory-stack-name"><strong>{{ $item->name }}</strong></a>
                                            <div><strong>Cost:</strong>
                                                @if (isset($currencies[$item->pivot->currency_id]))
                                                    {!! $currencies[$item->pivot->currency_id]->display((int) $item->pivot->cost) !!}
                                                @else
                                                    {{ (int) $item->pivot->cost }}
                                                @endif
                                            </div>
                                            <div>Stock: {{ $item->pivot->quantity }}</div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                @endforeach
            </div>
        </div>
    @else
        <p class="text-center">Nothing is for sale here right now.</p>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.inventory-item').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('user-shops/' . $shop->id) }}/" + $(this).data('id'), 'Purchase Item');
            });
        });
    </script>
@endsection
