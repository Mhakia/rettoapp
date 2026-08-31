<?php

namespace App\Http\Controllers;

use App\Models\Invoice;
use App\Services\Wompi\WompiSignatureValidator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WompiWebhookController extends Controller
{
    public function __invoke(Request $request, WompiSignatureValidator $validator): JsonResponse
    {
        $payload = $request->all();

        // Always answer 200 once we've looked at the event — anything else
        // makes Wompi retry the same event up to 3 times over 24 hours.
        if (! $validator->isValid($payload)) {
            Log::warning('Wompi webhook: invalid checksum, ignoring event.', [
                'event' => $payload['event'] ?? null,
                'reference' => data_get($payload, 'data.transaction.reference'),
                'status' => data_get($payload, 'data.transaction.status'),
                'ip' => $request->ip(),
            ]);

            return response()->json(['ignored' => true], 200);
        }

        if (($payload['event'] ?? null) === 'transaction.updated') {
            $this->handleTransactionUpdated($payload['data']['transaction'] ?? []);
        }

        return response()->json(['received' => true], 200);
    }

    /**
     * @param  array<string, mixed>  $transaction
     */
    protected function handleTransactionUpdated(array $transaction): void
    {
        $reference = $transaction['payment_link_id'] ?? $transaction['reference'] ?? null;
        $status = $transaction['status'] ?? null;

        if (! $reference || $status !== 'APPROVED') {
            return;
        }

        $invoice = Invoice::where('wompi_reference', $reference)->first();

        if (! $invoice || $invoice->status === 'paid') {
            return; // unknown reference, or already processed on a retry
        }

        $invoice->update([
            'status' => 'paid',
            'payment_method' => 'wompi',
            'paid_at' => now(),
        ]);
    }
}
