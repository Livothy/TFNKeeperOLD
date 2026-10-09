@if ($bossImage->id)
    <div class="alert alert-warning text-center">
        You are editing an <b>existing image</b> for <b>{!! $raidBoss->name !!}</b>.
    </div>
    @if ($bossImage->imageUrl)
        <div class="text-center mb-2">
            <img src="{{ $bossImage->imageUrl }}" class="img-fluid" alt="Image of {{ $raidBoss->name }}">
        </div>
    @endif

    {!! Form::open(['url' => 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/' . $raidBoss->id . '/image/edit/' . $bossImage->id, 'files' => true]) !!}
    <div class="form-group">
        {!! Form::label('Change ' . ucfirst(__('raids.boss')) . ' Image') !!}
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Accepted filetypes: png, gif, webp.</div>
    </div>

    <div class="alert alert-info">
        You can optionally define at what amount of health will this image begin to display at.<br>
        There are two types of thresholds: <b>percentage</b> indicates the health threshold number inputted should be a percentage of the {{ __('raids.boss') }}'s total health value (i.e. 25% of 1000 -> begin displaying at 250 HP), or a <b>specific
            amount</b> which is a static value (i.e. 555 HP out of 1000).<br><br>
        If you want this image to display from the beginning at full health, set the threshold type to <b>percentage</b> and the health threshold to <b>100</b>.
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('threshold_type', 'Threshold Type') !!}
                {!! Form::select('threshold_type', ['percent' => 'Percentage', 'amount' => 'Specific Amount'], $bossImage->threshold_type ?? 'percent', ['class' => 'form-control', 'placeholder' => 'Select type...']) !!}
            </div>
        </div>

        <div class="col-md">
            <div class="form-group">
                {!! Form::label('health_threshold', 'Health Threshold') !!}
                {!! Form::number('health_threshold', $bossImage->health_threshold ?? null, ['class' => 'form-control', 'placeholder' => 'Enter a number...', 'step' => 1]) !!}
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit('Edit Image', ['class' => 'btn btn-success']) !!}
    </div>
    {!! Form::close() !!}
@else
    <div class="alert alert-danger text-center">
        You are creating a <b>new image</b> for <b>{!! $raidBoss->name !!}</b>.
    </div>
    {!! Form::open(['url' => 'admin/data/' . __('raids.raid') . '-' . __('raids.bosses') . '/' . $raidBoss->id . '/image/create', 'files' => true]) !!}
    <div class="form-group">
        {!! Form::label(ucfirst(__('raids.boss')) . ' Image') !!} <b>(Required)</b>
        <div class="custom-file">
            {!! Form::label('image', 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Accepted filetypes: png, gif, webp.</div>
    </div>

    <div class="alert alert-info">
        You can optionally define at what amount of health will this image begin to display at.<br>
        There are two types of thresholds: <b>percentage</b> indicates the health threshold number inputted should be a percentage of the {{ __('raids.boss') }}'s total health value (i.e. 25% of 1000 -> begin displaying at 250 HP), or a <b>specific
            amount</b> which is a static value (i.e. 555 HP out of 1000).<br><br>
        If you want this image to display from the beginning at full health, set the threshold type to <b>percentage</b> and the health threshold to <b>100</b>.
    </div>

    <div class="row">
        <div class="col-md">
            <div class="form-group">
                {!! Form::label('threshold_type', 'Threshold Type') !!}
                {!! Form::select('threshold_type', ['percent' => 'Percentage', 'amount' => 'Specific Amount'], null, ['class' => 'form-control', 'placeholder' => 'Select type...']) !!}
            </div>
        </div>

        <div class="col-md">
            <div class="form-group">
                {!! Form::label('health_threshold', 'Health Threshold') !!}
                {!! Form::number('health_threshold', null, ['class' => 'form-control', 'placeholder' => 'Enter a number...', 'step' => 1]) !!}
            </div>
        </div>
    </div>

    <div class="text-right">
        {!! Form::submit('Add Image', ['class' => 'btn btn-success']) !!}
    </div>
    {!! Form::close() !!}
@endif

<script>
    $(document).ready(function() {
        bsCustomFileInput.init();
    });
</script>
