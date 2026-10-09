<h3>
    {!! Form::label('Image') !!}
</h3>
<p>
    Upload an image for the background that will go behind a character's image. Notice that the image will need to be transparent for this to work.
    This does not edit the images of the characters who have this item, instead, it places this same image url below behind the characters that have this item.
</p>
<p>
    It is highly recommended to only have one category with items that have this tag and have the category set to only allowing one item to be held at a time.
    In fact, it may not work if you allow otherwise and users have their characters holding multiple items with this tag.
</p>
<div class="col-8 mx-auto">
    @if ($tag->data && isset($tag->getData()['background-image']))
        <img src="{{ url($tag->getData()['background-image']) }}" class="img-fluid mb-2" />
        {!! Form::file('image') !!}
    @else
        {!! Form::file('image', ['required' => 'required']) !!}
    @endif
</div>



<h3>Padding</h3>
<p>
    Custom padding or space around the sides of the character image. Defaults to 1em.
</p>

<div class="row no-gutters">

    <div class="col-2"></div>
    <div class="col-md-8 my-2 text-md-center row no-gutters">
        <div class="col-4 col-md-12">{!! Form::label('padding-top', 'Top', ['class' => 'mb-0']) !!}</div>
        <div class="col-8 col-md-12">{!! Form::text('padding-top', $tag->data ? $tag->getData()['padding-top'] : '1em', ['class' => 'form-control', 'data-name' => 'padding-top', 'placeholder' => '1em']) !!}</div>
    </div>
    <div class="col-2"></div>

    <div class="col-md-6 my-2 my-md-0 row no-gutters pr-md-1">
        <div class="col-4 col-form-label text-md-right pr-md-2">{!! Form::label('padding-left', 'Left', ['class' => 'mb-0']) !!}</div>
        <div class="col-8">{!! Form::text('padding-left', $tag->data ? $tag->getData()['padding-left'] : '1em', ['class' => 'form-control', 'data-name' => 'padding-left', 'placeholder' => '1em']) !!}</div>
    </div>

    <div class="col-md-6 my-2 my-md-0 row no-gutters pl-md-1">
        <div class="col-8 order-1 order-md-0">{!! Form::text('padding-right', $tag->data ? $tag->getData()['padding-right'] : '1em', ['class' => 'form-control', 'data-name' => 'padding-right', 'placeholder' => '1em']) !!}</div>
        <div class="col-4 col-form-label pl-md-2 order-0 order-md-1">{!! Form::label('padding-right', 'Right', ['class' => 'mb-0']) !!}</div>
    </div>

    <div class="col-2"></div>
    <div class="col-md-8 my-2 text-md-center row no-gutters">
        <div class="col-8 col-md-12 order-1 order-md-0">{!! Form::text('padding-bottom', $tag->data ? $tag->getData()['padding-bottom'] : '1em', ['class' => 'form-control', 'data-name' => 'padding-bottom', 'placeholder' => '1em']) !!}</div>
        <div class="col-4 col-md-12 order-0 order-md-1">{!! Form::label('padding-bottom', 'Bottom') !!}</div>
    </div>
    <div class="col-2"></div>

</div>
