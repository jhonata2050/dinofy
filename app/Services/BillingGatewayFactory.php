<?php

namespace App\Services;

use App\Models\Setting;

class BillingGatewayFactory
{
    public static function make(): BillingGatewayInterface
    {
        $active = Setting::get('gateway_active', 'woovi');

        return match ($active) {
            'cajupay' => app(CajuPayBillingService::class),
            'woovi' => app(WooviBillingService::class),
            default => app(WooviBillingService::class),
        };
    }

    public static function activeGateway(): string
    {
        return Setting::get('gateway_active', 'woovi');
    }

    public static function resolve(string $gateway): BillingGatewayInterface
    {
        return match ($gateway) {
            'cajupay' => app(CajuPayBillingService::class),
            'woovi' => app(WooviBillingService::class),
            default => throw new \InvalidArgumentException("Gateway desconhecido: {$gateway}"),
        };
    }
}
