<?php

namespace App\Console\Commands;

use App\Models\InstitutionSubscription;
use App\Models\Invoice;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('billing:generate-invoices')]
#[Description('Generate an invoice for every active subscription whose current billing period has ended.')]
class GenerateSubscriptionInvoicesCommand extends Command
{
    protected $signature = 'billing:generate-invoices';

    protected $description = 'Generate an invoice for every active subscription whose current billing period has ended.';

    public function handle(): int
    {
        $subscriptions = InstitutionSubscription::query()
            ->where('status', 'active')
            ->with('institution')
            ->get()
            ->filter->isDueForInvoicing();

        if ($subscriptions->isEmpty()) {
            $this->info('No hay suscripciones pendientes de facturar hoy.');

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            [$start, $end] = $subscription->nextPeriod();
            $invoice = Invoice::generateFor($subscription, $start, $end);

            if ($invoice->payment_method === 'stripe') {
                $this->sendToStripe($invoice);
            }

            $this->info("Factura {$invoice->number} generada para {$subscription->institution->name}: {$invoice->total} {$invoice->currency}");
        }

        return self::SUCCESS;
    }

    /**
     * Push a manually-priced invoice to Stripe via Cashier's one-off invoice
     * item support (our pricing is negotiated per institution, not a Stripe
     * catalog price), then store the returned invoice id for correlation
     * when the payment webhook arrives.
     */
    protected function sendToStripe(Invoice $invoice): void
    {
        $institution = $invoice->institution;

        $stripeInvoice = $institution->invoiceFor(
            $invoice->number,
            (int) round($invoice->total * 100),
            ['currency' => strtolower($invoice->currency)]
        );

        $invoice->update(['stripe_invoice_id' => $stripeInvoice->id]);
    }
}
