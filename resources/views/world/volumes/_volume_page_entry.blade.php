<div class="card mb-3">
    <div class="card-body">
        @if (Auth::check() && Auth::user()->hasPower('edit_data'))
            <a data-toggle="tooltip" title="[ADMIN] Edit {{ __('volumes.volume') }}" href="{{ url('admin/data/volumes/edit/') . '/' . $volume->id }}" class="mb-2 float-right"><i class="fas fa-crown"></i></a>
        @endif
        <div class="row world-entry">
            @if ($volume->isUnlocked(Auth::user() ?? null))
                @if ($volume->imageUrl)
                    <div class="col-md-3 world-entry-image"><a href="{{ $volume->imageUrl }}" data-lightbox="entry" data-title="{{ $volume->name }}"><img src="{{ $volume->imageUrl }}" class="world-entry-image" alt="{{ $volume->name }}" /></a></div>
                @endif
            @endif
            <div class="{{ $volume->imageUrl ? 'col-md-9' : 'col-12' }}">
                <h3>{!! $volume->volumeName(Auth::user() ?? null, $isAdmin ?? false) !!}
                    @include('world.volumes._volume_icons', ['volume' => $volume])
                </h3>
                @if ($volume->book)
                    <div>
                        <strong>In {{ __('volumes.book') }}: {!! $volume->book->displayName !!}</strong>
                    </div>
                @endif
                <hr class="my-4 w-75" />
                <div class="world-entry-text">
                    {!! $volume->volumeDesc(Auth::user() ?? null, $isAdmin ?? false) !!}
                </div>
                <hr class="my-4 w-75" />
            </div>
            <div class="col text-center">
                @if ($volume->prevNextVolume('previous'))
                    <a href="{{ $volume->prevNextVolume('previous')->idUrl }}"><img src="{{ $volume->book->nextImageUrl }}" alt="previous page" /></a>
                @endif
            </div>
            <div class="col text-center">
                @if ($volume->prevNextVolume('next'))
                    <a href="{{ $volume->prevNextVolume('next')->idUrl }}"><img src="{{ $volume->book->nextImageUrl }}" alt="next page" style="transform: scaleX(-1);" /></a>
                @endif
            </div>
        </div>
    </div>
</div>
