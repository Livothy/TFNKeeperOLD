@extends('world.layout')

@section('title')
    {{ ucfirst(__('volumes.library')) }}
@endsection

@section('content')
    {!! breadcrumbs(['World' => 'world', ucfirst(__('volumes.library')) => 'world/' . __('volumes.library')]) !!}
    <h1>{{ ucfirst(__('volumes.library')) }}</h1>
    <p>Here at the {{ __('volumes.library') }}, you can see all available {{ __('volumes.books') }} as well as the
        {{ __('volumes.volumes') }} contained within them.</p>

    <div>
        {!! Form::open(['method' => 'GET', 'class' => '']) !!}
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::text('name', Request::get('name'), ['class' => 'form-control', 'placeholder' => 'Name']) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::select('bookshelf_id', $bookshelfOptions, Request::get('bookshelf_id'), ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="form-group ml-3 mb-3">
            {!! Form::label('Tags') !!}{!! add_help('Select one or more tags to filter ' . __('volumes.books') . ' by.') !!}
            {!! Form::select('tags[]', $tags, Request::get('tags'), [
                'id' => 'tagList',
                'class' => 'form-control',
                'multiple',
                'placeholder' => 'Tag(s)',
            ]) !!}
        </div>
        <div class="form-inline justify-content-end">
            <div class="form-group ml-3 mb-3">
                {!! Form::select(
                    'sort',
                    [
                        'alpha' => 'Sort Alphabetically (A-Z)',
                        'alpha-reverse' => 'Sort Alphabetically (Z-A)',
                        'newest' => 'Newest First',
                        'oldest' => 'Oldest First',
                    ],
                    Request::get('sort') ?: 'category',
                    ['class' => 'form-control'],
                ) !!}
            </div>
            <div class="form-group ml-3 mb-3">
                {!! Form::submit('Search', ['class' => 'btn btn-primary']) !!}
            </div>
        </div>
        {!! Form::close() !!}
    </div>

    {!! $books->render() !!}
    <div class="row books-row">
        @if ($books->count())
            @foreach ($books as $categoryId => $categorybooks)
                <div class="col-md-12">
                    <div class="card mb-2 text-center">
                        <div class="card-header d-flex flex-wrap no-gutters">
                            <h1 class="col-12">
                                {!! isset($bookshelves[$categoryId]) ? '' . '<img src="' . $bookshelves[$categoryId]->imageUrl . '" style="margin-right: 10px">' . '' : ' ' !!} {!! isset($bookshelves[$categoryId]) ? '' . $bookshelves[$categoryId]->displayName . '' : 'Miscellaneous' !!} {!! isset($bookshelves[$categoryId]) ? '' . '<img src="' . $bookshelves[$categoryId]->imageUrl . '" style="margin-left: 10px">' . '' : ' ' !!}
                            </h1>
                            <div class="col-12 text-center">
                                {!! isset($bookshelves[$categoryId]) ? '' . $bookshelves[$categoryId]->summary . '' : ' ' !!}
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card-body" id="{!! isset($bookshelves[$categoryId]) ? str_replace(' ', '', $bookshelves[$categoryId]->name) : 'miscellaneous' !!}">
                    @foreach ($categorybooks->chunk(4) as $chunk)
                        <div class="row mb-3">
                            @foreach ($chunk as $bookId => $book)
                                @include('world.volumes._book_entry', [
                                    'book' => $book,
                                    'isAdmin' => false,
                                ])
                            @endforeach
                        </div>
                    @endforeach
                </div>
            @endforeach
        @endif
    </div>
    {!! $books->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $books->total() }} result{{ $books->total() == 1 ? '' : 's' }} found.
    </div>
@endsection
@section('scripts')
    <script>
        $(document).ready(function() {
            $('#tagList').selectize({
                maxItems: 10
            });
        });
    </script>
@endsection
