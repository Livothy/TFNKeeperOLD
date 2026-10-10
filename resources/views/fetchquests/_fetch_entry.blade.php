<div class="col-md-6 col-6 mb-3 text-center ">
    @if (Auth::check() && Auth::user()->hasPower('edit_data'))
        <a data-toggle="tooltip" title="[ADMIN] Edit Fetch Quest"
            href="{{ url('admin/data/fetch-quests/edit/') . '/' . $fetch->id }}" class="mb-2 float-right"><i
                class="fas fa-crown"></i></a>
    @endif
    <div class="shop-name mt-1 h2 mb-0">
        {{ $fetch->name }}
    </div>
    @if ($fetch->has_image)
        <div class="shop-image">
            <img src="{{ $fetch->imageUrl }}" style="max-width:50%;" alt="{{ $fetch->name }}" />
        </div>
    @endif


    <h3> {{ $fetch->questgiver_name ? $fetch->questgiver_name : '' }} </h3>
    <div class="shop-text mt-1">
        {!! $fetch->parsed_description !!}
    </div>
    <h4> {{ $fetch->questgiver_name ? $fetch->questgiver_name . ' is currently looking for:' : 'Currently looking for:' }}
    </h4>
    @if ($fetch->fetchItem)
        @if ($fetch->fetchItem->imageUrl)
            <div>
                <a href="{{ $fetch->fetchItem->url }}"><img src="{{ $fetch->fetchItem->imageUrl }}" /></a>
            </div>
        @endif
        <div class="mt-1">
            <a href="{{ $fetch->fetchItem->url }}" class="h5 mb-0"> {{ $fetch->fetchItem->name }}</a>
        </div>
    @else
        <p> {{ $fetch->questgiver_name ? $fetch->questgiver_name . ' isn\'t looking for anything yet.' : 'There is no fetch item.' }}
        </p>
    @endif

    <h4> {{ $fetch->questgiver_name ? $fetch->questgiver_name . ' Is offering you:' : 'Reward:' }} </h4>
    @if (isset($fetch->extras['reward_min_min']) &&
            isset($fetch->extras['reward_min_max']) &&
            isset($fetch->extras['reward_max_min']) &&
            isset($fetch->extras['reward_max_max']) &&
            $fetch->current_min &&
            $fetch->current_max &&
            $fetch->fetchCurrency)
        <div>{!! $fetch->fetchCurrency->display($fetch->current_min) !!} - {!! $fetch->fetchCurrency->display($fetch->current_max) !!}</div>
    @elseif(isset($fetch->extras['reward_min_min']) && isset($fetch->extras['reward_max_min']) && $fetch->fetchCurrency)
        <div>{!! $fetch->fetchCurrency->display($fetch->extras['reward_min_min']) !!} - {!! $fetch->fetchCurrency->display($fetch->extras['reward_max_min']) !!}</div>
    @endif
    @if ($fetch->cooldown)
        <p><strong>Cooldown:</strong> {{ $fetch->cooldown }} minutes. </p>
    @else
        <p><strong>Cooldown:</strong> There is no cooldown. </p>
    @endif

    @if (
        (isset($fetch->extras['reward_min_min']) &&
            isset($fetch->extras['reward_min_max']) &&
            isset($fetch->extras['reward_max_min']) &&
            isset($fetch->extras['reward_max_max']) &&
            $fetch->current_min &&
            $fetch->current_max &&
            $fetch->fetchCurrency &&
            !Auth::user()->fetchCooldown($fetch->id) &&
            $fetch->fetchItem) ||
            (isset($fetch->extras['reward_min_min']) &&
                isset($fetch->extras['reward_max_min']) &&
                $fetch->fetchCurrency &&
                !Auth::user()->fetchCooldown($fetch->id) &&
                $fetch->fetchItem) ||
            ($fetch->rewards && !Auth::user()->fetchCooldown($fetch->id) && $fetch->fetchItem))
        <div class="text-right">
            <a href="#" class="btn btn-sm btn-primary submit-fetch" data-id="{{ $fetch->id }}"></i>Lend a
                Hand!</a>
        </div>
    @elseif(Auth::user()->fetchCooldown($fetch->id))
        <i>You can complete this again {!! pretty_date(Auth::user()->fetchCooldown($fetch->id)) !!}.</i>
    @else
        <p class="text-danger">Something went wrong. Please contact an admin.</p>
    @endif
</div>



<script>
    $(document).ready(function() {
        $('.submit-fetch').on('click', function(e) {
            e.preventDefault();
            loadModal("{{ url('fetch/new/') }}/" + $(this)
                .data('id'), 'Submit Fetch Quest');
        });
    });
</script>
