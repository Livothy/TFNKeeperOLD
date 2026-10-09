<div class="card mb-3">
    <div class="card-body">
        <div class="mb-1">
            @if (!$raid->is_visible)
                <i class="fas fa-eye-slash" data-toggle="tooltip" title="This {{ __('raids.raid') }} is not visible to regular users."></i>
            @endif
            <a href="{{ $raid->idUrl }}" class="h3 mb-0">
                {!! $raid->name !!}
            </a>
        </div>
        @if ($raid->imageUrl)
            <div class="my-2 rounded" style="background: transparent url('{{ $raid->imageUrl }}') no-repeat center center; background-size: cover; min-height: 125px;"></div>
        @endif
        <div class="row h6 text-center mb-2">
            <div class="col-md">
                <b>Started</b> {!! $raid->start_at ? pretty_date($raid->start_at) : 'an unknown time' !!}.
            </div>
            <div class="col-md">
                @if ($raid->isActive)
                    @if ($raid->end_at && $raid->end_at > Carbon\Carbon::now())
                        <b>Ends</b> {!! 'at ' . format_date($raid->end_at) !!}.
                    @else
                        <b>Ends</b> when {{ __('raids.boss') }} is defeated.
                    @endif
                @else
                    <b>Ended</b> {!! $raid->end_at ? pretty_date($raid->end_at) : 'when ' . __('raids.boss') . ' was defeated' !!}.
                @endif
            </div>
        </div>
        @if (isset($raid->description) && $raid->description)
            <div class="card p-3 mb-3">
                {!! $raid->parsed_description !!}
            </div>
        @endif
        <div class="card py-3 px-2">
            @if ($raid->bosses->count())
                @foreach ($raid->bosses as $boss)
                    <div class="row no-gutters mb-2 {{ !$loop->last ? 'border-bottom pb-2' : '' }}">
                        @if ($boss->imageUrl)
                            <div class="col-md-3 text-center">
                                <img src="{{ $boss->imageUrl }}" class="img-fluid" alt="{{ $boss->name }}">
                            </div>
                        @endif
                        <div class="{{ $boss->imageUrl ? 'col-md-9 pr-md-2' : 'col-12' }}">
                            <div class="h4 mb-1">
                                {!! $boss->displayName !!}
                                @if (isset($boss->health) && $boss->health)
                                    - {{ $boss->health }} HP
                                @else
                                    - HP Unknown
                                @endif
                            </div>
                            @if (isset($boss->description) && $boss->description)
                                <div class="card p-3 mb-1">
                                    {!! $boss->parsed_description !!}
                                </div>
                            @endif
                            <div class="text-right">
                                {{ !$raid->isActive ? 'Was dealt' : 'Has been dealt' }} a total of <b>{{ $boss->damage }} points of damage.</b>
                            </div>
                        </div>
                    </div>
                @endforeach
            @endif
        </div>
        <div class="mt-2">
            <a href="{{ url('raids/leaderboard/' . $raid->id) }}" class="btn btn-secondary d-block text-center">
                Leaderboard
            </a>
        </div>
    </div>
</div>
