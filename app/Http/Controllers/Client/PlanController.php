<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Ticket;
use App\Models\TicketMessage;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PlanController extends Controller
{
    public function index()
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $tenant->load('plan');

        $plans = Plan::where('is_active', true)->orderBy('price_cents')->get();

        return view('client.plans.index', compact('tenant', 'plans'));
    }

    public function requestUpgrade(Request $request)
    {
        $request->validate([
            'plan_id' => 'required|exists:plans,id',
        ]);

        $tenant = Auth::guard('tenant')->user()->tenant;
        $user = Auth::guard('tenant')->user();
        $newPlan = Plan::findOrFail($request->plan_id);

        if ($newPlan->id === $tenant->plan_id) {
            return back()->with('error', 'Você já está neste plano.');
        }

        $ticket = Ticket::create([
            'tenant_id' => $tenant->id,
            'subject' => "Solicitação de mudança para plano {$newPlan->name}",
            'category' => 'plan_change',
            'priority' => 'medium',
            'status' => 'open',
        ]);

        $action = $newPlan->price_cents > $tenant->plan->price_cents ? 'upgrade' : 'downgrade';

        TicketMessage::create([
            'ticket_id' => $ticket->id,
            'sender_type' => 'tenant',
            'sender_name' => $user->name,
            'message' => "Solicito {$action} do plano {$tenant->plan->name} (R$ {$tenant->plan->priceFormatted()}/mês) para o plano {$newPlan->name} (R$ {$newPlan->priceFormatted()}/mês).",
        ]);

        return redirect()->route('client.tickets.show', $ticket)->with('success', 'Solicitação de mudança de plano enviada. Acompanhe pelo ticket.');
    }
}
