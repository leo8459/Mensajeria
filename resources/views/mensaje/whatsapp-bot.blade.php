@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Codigos Postales
@endsection

@section('content')
    <livewire:whatsapp-bot :acc="$acc ?? 'wa2'" />
    @include('footer')
@endsection
