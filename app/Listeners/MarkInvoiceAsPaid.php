<?php

namespace App\Listeners;

use App\Models\Invoice;
use Laravel\Cashier\Events\WebhookHandled;

class MarkInvoiceAsPaid
{
    /**
     * Cashier fires this for every Stripe webhook it processes; we only care
     * about the event that confirms a customer's invoice was actually paid.
     */
    public function handle(WebhookHandled $event): void
    {
        if (($event->payload['type'] ?? null) !== 'invoice.paid') {
            return;
        }

        $stripeInvoiceId = $event->payload['data']['object']['id'] ?? null;

        if (! $stripeInvoiceId) {
            return;
        }

        Invoice::where('stripe_invoice_id', $stripeInvoiceId)->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);
    }
}
