<h3>
    {!! Form::label('Image') !!}
</h3>
<p>
    Upload an image for the foreground that will go in top a character's image. Notice that the image will need to be transparent for this to work. You may stack multiple foregrounds on a character, provided it is transparent and follows the image
    size.
    This does not edit the images of the characters who have this item.
</p>
<p>
    It is highly recommended to only have one category with items that have this tag
</p>
<div class="col-8 mx-auto">
    @if ($tag->data && isset($tag->getData()['background-image']))
        <img src="{{ url($tag->getData()['background-image']) }}" class="img-fluid mb-2" />
        {!! Form::file('image') !!}
    @else
        {!! Form::file('image', ['required' => 'required']) !!}
    @endif
</div>
