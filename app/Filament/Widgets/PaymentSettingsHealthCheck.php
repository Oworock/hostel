<?php

namespace App\Filament\Widgets;

use App\Models\PaymentGateway;
use App\Models\SystemSetting;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PaymentSettingsHealthCheck extends BaseWidget
{
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';

    public static function canView(): bool
    {
        return auth()->check() && auth()->user()->isAdmin();
    }

    protected function getStats(): array
    {
        $gateways = PaymentGateway::whereIn('name', ['Paystack', 'Flutterwave', 'Stripe', 'PayPal', 'Razorpay', 'Square'])
            ->get()
            ->keyBy('name');

        $gatewayReadiness = collect($gateways)->mapWithKeys(function ($gateway, $name) {
            return [$name => (bool) ($gateway?->is_active && !empty($gateway->public_key) && !empty($gateway->secret_key))];
        });

        $activeConfiguredCount = $gatewayReadiness->filter()->count();

        $smsProvider = SystemSetting::getSetting('sms_provider', 'none');
        $smsReady = $smsProvider === 'none'
            ? true
            : !empty(SystemSetting::getSetting('sms_url', ''))
                && !empty(SystemSetting::getSetting('sms_api_key', ''));

        $overallReady = ($activeConfiguredCount > 0) && $smsReady;

        $stats = [
            Stat::make('Overall Go-Live Status', $overallReady ? 'READY' : 'NOT READY')
                ->description('Requires at least one active payment gateway + valid SMS setup')
                ->descriptionIcon($overallReady ? 'heroicon-m-check-circle' : 'heroicon-m-exclamation-triangle')
                ->color($overallReady ? 'success' : 'danger'),

            Stat::make('Active Payment Gateways', (string) $activeConfiguredCount)
                ->description('Configured + enabled')
                ->descriptionIcon('heroicon-m-credit-card')
                ->color($activeConfiguredCount > 0 ? 'success' : 'warning'),

            Stat::make('SMS Gateway', $smsReady ? 'Configured' : 'Missing Setup')
                ->description('Provider: ' . strtoupper($smsProvider))
                ->descriptionIcon($smsReady ? 'heroicon-m-check' : 'heroicon-m-x-mark')
                ->color($smsReady ? 'success' : 'warning'),
        ];

        foreach (['Paystack', 'Flutterwave', 'Stripe', 'PayPal', 'Razorpay', 'Square'] as $gatewayName) {
            $gateway = $gateways->get($gatewayName);
            $ready = (bool) ($gatewayReadiness->get($gatewayName) ?? false);
            $stats[] = Stat::make($gatewayName, $ready ? 'Configured' : 'Missing Setup')
                ->description($gateway?->is_active ? 'Active gateway' : 'Inactive gateway')
                ->descriptionIcon($ready ? 'heroicon-m-check' : 'heroicon-m-x-mark')
                ->color($ready ? 'success' : 'warning');
        }

        return $stats;
    }
}
