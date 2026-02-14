<?php

namespace App\Http\Controllers\Admin\Settings;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Models\PaymentGateway;
use App\Models\SmsProvider;
use Illuminate\Http\Request;

class SystemSettingController extends Controller
{
    public function __construct()
    {
        $this->middleware('admin');
    }

    public function index()
    {
        $settings = SystemSetting::pluck('value', 'key');
        $paymentGateways = PaymentGateway::all();
        $smsProviders = SmsProvider::all();

        return view('admin.settings.index', compact('settings', 'paymentGateways', 'smsProviders'));
    }

    public function general()
    {
        $settings = SystemSetting::pluck('value', 'key');
        return view('admin.settings.general', compact('settings'));
    }

    public function updateGeneral(Request $request)
    {
        $validated = $request->validate([
            'app_name' => 'required|string|max:255',
            'app_description' => 'nullable|string',
            'primary_color' => 'required|string',
            'secondary_color' => 'required|string',
            'currency' => 'required|string|max:3',
            'timezone' => 'required|string',
            'support_email' => 'required|email',
        ]);

        foreach ($validated as $key => $value) {
            SystemSetting::setSetting($key, $value);
        }

        return redirect()->back()->with('success', 'Settings updated successfully');
    }

    public function payment()
    {
        $paymentGateways = PaymentGateway::all();
        return view('admin.settings.payment', compact('paymentGateways'));
    }

    public function updatePaymentGateway(Request $request, PaymentGateway $gateway)
    {
        $validated = $request->validate([
            'public_key' => 'required|string',
            'secret_key' => 'required|string',
            'transaction_fee' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $gateway->update($validated);

        return redirect()->back()->with('success', 'Payment gateway updated');
    }

    public function sms()
    {
        $smsProviders = SmsProvider::all();
        return view('admin.settings.sms', compact('smsProviders'));
    }

    public function updateSmsProvider(Request $request, SmsProvider $provider)
    {
        $validated = $request->validate([
            'api_key' => 'required|string',
            'api_secret' => 'nullable|string',
            'sender_id' => 'required|string',
            'config' => 'nullable|json',
            'is_active' => 'boolean',
        ]);

        $provider->update($validated);

        return redirect()->back()->with('success', 'SMS provider updated');
    }
}
