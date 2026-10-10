<div class="card mb-3">
    <div class="card-body">
        @if (Auth::check() && Auth::user()->hasPower('edit_data'))
            <a data-toggle="tooltip" title="[ADMIN] Edit {{ __('volumes.book') }}" href="{{ url('admin/data/volumes/books/edit/') . '/' . $book->id }}" class="mb-2 float-right"><i class="fas fa-crown"></i></a>
        @endif
        <div class="row world-entry">
            @if ($book->imageUrl)
                <div class="col-md-3 world-entry-image"><a href="{{ $book->imageUrl }}" data-lightbox="entry" data-title="{{ $book->name }}"><img src="{{ $book->imageUrl }}" class="world-entry-image" alt="{{ $book->name }}" /></a></div>
            @endif
            <div class="{{ $book->imageUrl ? 'col-md-9' : 'col-12' }}">
                <h3>{!! $book->displayName !!}</h3>
                @if ($book->tags->count())
                    <li class="list-group-item">
                        <strong>Tags:</strong>
                        @foreach ($book->tags as $tag)
                            {!! $tag->displayName !!}{{ !$loop->last ? ',' : '' }}
                        @endforeach
                    </li>
                @endif
                @if ($book->authors->count())
                    <li class="list-group-item">
                        <strong>Authors:</strong>
                        @foreach ($book->authors as $author)
                            {!! $author->displayLink() !!}
                            @if ($author->credit_type)
                                ({{ $author->credit_type }})
                            @endif
                            {{ !$loop->last ? ',' : '' }}
                        @endforeach
                    </li>
                @endif
                <hr class="my-4 w-75" />
                <div class="world-entry-text">
                    {!! $book->parsed_description !!}
                </div>
            </div>
        </div>
    </div>
</div>
