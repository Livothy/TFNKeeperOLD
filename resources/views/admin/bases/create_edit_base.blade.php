@extends('admin.layout')

@section('admin-title')
    {{ $base->id ? 'Edit' : 'Create' }} Base
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Bases' => 'admin/data/bases', ($base->id ? 'Edit' : 'Create') . ' Base' => $base->id ? 'admin/data/base/edit/' . $base->id : 'admin/data/base/create']) !!}

    <h1>{{ $base->id ? 'Edit' : 'Create' }} Base
        @if ($base->id)
            <a href="#" class="btn btn-danger float-right delete-feature-button">Delete Base</a>
        @endif
    </h1>

    {!! Form::open(['url' => $base->id ? 'admin/data/base/edit/' . $base->id : 'admin/data/base/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $base->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group" id="code">
                {!! Form::label('Base Code (Optional)') !!} {!! add_help('The base code for the genotype.') !!}
                {!! Form::text('code', $base->code, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-4">
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $base->id ? $base->is_visible : 1, ['class' => 'form-check-input', 'data-toggle' => 'toggle']) !!}
                {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the base will not be visible in the base list or available for selection in search and design updates. Permissioned staff will still be able to add them to characters, however.') !!}
            </div>
        </div>
    </div>



    <div class="form-group">
        {!! Form::label('Preview Image (Optional)') !!} {!! add_help('This image is used only on the base coats page.') !!}
        <div class="custom-file">
            {!! Form::label('image', file_exists($base->imageDirectory . '/' . $base->imageFileName) ? $base->imageFileName : 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($base->image_id !== null)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="text-right">
        {!! Form::submit($base->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($base->id)
        <h3>Preview</h3>
        <div class="card mb-3">
            <div class="card-body">
                @include('world._feature_entry', ['feature' => $base])
            </div>
        </div>
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-feature-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/base/delete') }}/{{ $base->id }}", 'Delete Base');
            });
        });
    </script>
@endsection
