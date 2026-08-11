<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Setting;
use Illuminate\Support\Facades\Http;

class PaystackService
{
    public const BASE_URL = 'https://api.paystack.co';

    public function isConfigured(): bool
    {
        return (bool) (Setting::paystackPublicKey() && Setting::paystackSecretKey());
    }

    public function initialize(Order $order, string $email, string $callbackUrl): ?string
    {
        $secret = Setting::paystackSecretKey();

        if (! $secret || ! $order->transaction_reference) {
            return null;
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->post(self::BASE_URL.'/transaction/initialize', [
                'amount' => (int) round($order->total * 100),
                'email' => $email,
                'reference' => $order->transaction_reference,
                'callback_url' => $callbackUrl,
                'metadata' => [
                    'order_number' => $order->order_number,
                    'custom_fields' => [
                        [
                            'display_name' => 'Order',
                            'variable_name' => 'order_number',
                            'value' => $order->order_number,
                        ],
                    ],
                ],
            ]);

        if ($response->failed()) {
            return null;
        }

        return $response->json('data.authorization_url');
    }

    public function verify(string $reference): array
    {
        $secret = Setting::paystackSecretKey();

        if (! $secret) {
            return ['status' => false, 'message' => 'Paystack is not configured.'];
        }

        $response = Http::withToken($secret)
            ->acceptJson()
            ->get(self::BASE_URL.'/transaction/verify/'.urlencode($reference));

        if ($response->failed()) {
            return [
                'status' => false,
                'message' => $response->json('message') ?: 'Could not reach Paystack to verify the payment.',
            ];
        }

        $data = $response->json('data') ?? [];

        return [
            'status' => ($data['status'] ?? null) === 'success',
            'message' => $data['gateway_response'] ?? '',
            'data' => $data,
        ];
    }
}
