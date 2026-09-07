<?php

namespace App\Services\Wompi;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WompiClient
{
    public function __construct(
        protected string $privateKey,
        protected string $publicKey = '',
        protected string $integritySecret = '',
        protected string $baseUrl = 'https://production.wompi.co/v1',
    ) {}

    /**
     * Create a Payment Link for a fixed amount. Returns the Wompi payment
     * link id (used as our `wompi_reference`) and the checkout URL to send
     * to the institution.
     *
     * @return array{id: string, url: string}
     */
    public function createPaymentLink(string $name, string $description, int $amountInCents, string $currency = 'COP'): array
    {
        $response = Http::withToken($this->privateKey)
            ->post("{$this->baseUrl}/payment_links", [
                'name' => $name,
                'description' => $description,
                'single_use' => true,
                'collect_shipping' => false,
                'amount_in_cents' => $amountInCents,
                'currency' => $currency,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Wompi payment link creation failed: {$response->body()}");
        }

        $id = $response->json('data.id');

        return [
            'id' => $id,
            'url' => "https://checkout.wompi.co/l/{$id}",
        ];
    }

    /**
     * Register a reusable payment source (card, Nequi, etc.) from a token obtained
     * through Wompi's tokenization widget/API, so future charges can be made
     * automatically (Credential On File) without the customer intervening again.
     * We only ever handle the token/id Wompi gives us — never raw card data.
     *
     * @return array{id: int|string, status: string, type: string, last_four: ?string}
     */
    public function createPaymentSource(string $type, string $token, string $customerEmail): array
    {
        $acceptance = $this->acceptanceTokens();

        $response = Http::withToken($this->privateKey)
            ->post("{$this->baseUrl}/payment_sources", [
                'type' => $type,
                'token' => $token,
                'customer_email' => $customerEmail,
                'acceptance_token' => $acceptance['acceptance_token'],
                'accept_personal_auth' => $acceptance['accept_personal_auth'],
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Wompi payment source creation failed: {$response->body()}");
        }

        $data = $response->json('data');

        return [
            'id' => $data['id'],
            'status' => $data['status'],
            'type' => $data['type'] ?? $type,
            'last_four' => $data['public_data']['last_four'] ?? null,
        ];
    }

    /**
     * Charge an existing payment source automatically (Credential On File), used
     * for recurring subscription billing instead of a new Payment Link each cycle.
     *
     * @return array{id: string, status: string}
     */
    public function createRecurrentTransaction(int|string $paymentSourceId, int $amountInCents, string $reference, string $customerEmail, string $currency = 'COP'): array
    {
        $signature = hash('sha256', "{$reference}{$amountInCents}{$currency}{$this->integritySecret}");

        $response = Http::withToken($this->privateKey)
            ->post("{$this->baseUrl}/transactions", [
                'amount_in_cents' => $amountInCents,
                'currency' => $currency,
                'signature' => $signature,
                'customer_email' => $customerEmail,
                'reference' => $reference,
                'payment_source_id' => $paymentSourceId,
                'recurrent' => true,
            ]);

        if ($response->failed()) {
            throw new RuntimeException("Wompi recurrent transaction failed: {$response->body()}");
        }

        $data = $response->json('data');

        return [
            'id' => $data['id'],
            'status' => $data['status'],
        ];
    }

    /**
     * Pre-signed acceptance tokens (terms of service + personal data policy),
     * required by Wompi to create any payment source or transaction.
     *
     * @return array{acceptance_token: ?string, accept_personal_auth: ?string}
     */
    protected function acceptanceTokens(): array
    {
        $response = Http::get("{$this->baseUrl}/merchants/{$this->publicKey}");

        if ($response->failed()) {
            throw new RuntimeException("Wompi merchant lookup failed: {$response->body()}");
        }

        return [
            'acceptance_token' => $response->json('data.presigned_acceptance.acceptance_token'),
            'accept_personal_auth' => $response->json('data.presigned_personal_data_auth.acceptance_token'),
        ];
    }
}
