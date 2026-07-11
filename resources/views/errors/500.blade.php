@extends('errors.layout')

@section('code', '500')
@section('title', 'Erro interno')
@section('message', 'Ocorreu um erro inesperado no servidor. Nossa equipe ja foi notificada. Tente novamente em instantes.')
@section('icon-bg', 'bg-red-100')
@section('icon')
<svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
@endsection
