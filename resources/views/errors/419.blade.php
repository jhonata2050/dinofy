@extends('errors.layout')

@section('code', '419')
@section('title', 'Sessao expirada')
@section('message', 'Sua sessao expirou. Atualize a pagina e tente novamente.')
@section('icon-bg', 'bg-amber-100')
@section('icon')
<svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
@endsection
