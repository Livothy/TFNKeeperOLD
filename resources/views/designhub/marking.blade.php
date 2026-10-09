@extends('layouts.app')


@section('title')
    Design Hub
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'design-hub', $marking->name => $marking->url]) !!}
    <x-admin-edit title="Marking" :object="$marking" />
    <h1>{{ $marking->name }} <span style="text-transform:none;">({{ $marking->recessive }}/{{ $marking->dominant }})</span></h1>
    <p>{{ $marking->short_description }}</p>

    <div class="site-page-content parsed-text">
        {!! $marking->description !!}
    </div>

    @if (count($carriers) > 0)
        <div class="site-page-content parsed-text">
            <div class="card mb-4 rounded mt-4">
                <h5 class="card-header">Carriers</h5>
                <div class="card-body">
                    @foreach ($carriers as $carrier)
                        <div class="card carrier-card mb-4 rounded">
                            <h5 class="card-header">{{ $carrier->name }}</h5>
                            <div class="card-body">
                                {!! $carrier->description !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('img').each(function(i, e) {
                $(this).wrap('<a href="' + $(this).attr('src') + '" data-lightbox="entry" ></a>');
            });
        });
    </script>
@endsection
