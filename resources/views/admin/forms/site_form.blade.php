@extends('admin.layout')

@section('admin-title') Forms & Polls @endsection

@section('admin-content')
{!! breadcrumbs(['Admin Panel' => 'admin', 'Forms & Polls' => 'admin/forms']) !!}

<h1>Forms & Polls</h1>

<p>You can create forms and polls here. Forms allow for multiple questions in one, but you can also do a single, poll-like question.</p>

<div class="text-right mb-3"><a class="btn btn-primary" href="{{ url('admin/forms/create') }}"><i class="fas fa-plus"></i> Create New Form</a></div>
@if(!count($forms))
    <p>No forms found.</p>
@else
    {!! $forms->render() !!}
      <div class="row ml-md-2">
        <div class="d-flex row flex-wrap col-12 pb-1 px-0 ubt-bottom">
          <div class="col-12 col-md-5 font-weight-bold">Title</div>
          <div class="col-6 col-md-2 font-weight-bold">Created At</div>
          <div class="col-6 col-md-2 font-weight-bold">Last Edited</div>
        </div>
        @foreach($forms as $form)
        <div class="d-flex row flex-wrap col-12 mt-1 pt-2 px-0 ubt-top">
          <div class="col-12 col-md-5">
              @if(!$form->is_active || ($form->is_active && $form->is_timed && $form->start_at > Carbon\Carbon::now())) 
                    <i class="fas fa-eye-slash mr-1" data-toggle="tooltip" title="This form is hidden."></i>
              @endif
              <a href="{{ $form->url }}">{{ $form->title }}</a>
          </div>
          <div class="col-6 col-md-2">{!! pretty_date($form->created_at) !!}</div>
          <div class="col-6 col-md-2">{!! pretty_date($form->updated_at) !!}</div>
          <div class="col-6 col-md-1"><a href="{{ url('admin/forms/edit/'.$form->id) }}" class="btn btn-primary py-0 px-2 w-100">Edit</a></div>
          <div class="col-6 col-md-2 text-right"><a href="{{ url('admin/forms/results/'.$form->id) }}" class="btn btn-secondary py-0 px-2 w-100">View Results</a></div>

        </div>
        @endforeach
      </div>
    {!! $forms->render() !!}

    <div class="text-center mt-4 small text-muted">{{ $forms->total() }} result{{ $forms->total() == 1 ? '' : 's' }} found.</div>

@endif

@endsection
