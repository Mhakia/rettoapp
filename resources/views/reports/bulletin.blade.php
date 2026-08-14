<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <style>
        body { font-family: sans-serif; font-size: 12px; color: #1f2937; }
        h1 { font-size: 18px; }
        table { width: 100%; border-collapse: collapse; margin-top: 12px; }
        th, td { border: 1px solid #d1d5db; padding: 6px 8px; text-align: left; }
        th { background-color: #f3f4f6; }
    </style>
</head>
<body>
    <h1>{{ $institution->name }}</h1>
    <p>
        {{ __('Boletín de :name', ['name' => $guardian->name]) }}<br>
        {{ __('Período: :start - :end', ['start' => $periodStart->format('d/m/Y'), 'end' => $periodEnd->format('d/m/Y')]) }}
    </p>

    <table>
        <thead>
            <tr>
                <th>{{ __('Estudiante') }}</th>
                <th>{{ __('Reto') }}</th>
                <th>{{ __('Puntos') }}</th>
                <th>{{ __('Verificado') }}</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($completions as $completion)
                <tr>
                    <td>{{ $completion->user->name }}</td>
                    <td>{{ $completion->challenge->title }}</td>
                    <td>{{ $completion->points_earned }}</td>
                    <td>{{ $completion->verified_at?->format('d/m/Y') }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <p>{{ __('Total de puntos: :total', ['total' => $completions->sum('points_earned')]) }}</p>
</body>
</html>
