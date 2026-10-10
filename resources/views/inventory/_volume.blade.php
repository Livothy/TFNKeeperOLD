<li class="list-group-item">
    <a class="card-title h5 collapse-title" data-toggle="collapse" href="#Volumeform"> Use
        {{ ucfirst(__('volumes.volume')) }}</a>
    <div id="Volumeform" class="collapse">
        {!! Form::hidden('tag', $tag->tag) !!}
        @php
            $volume = \App\Models\Volume\Volume::visible()->find($tag->getData()['volume_id']);
        @endphp
        @if (!isset($volume))
        <div class="alert alert-warning">
            Invalid {{ __('volumes.volume') }} or invisible {{ __('volumes.volume') }} set. Contact an admin.
            </div>
        @else
            <p>Using this item will credit {!! $volume->displayName !!} to your {{ __('volumes.volume') }} collection.</p>
            @if (Auth::user()->hasVolume($volume->id))
                <div class="alert alert-warning"> You already have collected this {{ __('volumes.volume') }}.</div>
            @else
                <p>This action is not reversible. Are you sure you want to use this item?</p>
            @endif

            @if (!Auth::user()->hasVolume($volume->id))
                <div class="text-right">
                    {!! Form::button('Use', [
                        'class' => 'btn btn-primary',
                        'name' => 'action',
                        'value' => 'act',
                        'type' => 'submit',
                    ]) !!}
                </div>
            @endif
        @endif
    </div>
</li>
