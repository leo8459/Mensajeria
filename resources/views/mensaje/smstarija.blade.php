@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Codigos Postales
@endsection

@section('content')
    @livewire('smstarija', ['origen' => 'packagesRDD'])
    @include('footer')
@endsection
