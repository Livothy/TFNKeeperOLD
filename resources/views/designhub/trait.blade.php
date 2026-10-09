@extends('layouts.app')


@section('title')
    Design Hub
@endsection


@section('content')
    {!! breadcrumbs(['Design Hub' => 'design-hub', $trait->name => $trait->url]) !!}
    <x-admin-edit title="Trait" :object="$trait" />
    <h1>{{ $trait->name }}</h1>
    <p>{{ $trait->short_description }}</p>

    <div class="site-page-content parsed-text">
        {!! $trait->description !!}
    </div>
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
