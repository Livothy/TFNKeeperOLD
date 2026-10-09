{!! Form::open(['url' => $isMyo ? 'admin/myo/' . $character->id . '/stats' : 'admin/character/' . $character->slug . '/stats']) !!}
@if ($isMyo)
    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $character->name, ['class' => 'form-control']) !!}
    </div>
@else
    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Character Category') !!}
                {!! Form::select('character_category_id', $categories, $character->category->id, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Number') !!} {!! add_help('This number helps to identify the character and should preferably be unique either within the category, or among all characters.') !!}
                {!! Form::text('number', $number, ['class' => 'form-control mr-2', 'id' => 'number']) !!}
            </div>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('Character Code') !!} {!! add_help('This code identifies the character itself. This must be unique among all characters (as it\'s used to generate the character\'s page URL).') !!}
        {!! Form::text('slug', $character->slug, ['class' => 'form-control', 'id' => 'code']) !!}
    </div>
@endif

<div class="alert alert-info">
    These are displayed on the character's profile, but don't have any effect on site functionality except for the following:
    <ul>
        <li>If all switches are off, the character cannot be transferred by the user (directly or through trades).</li>
        <li>If a transfer cooldown is set, the character also cannot be transferred by the user (directly or through trades) until the cooldown is up.</li>
    </ul>
</div>
<div class="form-group">
    {!! Form::checkbox('is_giftable', 1, $character->is_giftable, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_giftable', 'Is Giftable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_tradeable', 1, $character->is_tradeable, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
    {!! Form::label('is_tradeable', 'Is Tradeable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::checkbox('is_sellable', 1, $character->is_sellable, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'resellable']) !!}
    {!! Form::label('is_sellable', 'Is Resellable', ['class' => 'form-check-label ml-3']) !!}
</div>
<div class="form-group">
    {!! Form::label('Resale Value') !!} {!! add_help('This value is publicly displayed on the character\'s page. It\'s hidden if zero or lower.') !!}
    {!! Form::text('sale_value', $character->sale_value, ['class' => 'form-control']) !!}
</div>
<div class="form-group">
    {!! Form::label('On Transfer Cooldown Until (Optional)') !!}
    <div class="input-group">
        {!! Form::text('transferrable_at', $character->transferrable_at, ['class' => 'form-control datepickeralt']) !!}
        <div class="input-group-append">
            <a class="btn btn-info collapsed" href="#collapsedt" data-toggle="collapse"><i class="fas fa-calendar-alt"></i></a>
        </div>
    </div>
    <div class="collapse datepicker" id="collapsedt"></div>
</div>

<!-- Design Hub -->
<div class="form-group">
    {!! Form::checkbox('is_chimera', 0, $is_chimera ? 1 : 0, ['class' => 'form-check-input', 'data-toggle' => 'toggle', 'id' => 'is_chimera']) !!}
    {!! Form::label('is_chimera', 'Is Chimera', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If this character has the Chimera modifier, then this will give you the ability to customize both genomes.') !!}
</div>

<div class="row">
    <div class="col-md-6">
        <div class="form-group">
            {!! Form::label('Base Color') !!}
            {!! Form::select('base', $bases, $is_chimera ? explode('|', $character->base)[0] : $character->base, ['class' => 'form-control']) !!}
        </div>
    </div>
    <div class="col-md-6">
        <div class="form-group" connect="is_chimera" {!! $is_chimera ? '' : 'style="display:none"' !!}>
            {!! Form::label('Secondary Base Color') !!}
            {!! Form::select('secondary_base', $bases, $is_chimera ? explode('|', $character->base)[1] : $character->base, ['class' => 'form-control']) !!}
        </div>
    </div>
</div>

<div class="form-group">
    {!! Form::label('Markings') !!}
    {!! add_help('Select markings applicable to character') !!}
    <div><a href="#" class="btn btn-primary mb-2" id="add-marking">Add Marking</a></div>
    <div id="markingList">
        @foreach ($characterMarkings as $marking)
            <div class="d-flex mb-2 marking-row">
                {!! Form::select('marking_id[]', $markings, $marking->marking_id, ['class' => 'form-control mr-2 marking-select', 'placeholder' => 'Select Marking']) !!}
                <div class="form-group" style="width:50%">
                    <select name="is_dominant[]" id="is_dominant[]" class="form-control markingType" placeholder="Select Type...">
                        <option value="" data-code="">Select Type...</option>
                        <option value="0" data-code="0" {{ $marking->is_dominant == 0 ? 'selected' : '' }}>Recessive</option>
                        <option value="1" data-code="1" {{ $marking->is_dominant == 1 ? 'selected' : '' }}>Dominant</option>
                    </select>
                </div>
                <div class="form-group mx-2" connect="is_chimera" style="width:50%;{{ $is_chimera ? '' : 'display:none;' }}">
                    <select name="side_id[]" id="side_id" class="form-control" placeholder="Select Side...">
                        <option value="" data-code="">Select Side...</option>
                        <option value="0" data-code="0" {{ $marking->data == 0 ? 'selected' : '' }}>Side 1</option>
                        <option value="1" data-code="1" {{ $marking->data == 1 ? 'selected' : '' }}>Side 2</option>
                    </select>
                </div>
                <a href="#" class="remove-marking btn btn-danger mb-2">×</a>
            </div>
        @endforeach
    </div>
    <div class="marking-row hide mb-2">
        {!! Form::select('marking_id[]', $markings, null, ['class' => 'form-control mr-2 marking-select', 'placeholder' => 'Select Marking']) !!}
        <div class="form-group" style="width:50%">
            <select name="is_dominant[]" id="is_dominant[]" class="form-control markingType" placeholder="Select Type...">
                <option value="" data-code="">Select Type...</option>
                <option value="0" data-code="0">Recessive</option>
                <option value="1" data-code="1">Dominant</option>
            </select>
        </div>
        <div class="form-group mx-2" connect="is_chimera" style="width:50%;display:none;">
            <select name="side_id[]" id="side_id" class="form-control" placeholder="Select Side...">
                <option value="" data-code="">Select Side...</option>
                <option value="0" data-code="0">Side 1</option>
                <option value="1" data-code="1">Side 2</option>
            </select>
        </div>
        <a href="#" class="remove-marking btn btn-danger mb-2">×</a>
    </div>
</div>

<div class="text-right">
    {!! Form::submit('Edit', ['class' => 'btn btn-primary']) !!}
</div>
{!! Form::close() !!}

@include('widgets._datetimepicker_js', ['dtinline' => 'datepickeralt', 'dtvalue' => $character->transferrable_at])
@include('widgets._markingbases_js', [])
