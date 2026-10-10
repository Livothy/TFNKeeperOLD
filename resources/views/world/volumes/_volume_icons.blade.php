@if ($volume->is_global && $volume->checkGlobal())
    <i class="fas fa-globe mr-1" data-toggle="tooltip" title="A global effort has unlocked this {{ __('volumes.volume') }}."></i>
@elseif($volume->is_global)
    <i class="fas fa-globe mr-1 text-warning" data-toggle="tooltip" title="This {{ __('volumes.volume') }} will be visible for all users when a user obtains it."></i>
@endif
@if (Auth::check() && Auth::user()->hasVolume($volume->id))
    <i class="fas fa-lock-open" data-toggle="tooltip" title="You have this {{ __('volumes.volume') }}!"></i>
@else
    <i class="fas fa-lock" style="opacity:0.5" data-toggle="tooltip" title="You do not have this {{ __('volumes.volume') }}."></i>
@endif
