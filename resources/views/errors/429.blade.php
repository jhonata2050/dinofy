@extends('errors.layout')

@section('code', '429')
@section('title', 'Muitas requisicoes')
@section('message', 'Voce fez muitas requisicoes em pouco tempo. Aguarde alguns instantes e tente novamente.')
@section('icon-bg', 'bg-amber-100')
@section('icon')
<svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
@endsection
