@extends('client.layouts.app')
@section('title', 'Novo Ticket')
@section('content')

<div class="flex items-center gap-3 mb-8">
    <a href="{{ route('client.tickets.index') }}" class="text-zinc-400 hover:text-zinc-600">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7"/></svg>
    </a>
    <h1 class="text-xl font-semibold text-zinc-900">Novo Ticket</h1>
</div>

<div class="rounded-xl border border-zinc-50 bg-zinc-50 p-6 max-w-2xl">
    <form method="POST" action="{{ route('client.tickets.store') }}" class="space-y-4">
        @csrf

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Assunto</label>
            <input type="text" name="subject" value="{{ old('subject') }}" required
                class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent"
                placeholder="Descreva brevemente o problema">
        </div>

        <div class="grid grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Categoria</label>
                <select name="category" class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                    <option value="general" {{ old('category') === 'general' ? 'selected' : '' }}>Geral</option>
                    <option value="technical" {{ old('category') === 'technical' ? 'selected' : '' }}>Técnico</option>
                    <option value="billing" {{ old('category') === 'billing' ? 'selected' : '' }}>Financeiro</option>
                    <option value="plan_change" {{ old('category') === 'plan_change' ? 'selected' : '' }}>Mudança de Plano</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-zinc-700 mb-1">Prioridade</label>
                <select name="priority" class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent">
                    <option value="low" {{ old('priority') === 'low' ? 'selected' : '' }}>Baixa</option>
                    <option value="medium" {{ old('priority', 'medium') === 'medium' ? 'selected' : '' }}>Média</option>
                    <option value="high" {{ old('priority') === 'high' ? 'selected' : '' }}>Alta</option>
                </select>
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-zinc-700 mb-1">Mensagem</label>
            <textarea name="message" rows="6" required
                class="w-full px-3 py-2.5 border border-zinc-300 rounded-lg text-sm focus:ring-2 focus:ring-green-600 focus:border-transparent resize-y"
                placeholder="Descreva detalhadamente o que precisa...">{{ old('message') }}</textarea>
        </div>

        <div class="flex gap-3 pt-2">
            <button type="submit" class="px-6 py-2.5 text-white rounded-lg text-sm font-medium transition hover:opacity-90" style="background: var(--color-primary);">Enviar Ticket</button>
            <a href="{{ route('client.tickets.index') }}" class="px-6 py-2.5 bg-zinc-200 rounded-lg hover:bg-zinc-300 text-sm font-medium text-zinc-700 transition">Cancelar</a>
        </div>
    </form>
</div>

@endsection
