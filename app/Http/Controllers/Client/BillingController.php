<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use App\Services\BillingGatewayFactory;
use Illuminate\Support\Facades\Auth;

class BillingController extends Controller
{
    public function index()
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $tenant->load('plan');

        $invoices = $tenant->invoices()->latest()->paginate(20);

        return view('client.billing.index', compact('tenant', 'invoices'));
    }

    public function show($id)
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $invoice = $tenant->invoices()->findOrFail($id);

        return view('client.billing.show', compact('tenant', 'invoice'));
    }

    public function generatePix($id)
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $invoice = $tenant->invoices()->findOrFail($id);

        if ($invoice->status === 'paid') {
            return back()->with('error', 'Esta fatura já está paga.');
        }

        try {
            BillingGatewayFactory::make()->createPixCharge($invoice, $tenant);
            ActivityLog::log('billing.pix_generated', "Cliente gerou PIX para fatura #{$invoice->id}", $tenant->id);
            return back()->with('success', 'Cobrança PIX gerada com sucesso!');
        } catch (\Throwable $e) {
            $msg = $e->getMessage();

            if (str_contains($msg, 'escopo') || str_contains($msg, 'permiss')) {
                return back()->with('error', 'O gateway de pagamento não está configurado corretamente. Entre em contato com o suporte.');
            }

            if (str_contains($msg, 'não configurado') || str_contains($msg, 'inválido') || str_contains($msg, 'AppID')) {
                return back()->with('error', 'O gateway de pagamento não está disponível no momento. Entre em contato com o suporte.');
            }

            return back()->with('error', 'Não foi possível gerar a cobrança PIX. Tente novamente mais tarde.');
        }
    }

    public function checkPayment($id)
    {
        $tenant = Auth::guard('tenant')->user()->tenant;
        $invoice = $tenant->invoices()->findOrFail($id);

        return response()->json([
            'status' => $invoice->status,
            'paid' => $invoice->status === 'paid',
        ]);
    }
}
