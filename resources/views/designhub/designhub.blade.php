@extends('layouts.app')


@section('title')
    Design Hub
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'Design Hub']) !!}
    <h1>Design Hub</h1>

    @if ($dh_start)
        <div class="card rounded mb-4">
            <div class="card-header">
                {!! $dh_start->title !!}
            </div>
            <div class="card-body">
                {!! $dh_start->text !!}
            </div>
        </div>
    @elseif(!$dh_start && Auth::user()->isStaff)
        <div class="alert alert-warning" role="alert">
            Page for design hub start is missing. Please run <code>php artisan add-text-pages</code>. This message is only visible to staff.
        </div>
    @endif

    <div class="row mb-4">
        <div class="col d-flex"><a href="#markings" class="btn btn-primary">Markings</a></div>
        <div class="col d-flex"><a href="#traitguides" class="btn btn-primary">Trait Guides</a></div>
        <div class="col d-flex"><a href="#importtemplates" class="btn btn-primary">Import Templates</a></div>
        <div class="col d-flex"><a href="{{ url('design-hub/base-coats') }}" class="btn btn-primary">Base Coats</a></div>
    </div>

    <div class="card rounded mb-4" id="markings">
        <div class="card-header">
            Markings
        </div>
        <div class="card-body">
            <input type="text" placeholder="Search markings by name or code..." class="searchBar rounded mb-4 form-control" data-id="markingSearch" />

            @if ($rarity_list)
                @foreach ($markings as $rarityId => $markingItems)
                    <div class="card rounded mb-4">
                        <div class="card-header"><span class="rarity-indicator" style="background-color:#{{ $rarity_list[$rarityId]->color }}"></span> {{ $rarity_list[$rarityId]->name }} Markings</div>
                        <div class="card-body">
                            <div class="d-flex flex-wrap justify-content-between searchContent" data-id="markingSearch">
                                @foreach ($markingItems as $marking)
                                    @include('designhub._entry', [
                                        'imageUrl' => file_exists($marking->imageDirectory . '/' . $marking->imageFileName) ? asset($marking->imageDirectory . '/' . $marking->imageFileName) : '/images/account.png',
                                        'name' => $marking->name . ' (' . $marking->recessive . '/' . $marking->dominant . ')',
                                        'description' => $marking->short_description,
                                        'url' => 'design-hub/marking/' . $marking->slug,
                                    ])
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
    </div>

    <div class="card rounded mb-4" id="traitguides">
        <div class="card-header">
            Trait Guides
        </div>
        <div class="card-body">
            <input type="text" placeholder="Search traits..." class="searchBar rounded mb-4 form-control" data-id="traits" />
            <div class="card rounded mb-4">
                <div class="card-header">Trait Guides</div>
                <div class="card-body">
                    <div class="d-flex flex-wrap justify-content-between searchContent" data-id="traits">
                        @if ($trait_lists)
                            {!! $trait_lists->render() !!}
                            @foreach ($trait_lists as $trait)
                                <?php
                                $text = $trait->description;
                                $short_description = '';
                                
                                if ($text) {
                                    $dom = new DOMDocument();
                                    libxml_use_internal_errors(true);
                                    $dom->loadHTML($text);
                                    libxml_clear_errors();
                                
                                    $paragraphs = $dom->getElementsByTagName('p');
                                
                                    if ($paragraphs->length > 0) {
                                        $short_description = $paragraphs->item(0)->textContent; // Get the text content of the first <p> tag
                                    }
                                }
                                ?>

                                @include('designhub._entry', [
                                    'imageUrl' => $trait->imageUrl ?? '/images/account.png',
                                    'name' => $trait->name,
                                    'description' => $short_description ?? '',
                                    'url' => $trait->getUrlAttribute() ?: '/world/traits?name=' . $trait->name,
                                ])
                            @endforeach
                            {!! $trait_lists->render() !!}
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="card rounded mb-4" id="importtemplates">
        <div class="card-header">
            Import Templates
        </div>
        <div class="card-body">
            @foreach ($specieses as $species)
                <div class="card rounded mb-4">
                    <div class="card-header">{{ $species->name }} Templates</div>
                    <div class="card-body">
                        <div class="d-flex flex-wrap flex-column justify-content-between">
                            @foreach ($subtypes as $subtype)
                                @if ($subtype->species_id === $species->id)
                                    <div class="card item flex-fill my-2">
                                        <div class="card-body">
                                            @if ($subtype->subtypeImageUrl)
                                                <a href="{{ $subtype->subtypeImageUrl }}" data-lightbox="entry" data-title="{{ $subtype->name }}">
                                                    <img src="{{ $subtype->subtypeImageUrl }}" class="world-entry-image" alt="{{ $subtype->name }}" />
                                                </a>
                                            @endif
                                            <h3>{{ $subtype->name }}</h3>
                                            <p>{!! $subtype->description !!}</p>
                                        </div>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>

    @if ($dh_end)
        <div class="card rounded mb-4">
            <div class="card-header">
                {!! $dh_end->title !!}
            </div>
            <div class="card-body">
                {!! $dh_end->text !!}
            </div>
        </div>
    @elseif(!$dh_end && Auth::user()->isStaff)
        <div class="alert alert-warning" role="alert">
            Page for design hub end is missing. Please run <code>php artisan add-text-pages</code>. This message is only visible to staff.
        </div>
    @endif


@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.searchBar').on('keyup', function(e) {
                var sTerm = $(this).val().toLowerCase();
                var type = $(this).attr('data-id');
                $('.searchContent[data-id="' + type + '"]').children().each(function() {
                    $(this).toggle($(this).text().toLowerCase().indexOf(sTerm) > -1);
                });
            });
        });
    </script>
@endsection
