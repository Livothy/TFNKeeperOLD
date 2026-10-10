<div class="col-md-4">
    <div class="card character-bio">
        <div class="card-body tab-content">
            @if (Auth::check() && Auth::user()->hasPower('edit_data'))
                <a data-toggle="tooltip" title="[ADMIN] Edit {{ __('volumes.book') }}" href="{{ url('admin/data/volumes/books/edit/') . '/' . $book->id }}" class="mb-2 float-right"><i class="fas fa-crown"></i></a>
            @endif
            <div class="text-center">
                <h3>{!! $book->displayName !!}</h3>
                @if ($book->imageUrl)
                    <img src="{{ $book->imageUrl }}" class="img-fluid mb-2" />
                @endif
                @if ($book->summary)
                    <div class="world-entry-text">
                        <i>{!! $book->summary !!}</i>
                    </div>
                @endif
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
            </div>
            @if ($book->volumes->count())
                <h4>{{ __('volumes.volumes') }}</h4>
                <ul>
                    @foreach ($book->volumes as $volume)
                        <li>{!! $volume->volumeName(Auth::user() ?? null, $isAdmin ?? false) !!}</li>
                    @endforeach
                </ul>
            @endif
            <hr class="my-4 w-75" />
            <div class="text-right">
                <a href="{{ url('world/' . __('volumes.library') . '/' . __('volumes.book') . '/' . $book->id) }}">View full
                    {{ __('volumes.book') }}...</a>
            </div>
        </div>
    </div>
</div>
