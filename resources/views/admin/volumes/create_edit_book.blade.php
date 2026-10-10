@extends('admin.layout')

@section('admin-title')
    {{ $book->id ? 'Edit' : 'Create' }} {{ ucfirst(__('volumes.book')) }}
@endsection

@section('admin-content')
    {!! breadcrumbs([
        'Admin Panel' => 'admin',
        ucfirst(__('volumes.books')) => 'admin/data/volumes/books',
        ($book->id ? 'Edit' : 'Create') . ' ' . ucfirst(__('volumes.volume')) => $book->id ? 'admin/data/volumes/books/edit/' . $book->id : 'admin/data/volumes/books/create',
    ]) !!}

    <h1>{{ $book->id ? 'Edit' : 'Create' }} {{ ucfirst(__('volumes.book')) }}
        @if ($book->id)
            <a href="#" class="btn btn-danger float-right delete-book-button">Delete
                {{ ucfirst(__('volumes.book')) }}</a>
        @endif
    </h1>

    {!! Form::open([
        'url' => $book->id ? 'admin/data/volumes/books/edit/' . $book->id : 'admin/data/volumes/books/create',
        'files' => true,
    ]) !!}

    <h3>Basic Information</h3>

    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('Name') !!}
                {!! Form::text('name', $book->name, ['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label(ucfirst(__('volumes.bookshelf')) . ' (Optional)') !!}
                {!! Form::select('bookshelf_id', $bookshelves, $book->bookshelf_id, ['class' => 'form-control selectize']) !!}
            </div>
        </div>
    </div>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::checkbox('is_visible', 1, $book->id ? $book->is_visible : 1, [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                ]) !!}
                {!! Form::label('is_visible', 'Is Visible', ['class' => 'form-check-label ml-3']) !!} {!! add_help('If turned off, the ' . __('volumes.book') . ' will not be visible.') !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::checkbox('is_public', 1, $book->id ? $book->is_public : 1, [
                    'class' => 'form-check-input',
                    'data-toggle' => 'toggle',
                ]) !!}
                {!! Form::label('is_public', 'Is Public', ['class' => 'form-check-label ml-3']) !!} {!! add_help('A public ' . __('volumes.book') . ' will have all its ' . __('volumes.volumes') . ' visible by default.') !!}
            </div>
        </div>
    </div>
    <h5>{{ucfirst(__('volumes.volume'))}} Prefix</h5>
    <p>You can set a prefix for this {{__('volumes.book')}}'s {{__('volumes.volumes')}}. It can be text, a number, or both. Example: "Chapter" for text, and "number" for numeric will output "Chapter 1", "Chapter 2", etc.</p>
    <div class="row">
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('text_prefix', 'Text Prefix', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Give this  ' . __('volumes.book') . '\'s  ' . __('volumes.volumes') . ' a prefix before their names.') !!}
                {!! Form::text('text_prefix', $book->text_prefix, [
                    'class' => 'form-control',
                ]) !!}
            </div>
        </div>
        <div class="col-md-3">
            <div class="form-group">
                {!! Form::label('numeric_prefix', 'Numeric Prefix', ['class' => 'form-check-label ml-3']) !!} {!! add_help('Give this  ' . __('volumes.book') . '\'s  ' . __('volumes.volumes') . ' a prefix before their names.') !!}
                {!! Form::select('numeric_prefix', ['0' => 'No Prefix', '1' => 'Number (1, 2)', '2' => 'Roman Numeral (I, II)'], $book->numeric_prefix, [
                    'class' => 'form-control',
                ]) !!}
            </div>
        </div>
    </div>

    <div class="form-group">
        {!! Form::label('World Page Image (Optional)') !!} {!! add_help('This image is used only on the world information pages.') !!}
        <div>{!! Form::file('image') !!}</div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($book->has_image)
            <div class="form-check">
                {!! Form::checkbox('remove_image', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_image', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>
    <div class="form-group">
        {!! Form::label('Prev/Next Button Image (Optional)') !!} {!! add_help('This image is used as the previous/next button for the book\'s volumes. The image should be pointing to the left (<-) for accuracy, and will be automatically flipped for the next (->) image.') !!}
        <div>{!! Form::file('next_image') !!}</div>
        <div class="text-muted">Recommended size: 200px x 200px</div>
        @if ($book->has_next)
            <div class="form-check">
                {!! Form::checkbox('remove_next', 1, false, ['class' => 'form-check-input']) !!}
                {!! Form::label('remove_next', 'Remove current image', ['class' => 'form-check-label']) !!}
            </div>
        @endif
    </div>

    <div class="form-group">
        {!! Form::label('Description (Optional)') !!}
        {!! Form::textarea('description', $book->description, ['class' => 'form-control wysiwyg']) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Summary (Optional)') !!} {!! add_help('This is a short blurb that shows up on ' . __('volumes.book') . ' index. HTML cannot be used here.') !!}
        {!! Form::text('summary', $book->summary, ['class' => 'form-control', 'maxLength' => 250]) !!}
    </div>

    <div class="form-group">
        {!! Form::label('Tags (Optional)') !!} {!! add_help('A list of tags to organize books by. Separate each tag with commas (,).') !!}
        {!! Form::text('tags', null, [
            'class' => 'form-control tag-list',
            'multiple',
            'data-init-value' => $book->entryTags,
        ]) !!}
    </div>


    <div class="text-right">
        {!! Form::submit($book->id ? 'Edit Book' : 'Create', ['class' => 'btn btn-primary']) !!}
    </div>

    {!! Form::close() !!}

    @if ($book->id)
        <br>
        <div class="card mb-3 p-4">
            <h2>{{ ucfirst(__('volumes.book')) }} {{ ucfirst(__('volumes.volumes')) }}</h2>
            <p>You can sort this {{ __('volumes.book') }}'s {{ __('volumes.volumes') }} here.</p>
            <p><strong>The {{ __('volumes.volumes') }} MUST be sorted properly for their back/next buttons to display correctly. You should click to sort every time you add a new {{ __('volumes.volume') }} to this {{ __('volumes.book') }} order to keep
                    them current.</strong></p>

            <div class="card-body">

                @if (count($book->volumes))
                    <div id="sortable" class="sortable">
                        <table class="table table-sm type-table">
                            <thead>
                                <tr>
                                    <td class="font-weight-bold" style="width:25%;">
                                        Name
                                    </td>
                                    <td class="font-weight-bold">
                                        Summary
                                    </td>
                                    <td></td>
                                </tr>
                            </thead>
                            <tbody id="sortable" class="sortable">
                                @foreach ($book->volumes as $volume)
                                    <tr class="sort-item" data-id="{{ $volume->id }}">
                                        <td>
                                            <a class="fas fa-arrows-alt-v handle mr-3" href="#"></a>
                                            {!! $volume->displayName !!}
                                            @include('world.volumes._volume_icons', ['volume' => $volume])
                                        </td>
                                        <td>
                                            {!! $volume->summary !!}
                                        </td>
                                        <td class="text-right">
                                            <a href="{{ url('admin/data/volumes/edit/' . $volume->id) }}" class="btn btn-primary">Edit</a>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>

                        </table>

                    </div>
                    <div class="mb-4">
                        {!! Form::open(['url' => 'admin/data/volumes/books/sort/' . $book->id]) !!}
                        {!! Form::hidden('sort', '', ['id' => 'sortableOrder']) !!}
                        {!! Form::submit('Save Order', ['class' => 'btn btn-primary']) !!}
                        {!! Form::close() !!}
                    </div>
                @else
                    <div class="alert alert-info">No {{ __('volumes.volumes') }} found.</div>
                @endif
            </div>
        </div>

        {!! Form::open(['url' => 'admin/data/volumes/books/authors/' . $book->id]) !!}
        <h5>Book Authors</h5>
        <p>You can credit the authors of this book here. This can be a user (onsite or offsite), or a character onsite.</p>

        <div class="text-right mb-3">
            <a href="#" class="btn btn-outline-info" id="addLoot">Add Author</a>
        </div>
        <table class="table table-sm" id="authorTable">
            <thead>
                <tr>
                    <th width="35%">Author Type</th>
                    <th width="35%">Author</th>
                    <th width="20%">Credit (Writer, Editor, etc.)</th>
                    <th width="10%"></th>
                </tr>
            </thead>
            <tbody id="authorTableBody">
                @if (count($book->authors))
                    @foreach ($book->authors as $author)
                        <tr class="author-row">
                            <td>{!! Form::select('author_type[]', ['OnsiteUser' => 'Onsite User', 'OffsiteUser' => 'Offsite User', 'OffsiteCharacter' => 'Offsite Character', 'OnsiteCharacter' => 'Onsite Character'], $author->author_type, [
                                'class' => 'form-control author-type',
                                'placeholder' => 'Select Author Type',
                            ]) !!}</td>
                            <td class="author-row-select">
                                @if ($author->author_type == 'OnsiteUser')
                                    {!! Form::select('author[]', $users, $author->author, ['class' => 'form-control onuser-select selectize', 'placeholder' => 'Select Onsite User']) !!}
                                @elseif($author->author_type == 'OffsiteUser')
                                    {!! Form::text('author[]', $author->author, ['class' => 'form-control offuser-select', 'placeholder' => 'Offsite User URL']) !!}
                                @elseif($author->author_type == 'OffsiteCharacter')
                                    {!! Form::text('author[]', $author->author, ['class' => 'form-control offchar-select', 'placeholder' => 'Offsite Character URL']) !!}
                                @elseif($author->author_type == 'OnsiteCharacter')
                                    {!! Form::select('author[]', $characters, $author->author, ['class' => 'form-control onchar-select selectize', 'placeholder' => 'Select Onsite Character']) !!}
                                @endif
                            </td>
                            <td>{!! Form::text('credit_type[]', $author->credit_type, ['class' => 'form-control', 'placeholder' => 'Credit']) !!}</td>
                            <td class="text-right"><a href="#" class="btn btn-danger remove-author-button">Remove</a></td>
                        </tr>
                    @endforeach
                @endif
            </tbody>
        </table>

        <div class="text-right">
            {!! Form::submit('Edit Authors', ['class' => 'btn btn-primary']) !!}
        </div>
        {!! Form::close() !!}

        <div id="authorRowData" class="hide">
            <table class="table table-sm">
                <tbody id="authorRow">
                    <tr class="author-row">
                        <td>{!! Form::select('author_type[]', ['OnsiteUser' => 'Onsite User', 'OffsiteUser' => 'Offsite User', 'OffsiteCharacter' => 'Offsite Character', 'OnsiteCharacter' => 'Onsite Character'], null, [
                            'class' => 'form-control author-type',
                            'placeholder' => 'Select Author Type',
                        ]) !!}</td>
                        <td class="author-row-select"></td>
                        <td>{!! Form::text('credit_type[]', null, ['class' => 'form-control', 'placeholder' => 'Credit']) !!}</td>
                        <td class="text-right"><a href="#" class="btn btn-danger remove-author-button">Remove</a></td>
                    </tr>
                </tbody>
            </table>
            {!! Form::select('author[]', $users, null, ['class' => 'form-control onuser-select', 'placeholder' => 'Select Onsite User']) !!}
            {!! Form::text('author[]', null, ['class' => 'form-control offuser-select', 'placeholder' => 'Offsite User URL']) !!}
            {!! Form::text('author[]', null, ['class' => 'form-control offchar-select', 'placeholder' => 'Offsite Character URL']) !!}
            {!! Form::select('author[]', $characters, null, ['class' => 'form-control onchar-select', 'placeholder' => 'Select Onsite Character']) !!}
        </div>

        <h3>Preview</h3>
        @include('world.volumes._book_entry', [
            'book' => $book,
            'isAdmin' => true,
        ])
        <br>
        @include('world.volumes._book_page_entry', [
            'book' => $book,
        ])
    @endif
@endsection

@section('scripts')
    @parent
    <script>
        $(document).ready(function() {
            $('.delete-book-button').on('click', function(e) {
                e.preventDefault();
                loadModal("{{ url('admin/data/volumes/books/delete') }}/{{ $book->id }}",
                    'Delete {{ ucfirst(__('volumes.book')) }}');
            });
            $('.handle').on('click', function(e) {
                e.preventDefault();
            });
            $("#sortable").sortable({
                items: '.sort-item',
                handle: ".handle",
                placeholder: "sortable-placeholder",
                stop: function(event, ui) {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                },
                create: function() {
                    $('#sortableOrder').val($(this).sortable("toArray", {
                        attribute: "data-id"
                    }));
                }
            });
            $("#sortable").disableSelection();
            var $authorTable = $('#authorTableBody');
            var $authorRow = $('#authorRow').find('.author-row');
            var $onuserSelect = $('#authorRowData').find('.onuser-select');
            var $offuserSelect = $('#authorRowData').find('.offuser-select');
            var $offcharSelect = $('#authorRowData').find('.offchar-select');

            var $oncharSelect = $('#authorRowData').find('.onchar-select');


            $('#authorTableBody .selectize').selectize();
            attachRemoveListener($('#authorTableBody .remove-author-button'));

            $('#addLoot').on('click', function(e) {
                e.preventDefault();
                var $clone = $authorRow.clone();
                $authorTable.append($clone);
                attachAuthorTypeListener($clone.find('.author-type'));
                attachRemoveListener($clone.find('.remove-author-button'));
            });

            $('.author-type').on('change', function(e) {
                var val = $(this).val();
                var $cell = $(this).parent().find('.author-row-select');

                var $clone = null;
                if (val == 'OnsiteUser') $clone = $onuserSelect.clone();
                else if (val == 'OffsiteUser') $clone = $offuserSelect.clone();
                else if (val == 'OffsiteCharacter') $clone = $offcharSelect.clone();
                else if (val == 'OnsiteCharacter') $clone = $oncharSelect.clone();


                $cell.html('');
                $cell.append($clone);
            });

            function attachAuthorTypeListener(node) {
                node.on('change', function(e) {
                    var val = $(this).val();
                    var $cell = $(this).parent().parent().find('.author-row-select');

                    var $clone = null;
                    if (val == 'OnsiteUser') $clone = $onuserSelect.clone();
                    else if (val == 'OffsiteUser') $clone = $offuserSelect.clone();
                    else if (val == 'OffsiteCharacter') $clone = $offcharSelect.clone();
                    else if (val == 'OnsiteCharacter') $clone = $oncharSelect.clone();


                    $cell.html('');
                    $cell.append($clone);
                    if (val == 'OnsiteUser' || val == 'OnsiteCharacter') $clone.selectize();
                });
            }

            function attachRemoveListener(node) {
                node.on('click', function(e) {
                    e.preventDefault();
                    $(this).parent().parent().remove();
                });
            }
            $('.tag-list').selectize({
                plugins: ["restore_on_backspace", "remove_button"],
                delimiter: ",",
                valueField: 'tag',
                labelField: 'tag',
                searchField: 'tag',
                persist: false,
                create: true,
                preload: true,
                options: {!! json_encode($book->getAllTags()) !!},
                onInitialize: function() {
                    var existingOptions = JSON.parse(this.$input.attr(
                        'data-init-value'));
                    var self = this;
                    if (Object.prototype.toString.call(existingOptions) ===
                        "[object Array]") {
                        existingOptions.forEach(function(existingOption) {
                            self.addOption(existingOption);
                            self.addItem(existingOption[self.settings
                                .valueField]);
                        });
                    } else if (typeof existingOptions === 'object') {
                        self.addOption(existingOptions);
                        self.addItem(existingOptions[self.settings.valueField]);
                    }
                }
            });
        });
    </script>
@endsection
