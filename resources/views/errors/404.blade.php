@extends('errors.layout')

@section('code', '404')
@section('title', 'Pagina nao encontrada')
@section('message', 'A pagina que voce procura nao existe ou foi movida.')
@section('icon-bg', 'bg-zinc-100')
@section('icon')
<svg class="w-8 h-8 text-zinc-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
@endsection
