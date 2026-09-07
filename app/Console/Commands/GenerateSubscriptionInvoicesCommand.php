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
     * Wompi has no invoice concept. If the institution already registered a
     * reusable payment source, charge it automatically (Credential On File) —
     * otherwise fall back to creating a one-time Payment Link and notifying
     * the institution, exactly as before.
     */
    protected function sendToWompi(Invoice $invoice): void
    {
        $subscription = $invoice->subscription;

        if ($subscription->wompi_payment_source_id) {
            $this->chargeWompiPaymentSource($invoice, $subscription);

            return;
        }

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

    /**
     * Charge the institution's saved payment source directly. `wompi_reference`
     * is set to our own invoice number (same value we send as `reference`), so
     * the webhook controller can match the resulting `transaction.updated`
     * event back to this invoice exactly like it already does for Payment Links.
     */
    protected function chargeWompiPaymentSource(Invoice $invoice, InstitutionSubscription $subscription): void
    {
        $this->wompi->createRecurrentTransaction(
            paymentSourceId: $subscription->wompi_payment_source_id,
            amountInCents: (int) round($invoice->total * 100),
            reference: $invoice->number,
            customerEmail: $invoice->institution->contact_email,
            currency: $invoice->currency,
        );

        $invoice->update(['wompi_reference' => $invoice->number]);
    }
}
