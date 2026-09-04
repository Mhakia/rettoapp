<?php

namespace App\Notifications;

use App\Models\Invoice;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PaymentLinkReady extends Notification
{
    public function __construct(private Invoice $invoice) {}

    /**
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $paymentUrl = $this->invoice->wompi_reference
            ? 'https://checkout.wompi.co/l/'.$this->invoice->wompi_reference
            : '#';

        return (new MailMessage)
            ->subject(__('Enlace de pago — Factura :number', ['number' => $this->invoice->number]))
            ->greeting(__('¡Hola!'))
            ->line(__('Se generó la factura :number para el período :period.', [
                'number' => $this->invoice->number,
                'period' => $this->invoice->period_start->format('F Y'),
            ]))
            ->line(__('Monto total a pagar: :amount :currency', [
                'amount' => number_format($this->invoice->total, 2, ',', '.'),
                'currency' => $this->invoice->currency,
            ]))
            ->action(__('Ir al enlace de pago'), $paymentUrl)
            ->line(__('Si tienes dudas sobre esta factura, contacta con nuestro equipo de facturación.'));
    }
}
