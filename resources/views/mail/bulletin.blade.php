@component('mail::message')
# {{ __('Boletín de :institution', ['institution' => $institution->name]) }}

{{ __('Adjunto encontrarás el boletín de retos completados entre :start y :end.', [
    'start' => $periodStart->format('d/m/Y'),
    'end' => $periodEnd->format('d/m/Y'),
]) }}

{{ __('Gracias por acompañar el proceso de tu estudiante.') }}

{{ config('app.name') }}
@endcomponent
