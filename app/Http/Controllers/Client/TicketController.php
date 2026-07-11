<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TicketController extends Controller
{
    public function index()
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $tickets = $tenant->tickets()->with('latestMessage')->latest()->paginate(20);

        return view('client.tickets.index', compact('tickets'));
    }

    public function create()
    {
        return view('client.tickets.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'category' => 'required|in:general,billing,technical,plan_change',
            'priority' => 'required|in:low,medium,high',
            'message' => 'required|string|max:5000',
        ]);

        $tenant = Auth::guard('tenant')->user()->tenant;
        $user = Auth::guard('tenant')->user();

        $ticket = Ticket::create([
            'tenant_id' => $tenant->id,
            'subject' => $validated['subject'],
            'category' => $validated['category'],
            'priority' => $validated['priority'],
        ]);

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'tenant',
            'sender_name' => $user->name,
            'message' => $validated['message'],
        ]);

        return redirect()->route('client.tickets.show', $ticket)->with('success', 'Ticket criado com sucesso.');
    }

    public function show(Ticket $ticket)
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        abort_if($ticket->tenant_id !== $tenant->id, 403);

        $ticket->load('messages');

        return view('client.tickets.show', compact('ticket'));
    }

    public function reply(Request $request, Ticket $ticket)
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        abort_if($ticket->tenant_id !== $tenant->id, 403);

        $request->validate(['message' => 'required|string|max:5000']);

        $user = Auth::guard('tenant')->user();

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'tenant',
            'sender_name' => $user->name,
            'message' => $request->message,
        ]);

        if ($ticket->status === 'waiting_client') {
            $ticket->update(['status' => 'open']);
        }

        return back()->with('success', 'Resposta enviada.');
    }
}
