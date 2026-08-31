<?php

namespace App\Services\Wompi;

use Illuminate\Support\Facades\Http;
use RuntimeException;

class WompiClient
{
    public function __construct(
        protected string $privateKey,
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
}
