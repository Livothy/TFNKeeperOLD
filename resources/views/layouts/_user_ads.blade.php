@php
    $randomAd = \App\Models\UserAds::with('user')
        ->inRandomOrder()
        ->first();
@endphp
<div class="row">
    <div class="col-12">
        <div class="text-center">
            @if ($randomAd)
                <i class="fas fa-bullhorn"></i> <strong>{!! $randomAd->user->displayName !!}:</strong>
                {{ $randomAd->text }}
            @else
            @endif
        </div>
    </div>
</div>
