@extends('adminlte::page')
@section('title', 'Usuarios')
@section('template_title')
    Codigos Postales
@endsection

@section('content')
    <livewire:whatsapp-bot :acc="$acc ?? 'urbano3'" />
    @include('footer')
@endsection
