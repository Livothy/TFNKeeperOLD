@if ($raid->status >= 2)
    <div class="text-center">
        @if ($raid->status == 2)
            This {{ __('raids.boss') }} has already been defeated!
        @else
            This {{ __('raids.boss') }} has concluded.
        @endif
    </div>
    <div class="alert alert-{{ $raid->logs()->where('user_id', Auth::user()->id)->first()? 'success': 'primary' }} text-center mt-2">
        @if ($raid->logs()->where('user_id', Auth::user()->id)->first())
            <b>You participated in this {{ __('raids.raid') }}!</b>
            @if ($raid->distributed_at && $raid->distributed_at <= Carbon\Carbon::now())
                Rewards were distributed {!! pretty_date($raid->distributed_at) !!}.
            @else
                Please wait for rewards to be distributed.
            @endif
            @if ($raid->userDamage(Auth::user() ?? null) && $raid->userDamage(Auth::user() ?? null) >= 0)
                <br>
                You have dealt a total of <b>{{ $raid->userDamage(Auth::user() ?? null) }} damage</b> to this {{ __('raids.raid') . ' ' . __('raids.boss') }}.
            @endif
        @else
            You didn't participate in this {{ __('raids.raid') }}.
        @endif
    </div>
@elseif ($raid->status == 1)
    @if ($raid->damage)
        <div class="text-center">
            Requires <b>{{ $raid->attackAsset()['quantity'] }} {!! $raid->attackAsset()['asset']->displayName !!} ({{ $raid->damage['type'] }})</b> to attack.
        </div>
        @if ($raid->attackAsset()['asset']->imageUrl || $raid->attackAsset()['asset']->currencyImageUrl || $raid->attackAsset()['asset']->currencyIconUrl)
            <div class="text-center my-2">
                <img src="{{ $raid->attackAsset()['asset']->imageUrl ?? ($raid->attackAsset()['asset']->currencyImageUrl ?? ($raid->attackAsset()['asset']->currencyIconUrl ?? null)) }}" alt="{{ $raid->attackAsset()['asset']->name }}"
                    class="img-fluid p-2 bg-secondary rounded-circle">
            </div>
        @endif
        <div class="text-center">
            @if (isset($raid->damage['max']))
                Between <b>{{ $raid->damage['base'] }}</b> to <b>{{ $raid->damage['max'] }}</b> points of damage per attack.
            @else
                <b>{{ $raid->damage['base'] }}</b> points of damage per attack.
            @endif
        </div>
        @if ($raid->userDamage(Auth::user() ?? null) && $raid->userDamage(Auth::user() ?? null) >= 0)
            <div class="text-center">
                You have dealt <b>{{ $raid->userDamage(Auth::user() ?? null) }} damage</b> to this {{ __('raids.raid') . ' ' . __('raids.boss') }} so far.
            </div>
        @endif

        @if (isset($raid->damage['type']) && $raid->damage['type'] == 'Currency')
            <div class="mt-2">Your Currencies</div>
            @foreach (Auth::user()->getCurrencies(1) as $currency)
                <span class="mx-1">
                    {!! $currency->display($currency->quantity) !!}
                </span>
                @if (!$loop->last)
                    <span class="mx-1">|</span>
                @endif
            @endforeach
        @endif

        @if ($raid->canAttack(Auth::user() ?? null))
            {!! Form::open(['url' => __('raids.raids') . '/attack/' . $raid->id . '/' . __('raids.boss') . '/' . $boss->id, 'class' => 'mt-2']) !!}
            <div class="text-center">
                {!! Form::submit('Attack!', ['class' => 'btn btn-primary font-weight-bold']) !!}
            </div>
            {!! Form::close() !!}
        @else
            <div class="alert alert-danger text-center mt-2">
                You lack the requirements to make an attack.
            </div>
        @endif
    @endif
@endif
