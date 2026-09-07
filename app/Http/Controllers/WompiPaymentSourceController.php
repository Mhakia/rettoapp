<?php

namespace App\Http\Controllers;

use App\Services\Wompi\WompiClient;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class WompiPaymentSourceController extends Controller
{
    /**
     * Receives the token posted back by Wompi's tokenization widget
     * (`data-widget-operation="tokenize"`) and registers it as a reusable
     * payment source for the current institution's active subscription.
     *
     * NOTE: Wompi's docs don't state the exact field name used for the form
     * post in tokenize mode; `id`/`token` are the two most likely candidates.
     * Confirm against a real sandbox run before relying on this in production.
     */
    public function __invoke(Request $request, WompiClient $wompi): RedirectResponse
    {
        $token = $request->input('id') ?? $request->input('token');

        if (! $token) {
            Log::warning('Wompi tokenization: no token received in payment source callback.', [
                'ip' => $request->ip(),
            ]);

            return back()->with('billingError', __('No se pudo procesar el nuevo método de pago. Intenta de nuevo.'));
        }

        $institution = Auth::user()->institution;
        $subscription = $institution?->activeSubscription;

        if (! $subscription) {
            abort(404);
        }

        try {
            $paymentSource = $wompi->createPaymentSource(
                type: 'CARD',
                token: $token,
                customerEmail: $institution->contact_email,
            );
        } catch (Throwable $e) {
            Log::error('Wompi payment source creation failed.', ['message' => $e->getMessage()]);

            return back()->with('billingError', __('No se pudo guardar el nuevo método de pago con Wompi.'));
        }

        $subscription->update(['wompi_payment_source_id' => $paymentSource['id']]);

        activity('billing')
            ->causedBy(Auth::user())
            ->performedOn($subscription)
            ->withProperties([
                'wompi_payment_source_id' => $paymentSource['id'],
                'type' => $paymentSource['type'],
                'last_four' => $paymentSource['last_four'],
            ])
            ->log('wompi_payment_source_updated');

        return redirect()->route('billing.edit')->with('billingStatus', __('Método de pago actualizado correctamente.'));
    }
}
