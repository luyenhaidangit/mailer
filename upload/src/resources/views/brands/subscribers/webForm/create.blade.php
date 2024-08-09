@extends('layout.web_form')

@section('title', trans('default.subscribers'))

@section('contents')
<app-web-form-subscriber-create-edit api-key="{{$api_key}}" store-url="{{$store_url}}" update-url="{{$update_url}}" @if(isset($brand) && isset($id)) selected-url="brands/{{ $brand }}/web-form/get-subscriber/{{ $id }}" @endif></app-web-form-subscriber-create-edit>
@endsection