<?php

namespace App\Console\Commands;

use App\Models\InstitutionSubscription;
use App\Models\Invoice;
use App\Notifications\PaymentLinkReady;
use App\Services\Wompi\WompiClient;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;

#[Signature('billing:generate-invoices')]
#[Description('Generate an invoice for every active subscription whose current billing period has ended.')]
class GenerateSubscriptionInvoicesCommand extends Command
{
    protected $signature = 'billing:generate-invoices';

    protected $description = 'Generate an invoice for every active subscription whose current billing period has ended.';

    public function __construct(protected WompiClient $wompi)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $subscriptions = InstitutionSubscription::query()
            ->where('status', 'active')
            ->with('institution')
            ->get()
            ->filter->isDueForInvoicing();

        if ($subscriptions->isEmpty()) {
            $this->info(__('There are no subscriptions pending billing today.'));

            return self::SUCCESS;
        }

        foreach ($subscriptions as $subscription) {
            [$start, $end] = $subscription->nextPeriod();
            $invoice = Invoice::generateFor($subscription, $start, $end);

            match ($invoice->payment_method) {
                'stripe' => $this->sendToStripe($invoice),
                'wompi' => $this->sendToWompi($invoice),
                default => null, // manual: pending for the administrative team
            };

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

    /**
     * Wompi has no invoice concept — instead we create a one-time Payment
     * Link for the exact amount and store its id as `wompi_reference` so
     * the webhook controller can match the later `transaction.updated`
     * event back to this invoice.
     */
    protected function sendToWompi(Invoice $invoice): void
    {
        $link = $this->wompi->createPaymentLink(
            name: $invoice->number,
            description: "Suscripción {$invoice->institution->name} — {$invoice->period_start->format('M Y')}",
            amountInCents: (int) round($invoice->total * 100),
            currency: $invoice->currency,
        );

        $invoice->update(['wompi_reference' => $link['id']]);

        // Send payment link to institution's contact email
        $invoice->institution->notify(new PaymentLinkReady($invoice));
    }
}
