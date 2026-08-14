<section class="w-full space-y-8">
    <flux:heading size="lg">{{ __('Estadísticas de retos') }}</flux:heading>

    <div>
        <flux:heading size="sm">{{ __('Por reto') }}</flux:heading>
        <table class="mt-4 w-full text-sm">
            <thead>
                <tr class="text-left text-gray-500">
                    <th class="py-2">{{ __('Reto') }}</th>
                    <th>{{ __('Enviados') }}</th>
                    <th>{{ __('Verificados') }}</th>
                    <th>{{ __('Rechazados') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($this->byChallenge as $challenge)
                    <tr class="border-t">
                        <td class="py-2">{{ $challenge->title }}</td>
                        <td>{{ $challenge->submitted_count }}</td>
                        <td>{{ $challenge->verified_count }}</td>
                        <td>{{ $challenge->rejected_count }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div>
        <flux:heading size="sm">{{ __('Por institución') }}</flux:heading>
        <div class="mt-4 space-y-2">
            @foreach ($this->byInstitution as $institution => $rows)
                <flux:text class="font-medium">{{ $institution }}</flux:text>
                <flux:text class="text-sm text-gray-500">
                    @foreach ($rows as $row)
                        {{ $row->status }}: {{ $row->total }}@if (! $loop->last), @endif
                    @endforeach
                </flux:text>
            @endforeach
        </div>
    </div>

    <div>
        <flux:heading size="sm">{{ __('Por rol objetivo') }}</flux:heading>
        <div class="mt-4 space-y-2">
            @foreach ($this->byTargetRole as $role => $rows)
                <flux:text class="font-medium">{{ $role }}</flux:text>
                <flux:text class="text-sm text-gray-500">
                    @foreach ($rows as $row)
                        {{ $row->status }}: {{ $row->total }}@if (! $loop->last), @endif
                    @endforeach
                </flux:text>
            @endforeach
        </div>
    </div>
</section>
