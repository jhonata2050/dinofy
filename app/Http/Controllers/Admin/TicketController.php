<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;

class TicketController extends Controller
{
    public function index(Request $request)
    {
        $query = Ticket::with(['tenant', 'latestMessage']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('subject', 'like', "%{$search}%")
                  ->orWhereHas('tenant', fn ($t) => $t->where('subdomain', 'like', "%{$search}%")->orWhere('name', 'like', "%{$search}%"));
            });
        }

        $tickets = $query->latest()->paginate(20);

        return view('admin.tickets.index', compact('tickets'));
    }

    public function show(Ticket $ticket)
    {
        $ticket->load(['tenant.plan', 'messages']);

        return view('admin.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $request->validate(['message' => 'required|string|max:5000']);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'admin',
            'sender_name' => auth()->user()->name,
            'message' => $request->message,
        ]);

        if ($ticket->status === 'open') {
            $ticket->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Resposta enviada.');
    }

    public function updateStatus(Request $request, Ticket $ticket)
    {
        $request->validate([
            'status' => 'required|in:open,in_progress,waiting_client,resolved,closed',
        ]);

        $ticket->update(['status' => $request->status]);

        return back()->with('success', 'Status atualizado.');
    }
}
