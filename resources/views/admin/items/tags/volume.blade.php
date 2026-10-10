<h3>{{ ucfirst(__('volumes.volume')) }} Settings</h3>

<p>Select the {{ __('volumes.volume') }} that you want a user to obtain when they use this item.</p>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label(__('volumes.volume').' Granted') !!}
            {!! Form::select('volume_id', $volumes, $tag->getData()['volume_id'], ['class' => 'form-control selectize', 'placeholder' => 'Select a '.ucfirst(__('volumes.volume'))]) !!}
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.selectize').selectize();
    });
</script>