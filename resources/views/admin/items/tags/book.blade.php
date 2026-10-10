<h3>{{ ucfirst(__('volumes.book')) }} Settings</h3>

<p>Select the {{ __('volumes.book') }} to "grant". When used, the user will be credited all visible
    {{ __('volumes.volumes') }} within this {{ __('volumes.book') }}. </p>
<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label(__('volumes.book') . ' Granted') !!}
            {!! Form::select('book_id', $books, $tag->getData()['book_id'], [
                'class' => 'form-control selectize',
                'placeholder' => 'Select a ' . ucfirst(__('volumes.book')),
            ]) !!}
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('.selectize').selectize();
    });
</script>
