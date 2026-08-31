<?php

namespace App\Services\Wompi;

class WompiSignatureValidator
{
    public function __construct(protected string $eventsSecret) {}

    /**
     * Whether the checksum in an incoming Wompi event payload is genuine.
     * Wompi's algorithm: concatenate the values pointed to by
     * `signature.properties` (dot-notation paths into `data`), then the
     * event's `timestamp`, then our events secret — and SHA256 the result.
     *
     * @param  array<string, mixed>  $payload  The full decoded event body.
     */
    public function isValid(array $payload): bool
    {
        $properties = $payload['signature']['properties'] ?? [];
        $expectedChecksum = $payload['signature']['checksum'] ?? null;
        $timestamp = $payload['timestamp'] ?? null;

        if (empty($properties) || ! $expectedChecksum || ! $timestamp) {
            return false;
        }

        $concatenated = collect($properties)
            ->map(fn (string $path) => (string) data_get($payload['data'], $path))
            ->implode('');

        $checksum = hash('sha256', $concatenated.$timestamp.$this->eventsSecret);

        return hash_equals(strtoupper($expectedChecksum), strtoupper($checksum));
    }
}
