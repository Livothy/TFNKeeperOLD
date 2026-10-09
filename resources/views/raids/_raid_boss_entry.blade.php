<div class="card mb-3">
    <div class="card-body">
        <div class="mb-2 row justify-content-between align-items-end">
            <div class="col-md-auto">
                @if (!$boss->is_visible)
                    <i class="fas fa-eye-slash" data-toggle="tooltip" title="This {{ __('raids.raid') . ' ' . __('raids.boss') }} is not visible to regular users."></i>
                @endif
                <a href="{{ $boss->idUrl }}" class="h3 mb-0">
                    {!! $boss->name !!}
                </a>
            </div>
            <div class="h5 mb-0 col-md-auto text-right">
                Encountered in {!! $boss->raid->displayName !!}
            </div>
        </div>
        <div class="row no-gutters">
            @if ($boss->imageUrl)
                <div class="col-md-3 text-center">
                    <img src="{{ $boss->imageUrl }}" class="img-fluid" alt="{{ $boss->name }}">
                </div>
            @endif
            <div class="{{ $boss->imageUrl ? 'col-md-9 pr-md-2' : 'col-12' }}">
                <div class="h4 mb-1">
                    @if (isset($boss->health) && $boss->health)
                        ({{ $boss->health }} HP)
                    @else
                        (HP Unknown)
                    @endif
                </div>
                @if (isset($boss->description) && $boss->description)
                    <div class="card p-3 mb-1">
                        {!! $boss->parsed_description !!}
                    </div>
                @endif
                <div class="text-right">
                    {{ !$boss->raid->isActive ? 'Was dealt' : 'Has been dealt' }} a total of <b>{{ $boss->damage }} points of damage.</b>
                </div>
            </div>
        </div>
    </div>
</div>
