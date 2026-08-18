<section class="mx-auto w-full max-w-3xl pb-28">
    <div class="mb-8">
        <flux:button variant="ghost" size="sm" icon="arrow-left" href="{{ $backUrl }}" wire:navigate class="mb-4">
            {{ __('Volver') }}
        </flux:button>

        <div class="rounded-xl border border-teal-border bg-teal-bg px-6 py-5">
            <flux:heading size="xl" class="text-teal-deep!">{{ __('Carga masiva de profesores') }}</flux:heading>
            <flux:text class="text-brand-text-muted!">
                {{ __('Crea varios profesores a la vez subiendo un archivo de Excel.') }}
            </flux:text>
            <flux:text class="mt-1 text-sm font-semibold text-teal-deep!">
                {{ __('Institución: :name', ['name' => $institutionName]) }}
            </flux:text>
        </div>

        <flux:text class="mt-3 text-sm text-brand-text-muted!">
            {{ __('¿Solo necesitas crear uno?') }}
            <flux:link href="{{ route('actors.teachers.create', ['institution' => $institutionUuid]) }}" wire:navigate>{{ __('Crear un profesor individual') }}</flux:link>
        </flux:text>
    </div>

    <div class="mb-6 rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                <flux:icon icon="information-circle" variant="micro" class="size-5" />
            </span>
            <div>
                <flux:heading size="lg">{{ __('Antes de empezar') }}</flux:heading>
                <flux:text class="text-sm text-brand-text-muted!">{{ __('Lee esto con atención para evitar errores en la carga.') }}</flux:text>
            </div>
        </div>

        <ul class="list-disc space-y-2 pl-5 text-sm text-brand-text">
            <li>{{ __('Descarga la plantilla y NO cambies el orden ni el nombre de las columnas. La primera fila debe ser siempre el encabezado.') }}</li>
            <li>{{ __('Columnas requeridas: Nombres, Apellidos, Tipo de documento, Numero de documento, Celular, Correo. La columna Grupos es opcional.') }}</li>
            <li>{{ __('Tipo de documento: usa el código CC (cédula de ciudadanía), CE (cédula de extranjería) o PA (pasaporte).') }}</li>
            <li>{{ __('Grupos: escribe los nombres EXACTOS de los salones separados por coma (ej: "1°A, 2°B"). Revisa la segunda hoja de la plantilla con los grupos disponibles en tu institución. Puedes dejarlo vacío y asignar grupos después.') }}</li>
            <li>{{ __('El correo se usará como usuario de acceso: cada profesor recibirá un correo para crear su propia contraseña, igual que al crear un profesor de forma individual.') }}</li>
            <li>{{ __('Formatos aceptados: .xlsx, .xls o .csv, máximo 5 MB y :max filas de datos por archivo.', ['max' => 200]) }}</li>
            <li>{{ __('No incluyas fórmulas, enlaces ni código en las celdas: el sistema los ignora o los rechaza por seguridad.') }}</li>
            <li>{{ __('Si una fila tiene errores (documento o correo repetido, datos incompletos, etc.) esa fila se omite y las demás se procesan igual; al final verás el detalle de cada fila con error.') }}</li>
        </ul>

        <div class="mt-5">
            <flux:button icon="arrow-down-tray" wire:click="downloadTemplate">{{ __('Descargar plantilla de Excel') }}</flux:button>
        </div>
    </div>

    <div class="rounded-xl border border-zinc-200 bg-white p-6 shadow-sm dark:border-zinc-700 dark:bg-zinc-900">
        <div class="mb-4 flex items-start gap-3">
            <span class="flex size-10 shrink-0 items-center justify-center rounded-lg bg-teal-bg text-teal-deep">
                <flux:icon icon="arrow-up-tray" variant="micro" class="size-5" />
            </span>
            <div>
                <flux:heading size="lg">{{ __('Subir archivo') }}</flux:heading>
                <flux:text class="text-sm text-brand-text-muted!">{{ __('Selecciona el archivo diligenciado con la plantilla.') }}</flux:text>
            </div>
        </div>

        <form wire:submit="import" class="space-y-4">
            <flux:input type="file" wire:model="file" :label="__('Archivo (.xlsx, .xls o .csv)')" accept=".xlsx,.xls,.csv" />

            <div wire:loading wire:target="import" class="flex items-center gap-2 text-sm text-brand-text-muted!">
                <flux:icon.loading class="size-4" />
                {{ __('Procesando archivo, esto puede tardar unos segundos...') }}
            </div>

            <div class="flex justify-end">
                <flux:button variant="primary" type="submit" wire:loading.attr="disabled" wire:target="import">
                    {{ __('Cargar profesores') }}
                </flux:button>
            </div>
        </form>

        @if ($createdCount !== null)
            <div class="mt-6 space-y-4">
                <div class="rounded-lg bg-teal-bg px-4 py-3 text-sm font-semibold text-teal-deep">
                    {{ __(':count profesores creados correctamente.', ['count' => $createdCount]) }}
                    @if (count($rowErrors))
                        {{ __(':count filas con errores (detalle abajo).', ['count' => count($rowErrors)]) }}
                    @endif
                </div>

                @if (count($rowErrors))
                    <div class="overflow-hidden rounded-lg border border-red-200 dark:border-red-900">
                        <table class="w-full text-left text-sm">
                            <thead class="bg-red-50 text-xs font-semibold uppercase text-red-700 dark:bg-red-950/40 dark:text-red-400">
                                <tr>
                                    <th class="px-3 py-2">{{ __('Fila') }}</th>
                                    <th class="px-3 py-2">{{ __('Error') }}</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-red-100 dark:divide-red-900/50">
                                @foreach ($rowErrors as $error)
                                    <tr>
                                        <td class="px-3 py-2 font-semibold text-red-700 dark:text-red-400">{{ $error['row'] }}</td>
                                        <td class="px-3 py-2 text-brand-text">{{ $error['message'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            </div>
        @endif
    </div>
</section>
