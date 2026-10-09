@extends('layouts.app')


@section('title')
    Bases
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'design-hub', 'Bases' => 'Bases']) !!}
    <h1>Bases</h1>
    <a class="btn btn-primary" href="/design-hub/" role="button">Back to Design Hub</a>

    <div class="card rounded my-4">
        <div class="card-body">
            <input type="text" placeholder="Search base coats..." class="searchBar rounded mb-4 form-control" data-id="baseSearch" />

            <div class="d-flex flex-wrap justify-content-between searchContent base-card-container" data-id="baseSearch">
                @if ($bases)
                    @foreach ($bases as $base)
                        <div class="card rounded mb-4">
                            <div class="card-body">
                                <div class="text-center">
                                    <?php
                                    $image_url = file_exists($base->imageDirectory . '/' . $base->imageFileName) ? asset($base->imageDirectory . '/' . $base->imageFileName) : null;
                                    ?>
                                    <img src="{{ $image_url ?? '/images/account.png' }}" class="img-fluid mb-3" />
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <h4 class="mb-0 flex-grow-1" style="width:100%;"><span class="badge badge-secondary">{{ $base->id }}</span> {{ $base->name }} <span style="text-transform:none;"
                                            class="font-weight-normal">({{ $base->code }})</span></h4>
                                    @if ($image_url)
                                        <a href="{{ $image_url }}" class="btn btn-secondary flex-shrink-1" download><i class="fas fa-download"></i></a>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </div>

@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.searchBar').on('keyup', function(e) {
                var sTerm = $(this).val().toLowerCase();
                var type = $(this).attr('data-id');
                console.log(type)
                console.log(sTerm);
                $('.searchContent[data-id="' + type + '"]').children().each(function() {
                    console.log($(this))
                    $(this).toggle($(this).text().toLowerCase().indexOf(sTerm) > -1);
                });
            });
        });
    </script>
@endsection
