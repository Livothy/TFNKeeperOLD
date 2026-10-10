@extends('admin.layout')

@section('admin-title')
    {{ $volume->id ? 'Edit' : 'Create' }} {{ ucfirst(__('volumes.volume')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucfirst(__('volumes.volumes')) => 'admin/data/volumes',
        ($volume->id ? 'Edit' : 'Create') . ' ' . ucfirst(__('volumes.volume')) => $volume->id
            ? 'admin/data/volumes/edit/' . $volume->id
            : 'admin/data/volumes/create',
    ]) !!}

    <h1>{{ $volume->id ? 'Edit' : 'Create' }} Volume
        @if ($volume->id)
            <a href="#" class="btn btn-outline-danger float-right delete-volume-button">Delete
                {{ ucfirst(__('volumes.volume')) }}</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $volume->id ? 'admin/data/volumes/edit/' . $volume->id : 'admin/data/volumes/create',
        'files' => true,
    ]) !!}

    <h3>Basic Information</h3>

    <div class="form-group">
        {!! Form::label('Name') !!}
        {!! Form::text('name', $volume->name, ['class' => 'form-control']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 100px x 100px</div>
        @if ($volume->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>
    <div class="row">
        <div class="col-md-8">
            <div class="form-group">
                {!! Form::label(ucfirst(__('volumes.book')) . ' (Optional)') !!}
                {!! Form::select('book_id', $books, $volume->book_id, ['class' => 'form-control selectize']) !!}
            </div>
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $volume->id ? $volume->is_visible : 1, [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                ]) !!}
                {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the ' . __('volumes.volume') . ' will not be visible.') !!}
            </div>
            <div class="form-group">
                {!! Form::checkbox('is_global', 1, $volume->id ? $volume->is_global : 1, [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                ]) !!}
                {!! Form::label('is_global', 'Is Global', ['class' => 'form-check-label ml-3']) !!} {!! add_help(
                    'Global ' .
                        __('volumes.volumes') .
                        ' will have their contents visible for all users as long as at least 1 person has unlocked this ' .
                        __('volumes.volume') .
                        '.',
                ) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $volume->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!} {!! add_help(
            'This is a short blurb that shows up on ' . __('volumes.book') . ' index. HTML cannot be used here.',
        ) !!}
        {!! Form::text('summary', $volume->summary, ['class' => 'form-control', 'maxLength' => 250]) !!}
    </div>

    <div class="text-right">
        {!! Form::submit($volume->id ? 'Edit' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}


    @if ($volume->id)
        <h3>Preview</h3>
        @include('world.volumes._volume_entry', ['volume' => $volume, 'isAdmin' => true])
        <br>
        @include('world.volumes._volume_page_entry', [
            'volume' => $volume,
            'isAdmin' => true,
        ])
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-volume-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/volumes/delete') }}/{{ $volume->id }}",
                    'Delete {{ ucfirst(__('volumes.volume')) }}');
            });
        });
    </script>
@endsection
