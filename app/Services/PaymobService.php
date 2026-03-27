<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class PaymobService
{
    protected string $baseUrl = 'https://accept.paymob.com';

    /**
     * Authenticate with Paymob and get an auth token.
     */
    public function authenticate(): string
    {
        $apiKey = Setting::get('paymob_api_key');

        $response = Http::post("{$this->baseUrl}/api/auth/tokens", [
            'api_key' => $apiKey,
        ]);

        if (!$response->successful()) {
            Log::error('Paymob authentication failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \Exception('Paymob authentication failed: ' . ($response->json('message') ?? 'Unknown error'));
        }

        return $response->json('token');
    }

    /**
     * Register an order with Paymob.
     */
    public function registerOrder(string $authToken, string $merchantOrderId, int $amountCents, array $items = []): int
    {
        $response = Http::post("{$this->baseUrl}/api/ecommerce/orders", [
            'auth_token' => $authToken,
            'delivery_needed' => false,
            'amount_cents' => $amountCents,
            'currency' => 'EGP',
            'merchant_order_id' => $merchantOrderId,
            'items' => $items,
        ]);

        if (!$response->successful()) {
            Log::error('Paymob order registration failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \Exception('Paymob order registration failed: ' . ($response->json('message') ?? 'Unknown error'));
        }

        return $response->json('id');
    }

    /**
     * Get a payment key for the iframe.
     */
    public function getPaymentKey(string $authToken, int $paymobOrderId, int $amountCents, array $billingData, ?string $integrationId = null): string
    {
        $integrationId = $integrationId ?? Setting::get('paymob_integration_id');

        $response = Http::post("{$this->baseUrl}/api/acceptance/payment_keys", [
            'auth_token' => $authToken,
            'amount_cents' => $amountCents,
            'expiration' => 3600,
            'order_id' => $paymobOrderId,
            'billing_data' => $billingData,
            'currency' => 'EGP',
            'integration_id' => (int) $integrationId,
        ]);

        if (!$response->successful()) {
            Log::error('Paymob payment key generation failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \Exception('Paymob payment key generation failed: ' . ($response->json('message') ?? 'Unknown error'));
        }

        return $response->json('token');
    }

    /**
     * Get the iframe payment URL.
     */
    public function getPaymentUrl(string $paymentToken): string
    {
        $iframeId = Setting::get('paymob_iframe_id');

        return "{$this->baseUrl}/api/acceptance/iframes/{$iframeId}?payment_token={$paymentToken}";
    }

    /**
     * Orchestrate the full payment flow: authenticate, register order, get payment key, return URL.
     */
    public function processPayment(string $merchantOrderId, int $amountCents, array $items, array $billingData): array
    {
        // Step 1: Authenticate
        $authToken = $this->authenticate();

        // Step 2: Register order
        $paymobOrderId = $this->registerOrder($authToken, $merchantOrderId, $amountCents, $items);

        // Step 3: Get payment key
        $paymentToken = $this->getPaymentKey($authToken, $paymobOrderId, $amountCents, $billingData);

        // Step 4: Build payment URL
        $paymentUrl = $this->getPaymentUrl($paymentToken);

        return [
            'payment_url' => $paymentUrl,
            'payment_token' => $paymentToken,
            'paymob_order_id' => $paymobOrderId,
        ];
    }

    /**
     * Verify HMAC signature from Paymob webhook/callback.
     */
    public function verifyHmac(array $data, string $hmac): bool
    {
        $hmacSecret = Setting::get('paymob_hmac_secret');

        $concatenatedString = $data['amount_cents']
            . $data['created_at']
            . $data['currency']
            . $data['error_occured']
            . $data['has_parent_transaction']
            . $data['id']
            . $data['integration_id']
            . $data['is_3d_secure']
            . $data['is_auth']
            . $data['is_capture']
            . $data['is_refunded']
            . $data['is_standalone_payment']
            . $data['is_voided']
            . $data['order.id']
            . $data['owner']
            . $data['pending']
            . $data['source_data.pan']
            . $data['source_data.sub_type']
            . $data['source_data.type']
            . $data['success'];

        $calculatedHmac = hash_hmac('sha512', $concatenatedString, $hmacSecret);

        return hash_equals($calculatedHmac, $hmac);
    }

    /**
     * Inquire about a transaction status.
     */
    public function inquireTransaction(string $authToken, string $transactionId): array
    {
        $response = Http::withToken($authToken)->get("{$this->baseUrl}/api/acceptance/transactions/{$transactionId}");

        if (!$response->successful()) {
            Log::error('Paymob transaction inquiry failed', [
                'status' => $response->status(),
                'body' => $response->json(),
            ]);
            throw new \Exception('Paymob transaction inquiry failed: ' . ($response->json('message') ?? 'Unknown error'));
        }

        return $response->json();
    }
}
