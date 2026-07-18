<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use App\Models\Invoice;
use App\Models\InvoiceItem;
use App\Models\Plan;
use App\Models\Tenant;
use App\Services\BillingGatewayFactory;
use Illuminate\Http\Request;

class InvoiceController extends Controller
{
    public function index(Request $request)
    {
        $query = Invoice::with(['tenant', 'plan']);

        if ($status = $request->get('status')) {
            $query->where('status', $status);
        }

        if ($tenantId = $request->get('tenant_id')) {
            $query->where('tenant_id', $tenantId);
        }

        $invoices = $query->latest()->paginate(20);

        return view('invoices.index', compact('invoices'));
    }

    public function show(Invoice $invoice)
    {
        $invoice->load(['tenant', 'plan', 'items']);
        return view('invoices.show', compact('invoice'));
    }

    public function create(Request $request)
    {
        $tenants = Tenant::whereNotIn('status', ['terminated', 'terminating'])->orderBy('subdomain')->get();
        $plans = Plan::where('is_active', true)->get();
        $selectedTenant = $request->get('tenant_id');

        return view('invoices.create', compact('tenants', 'plans', 'selectedTenant'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'tenant_id' => 'required|exists:tenants,id',
            'plan_id' => 'required|exists:plans,id',
            'due_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'generate_pix' => 'nullable|boolean',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price_cents' => 'required|integer|min:0',
        ]);

        $totalCents = 0;
        foreach ($validated['items'] as $item) {
            $totalCents += $item['quantity'] * $item['unit_price_cents'];
        }

        $invoice = Invoice::create([
            'tenant_id' => $validated['tenant_id'],
            'plan_id' => $validated['plan_id'],
            'amount_cents' => $totalCents,
            'due_date' => $validated['due_date'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'status' => 'pending',
            'idempotency_key' => \Illuminate\Support\Str::random(48),
        ]);

        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price_cents' => $item['unit_price_cents'],
                'total_cents' => $item['quantity'] * $item['unit_price_cents'],
            ]);
        }

        $tenant = Tenant::find($validated['tenant_id']);
        ActivityLog::log('invoice.created', "Fatura #{$invoice->id} criada — R$ " . $invoice->amountFormatted(), $tenant->id);

        if ($request->boolean('generate_pix')) {
            try {
                BillingGatewayFactory::make()->createPixCharge($invoice, $tenant);
                return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Fatura criada e cobrança PIX gerada.');
            } catch (\Throwable $e) {
                return redirect()->route('admin.invoices.show', $invoice)->with('error', "Fatura criada, mas falha ao gerar PIX: {$e->getMessage()}");
            }
        }

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Fatura criada.');
    }

    public function edit(Invoice $invoice)
    {
        $invoice->load(['tenant', 'plan', 'items']);
        $plans = Plan::where('is_active', true)->get();

        return view('invoices.edit', compact('invoice', 'plans'));
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'plan_id' => 'required|exists:plans,id',
            'due_date' => 'required|date',
            'period_start' => 'required|date',
            'period_end' => 'required|date|after_or_equal:period_start',
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'items' => 'required|array|min:1',
            'items.*.description' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.unit_price_cents' => 'required|integer|min:0',
        ]);

        $totalCents = 0;
        foreach ($validated['items'] as $item) {
            $totalCents += $item['quantity'] * $item['unit_price_cents'];
        }

        $invoice->update([
            'plan_id' => $validated['plan_id'],
            'due_date' => $validated['due_date'],
            'period_start' => $validated['period_start'],
            'period_end' => $validated['period_end'],
            'status' => $validated['status'],
            'amount_cents' => $totalCents,
        ]);

        if ($validated['status'] === 'paid' && !$invoice->paid_at) {
            $invoice->update(['paid_at' => now()]);
        }

        $invoice->items()->delete();
        foreach ($validated['items'] as $item) {
            $invoice->items()->create([
                'description' => $item['description'],
                'quantity' => $item['quantity'],
                'unit_price_cents' => $item['unit_price_cents'],
                'total_cents' => $item['quantity'] * $item['unit_price_cents'],
            ]);
        }

        ActivityLog::log('invoice.updated', "Fatura #{$invoice->id} atualizada", $invoice->tenant_id);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Fatura atualizada.');
    }

    public function confirmPayment(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Esta fatura já está paga.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        $tenant = $invoice->tenant;
        if ($tenant && $tenant->status === 'pending_payment') {
            $tenant->update([
                'status' => 'provisioning',
                'next_billing_date' => now()->addMonth(),
            ]);

            try {
                app(\App\Services\TenantProvisioner::class)->provision($tenant);
            } catch (\Throwable $e) {
                ActivityLog::log('provision.failed', "Falha ao provisionar após confirmação: {$e->getMessage()}", $tenant->id);
            }
        } elseif ($tenant && $tenant->status === 'suspended') {
            $tenant->update([
                'status' => 'active',
                'next_billing_date' => now()->addMonth(),
            ]);

            try {
                app(\App\Services\TenantProvisioner::class)->activate($tenant);
            } catch (\Throwable $e) {
                ActivityLog::log('activation.failed', "Falha ao reativar após confirmação: {$e->getMessage()}", $tenant->id);
            }
        } elseif ($tenant) {
            $tenant->update(['next_billing_date' => now()->addMonth()]);
        }

        ActivityLog::log('invoice.confirmed', "Pagamento da fatura #{$invoice->id} confirmado manualmente", $invoice->tenant_id);

        return redirect()->route('admin.invoices.show', $invoice)->with('success', 'Pagamento confirmado.');
    }

    public function generateCharge(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Esta fatura já está paga.');
        }

        $tenant = $invoice->tenant;

        try {
            BillingGatewayFactory::make()->createPixCharge($invoice, $tenant);
            ActivityLog::log('invoice.charge_generated', "Cobrança PIX gerada para fatura #{$invoice->id}", $tenant->id);
            return back()->with('success', 'Cobrança PIX gerada com sucesso.');
        } catch (\Throwable $e) {
            return back()->with('error', "Falha ao gerar cobrança: {$e->getMessage()}");
        }
    }
}
