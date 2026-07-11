@extends('errors.layout')

@section('code', '400')
@section('title', 'Requisicao invalida')
@section('message', 'A requisicao enviada nao pode ser processada. Verifique os dados e tente novamente.')
@section('icon-bg', 'bg-amber-100')
@section('icon')
<svg class="w-8 h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L3.072 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
@endsection
