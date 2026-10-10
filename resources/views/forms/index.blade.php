@extends('layouts.app')

@section('title') Site Forms & Polls @endsection

@section('content')
{!! breadcrumbs(['Site Forms & Polls' => 'forms']) !!}
<h1>Site Forms & Polls</h1>
@if(count($forms))
    {!! $forms->render() !!}
    @foreach($forms as $form)
        @include('forms._site_form', ['form' => $form, 'page' => FALSE])
    @endforeach
    {!! $forms->render() !!}
@else
    <div>No forms or polls were posted yet.</div>
@endif
@endsection
