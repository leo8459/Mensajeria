@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Codigos Postales
@endsection

@section('content')
    @livewire('ventanilla7', ['origen' => 'packagesUENCOMIENDAS'])
    @include('footer')
@endsection
