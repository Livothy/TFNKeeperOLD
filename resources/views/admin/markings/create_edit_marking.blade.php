@extends('admin.layout')

@section('admin-title')
    {{ $marking->id ? 'Edit' : 'Create' }} Marking
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Markings' => 'admin/data/markings', ($marking->id ? 'Edit' : 'Create') . ' Marking' => $marking->id ? 'admin/data/markings/edit/' . $marking->id : 'admin/data/markings/create']) !!}

    <h1>{{ $marking->id ? 'Edit' : 'Create' }} Marking
        @if ($marking->id)
            <a href="#" class="btn btn-danger float-right delete-marking-button">Delete Marking</a>
        @endif
    </h1>

    {!! Form::open(['url' => $marking->id ? 'admin/data/markings/edit/' . $marking->id : 'admin/data/markings/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $marking->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-6">
            <div class="form-group">
                {!! Form::label('Slug') !!}
                {!! Form::text('slug', $marking->slug, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Preview Image (Optional)') !!} {!! add_help('This image is used only on design hub page.') !!}
        <div class="custom-file">
            {!! Form::label('image', file_exists($marking->imageDirectory . '/' . $marking->imageFileName) ? $marking->imageFileName : 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if (file_exists($marking->imageDirectory . '/' . $marking->imageFileName))
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('Species Restriction (Optional)') !!}
                {!! Form::select('species_id', $specieses, $marking->species_id, ['class' => 'form-control', 'id' => 'species']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('Rarity') !!}
                {!! Form::select('rarity_id', $rarities, $marking->rarity_id, ['class' => 'form-control', 'id' => 'rarities']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group" id="recessive">
                {!! Form::label('Recessive Gene Code (Optional)') !!} {!! add_help('Recessive gene code for this marking.') !!}
                {!! Form::text('recessive', $marking->recessive, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group" id="dominant">
                {!! Form::label('Dominant Gene Code (Optional)') !!} {!! add_help('Dominant gene code for this marking.') !!}
                {!! Form::text('dominant', $marking->dominant, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>
    <div class="form-group">
        {!! Form::label('Short Description (Optional)') !!} {!! add_help('Adds a short description to any marking boxes.') !!}
        {!! Form::text('short_description', $marking->short_description, ['class' => 'form-control']) !!}
    </div>
    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $marking->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::checkbox('is_visible', 1, $marking->id ? $marking->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
        {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the marking will not be visible in the marking list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
    </div>

    <div class="text-right">
        {!! Form::submit($marking->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($marking->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._feature_entry', ['feature' => $marking])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-marking-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/markings/delete') }}/{{ $marking->id }}", 'Delete Marking');
            });
        });
    </script>
@endsection
