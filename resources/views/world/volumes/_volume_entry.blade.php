<div class="col-md-4 text-center">
    <div class="card character-bio">
        <div class="card-body tab-content">
            <h3>{!! $volume->volumeName(Auth::user() ?? null, $isAdmin ?? false) !!}
                @include('world.volumes._volume_icons', ['volume' => $volume])
            </h3>
            @if ($volume->isUnlocked(Auth::user() ?? null))
                @if ($volume->imageUrl)
                    <img src="{{ $volume->imageUrl }}" class="img-fluid mb-2" />
                @endif
            @endif
            @if ($volume->summary)
                <p>{!! $volume->volumeSummary(Auth::user() ?? null, $isAdmin ?? false) !!}</p>
            @endif
            @if ($volume->book)
                <div>
                    <strong>In {{ __('volumes.book') }}: {!! $volume->book->displayName !!}</strong>
                </div>
                <br>
            @endif
            <div class="text-right">
                <a href="{{ url('world/' . __('volumes.library') . '/' . __('volumes.volume') . '/' . $volume->id) }}">View
                    full {{ __('volumes.volume') }}...</a>
            </div>
        </div>
    </div>
</div>
