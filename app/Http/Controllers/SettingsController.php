<?php

namespace App\Http\Controllers;

use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

class SettingsController extends Controller
{
    public function index()
    {
        $groups = [
            'gateway' => Setting::getByGroup('gateway'),
            'billing' => Setting::getByGroup('billing'),
            'platform' => Setting::getByGroup('platform'),
            'notifications' => Setting::getByGroup('notifications'),
        ];

        return view('settings.index', compact('groups'));
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);

        foreach ($settings as $key => $value) {
            $setting = Setting::where('key', $key)->first();
            if (!$setting) {
                continue;
            }

            if ($setting->is_encrypted && ($value === '••••••••' || $value === '' || $value === null)) {
                continue;
            }

            Setting::set($key, $value);
        }

        return back()->with('success', 'Configurações salvas com sucesso.');
    }

    public function testGateway()
    {
        $this->clearGatewayCache();

        $active = Setting::get('gateway_active', 'woovi');

        if ($active === 'woovi') {
            return $this->testWoovi();
        }

        return $this->testCajuPay();
    }

    private function clearGatewayCache(): void
    {
        $keys = [
            'gateway_active',
            'woovi_app_id', 'woovi_sandbox', 'woovi_webhook_secret',
            'cajupay_api_key', 'cajupay_api_secret', 'cajupay_base_url', 'cajupay_webhook_secret',
        ];

        foreach ($keys as $key) {
            Cache::forget("setting.{$key}");
        }
    }

    private function testWoovi()
    {
        $appId = Setting::get('woovi_app_id');
        $sandbox = (bool) Setting::get('woovi_sandbox', '0');
        $baseUrl = $sandbox ? 'https://api.woovi-sandbox.com' : 'https://api.openpix.com.br';
        $env = $sandbox ? 'Sandbox' : 'Producao';

        if (!$appId) {
            return back()->with('error', 'AppID da Woovi nao configurado. Insira o AppID e salve antes de testar.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => $appId,
                'Content-Type' => 'application/json',
            ])->timeout(10)->get("{$baseUrl}/api/v1/customer");

            $status = $response->status();

            if ($response->successful()) {
                return back()->with('success', "Woovi ({$env}) conectado com sucesso! AppID valido e API respondendo.");
            }

            if ($status === 401) {
                return back()->with('error', "AppID invalido. Verifique o AppID no painel Woovi ({$env}).");
            }

            if ($status === 403) {
                $body = $response->json();
                $errorMsg = $body['error'] ?? ($body['errors'][0]['message'] ?? $response->body());

                if (str_contains((string) $errorMsg, 'escopo')) {
                    return back()->with('success', "Woovi ({$env}) conectado! AppID reconhecido. Nota: o app nao possui todos os escopos — verifique no painel Woovi se os escopos CHARGE_CREATE e CHARGE_READ estao habilitados.");
                }

                return back()->with('error', "Woovi ({$env}) retornou 403: {$errorMsg}");
            }

            return back()->with('error', "Woovi ({$env}) respondeu com HTTP {$status}: " . $response->body());
        } catch (\Exception $e) {
            return back()->with('error', "Falha na conexao com Woovi ({$env}): " . $e->getMessage());
        }
    }

    private function testCajuPay()
    {
        $apiKey = Setting::get('cajupay_api_key');
        $baseUrl = rtrim(Setting::get('cajupay_base_url') ?: 'https://api.cajupay.com.br', '/');

        if (!$apiKey) {
            return back()->with('error', 'API Key do CajuPay nao configurada. Insira a API Key e salve antes de testar.');
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$apiKey}",
            ])->timeout(10)->get("{$baseUrl}/api/v1/account");

            $status = $response->status();

            if ($response->successful()) {
                return back()->with('success', 'CajuPay conectado com sucesso! Conta: ' . ($response->json('name') ?? 'Verificada'));
            }

            if ($status === 401) {
                return back()->with('error', 'API Key do CajuPay invalida. Verifique as credenciais.');
            }

            return back()->with('error', "CajuPay respondeu com HTTP {$status}: " . $response->body());
        } catch (\Exception $e) {
            return back()->with('error', 'Falha na conexao com CajuPay: ' . $e->getMessage());
        }
    }
}
