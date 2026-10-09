@extends('admin.layout')

@section('admin-title')
    {{ $carrier->id ? 'Edit' : 'Create' }} Carrier
@endsection

@section('admin-content')
    {!! breadcrumbs(['Admin Panel' => 'admin', 'Carriers' => 'admin/data/carriers', ($carrier->id ? 'Edit' : 'Create') . ' Carrier' => $carrier->id ? 'admin/data/carrier/edit/' . $carrier->id : 'admin/data/carrier/create']) !!}

    <h1>{{ $carrier->id ? 'Edit' : 'Create' }} Carrier
        @if ($carrier->id)
            <a href="#" class="btn btn-danger float-right delete-feature-button">Delete Carrier</a>
        @endif
    </h1>

    {!! Form::open(['url' => $carrier->id ? 'admin/data/carrier/edit/' . $carrier->id : 'admin/data/carrier/create', 'files' => true]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-12">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $carrier->name, ['class' => 'form-control']) !!}
            </div>
        </div>
    </div>



    <div class="form-group">
        {!! Form::label('Preview Image (Optional)') !!} {!! add_help('This image is used only on the carriers page.') !!}
        <div class="custom-file">
            {!! Form::label('image', file_exists($carrier->imageDirectory . '/' . $carrier->imageFileName) ? $carrier->imageFileName : 'Choose file...', ['class' => 'custom-file-label']) !!}
            {!! Form::file('image', ['class' => 'custom-file-input']) !!}
        </div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($carrier->image_id !== null)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!} {!! add_help('You do not need to add any cards to the code! Will be automatically generated on the marking page for you.') !!}
        {!! Form::textarea('description', $carrier->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Applicable Markings') !!} {!! add_help('Select any markings that this carrier can apply to.') !!}
        {!! Form::select('attached_markings[]', $markings, $active_markings, ['class' => 'form-control', 'id' => 'attached_markings', 'multiple' => true]) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($carrier->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('#attached_markings').selectize({
                placeholder: 'Select any applicable marking(s)...',
                multiple: true,
            });
            $('.delete-carrier-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/carrier/delete') }}/{{ $carrier->id }}", 'Delete Carrier');
            });
        });
    </script>
@endsection
