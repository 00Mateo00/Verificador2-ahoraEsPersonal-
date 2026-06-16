<?php

namespace App\Livewire\Actividades;

use App\Mail\NuevasActividadesPendientes;
use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Unidad;
use App\Services\ExcelImporterService;
use App\Services\ExcelService;
use App\Services\MailService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportActividadesForm extends Component
{
    use WithFileUploads;

    private const MANDATORY_FIELDS = Actividad::MANDATORY_FIELDS_TO_CREATE_ACTIVIDAD;

    public $excelFile;

    public int $step = 1; // 1: Subida, 2: Previsualización, 3: Cuenta regresiva (Confirmación), 4: Éxito

    // Control de Mes Estadístico (M.E.)
    public int $mesEstadistico;

    public int $anoEstadistico;

    // Datos de la carga
    public array $headers = [];

    public array $previewRows = [];

    public array $warnings = [];

    public int $totalRows = 0;

    public string $tempFilePath = '';

    public string $originalFileName = '';

    public string $fileHash = '';

    // Temporizador
    public int $countdown = 10;

    public bool $isCountingDown = false;

    public function mount()
    {
        // Preselección dinámica por defecto del Mes Estadístico y Año actual
        $this->mesEstadistico = (int) date('m');
        $this->anoEstadistico = (int) date('Y');
    }

    /**
     * Asegura de manera reactiva que no se seleccionen meses futuros si el año se cambia al actual.
     */
    public function updatedAnoEstadistico($value)
    {
        $currentYear = (int) date('Y');
        $currentMonth = (int) date('m');

        if ((int) $value === $currentYear && $this->mesEstadistico > $currentMonth) {
            $this->mesEstadistico = $currentMonth;
        }
    }

    private function normalizarTexto(string $texto): string
    {
        return ExcelService::normalizarTexto($texto);
    }

    private function obtenerMapaUnidadesNormalizado(): array
    {
        // Cruzar con la tabla users para obtener el nombre de la unidad operativa (users.name)
        $unidadesMap = Unidad::query()
            ->join('users', 'unidad.user_id', '=', 'users.id')
            ->pluck('unidad.id', 'users.name')
            ->toArray();

        $resultado = [];

        foreach ($unidadesMap as $nombre => $id) {
            $resultado[$this->normalizarTexto($nombre)] = $id;
        }

        return $resultado;
    }

    private function obtenerRedirecciones(): array
    {
        return [
            $this->normalizarTexto('PMA LOS ANGELES') => $this->normalizarTexto('PMA CONCEPCIÓN'),
        ];
    }

    private function resolverUnidadId(
        string $unidadNombre,
        array $mapaNormalizado
    ): ?int {
        $unidadNombreNorm = $this->normalizarTexto($unidadNombre);

        $redirecciones = $this->obtenerRedirecciones();

        if (isset($redirecciones[$unidadNombreNorm])) {
            $unidadNombreNorm = $redirecciones[$unidadNombreNorm];
        }

        return $mapaNormalizado[$unidadNombreNorm] ?? null;
    }

    public function rules()
    {
        return [
            'excelFile' => 'required|file|mimes:xlsx,xls|max:10240', // Máximo 10MB
        ];
    }

    public function uploadFile(ExcelImporterService $importer)
    {
        // Defensa en profundidad: Bloquear mutación si el rol del usuario es auditor
        Gate::authorize('mutate');

        $this->validate();

        // Guardar archivo de forma segura en disco temporal
        $path = $this->excelFile->store('temp-imports');
        $this->tempFilePath = Storage::path($path);
        $this->originalFileName = $this->excelFile->getClientOriginalName();

        // Calcular huella digital única (SHA-256) del contenido del archivo
        $this->fileHash = hash_file('sha256', $this->tempFilePath);

        // Validar duplicados únicamente si la planilla previa está activa ("PROCESADA")
        $hashDuplicado = CargaExcel::query()
            ->where('hash_archivo', $this->fileHash)
            ->where('estado', 'PROCESADA')
            ->exists();

        if ($hashDuplicado) {
            $this->cleanupTempFile();
            $this->excelFile = null;
            session()->flash('error', 'Esta planilla (o una con exactamente el mismo contenido) ya ha sido procesada de manera exitosa anteriormente.');

            return;
        }

        try {
            // Importar y validar cabeceras estructuradas utilizando el pipeline unificado del servicio
            $data = $importer->importActividades($this->tempFilePath);

            $cacheKey = 'excel_import_'.Auth::id();

            $this->headers = $data['headers'];
            $allRows = $data['rows'];

            // Ejecutar análisis de advertencias en memoria O(1) con normalización
            $this->warnings = [];
            $mapaNormalizado = $this->obtenerMapaUnidadesNormalizado();

            // Consultar preventivamente colisiones de código de actividad COD en un único viaje redondo a BD (O(1))
            $incomingCods = array_filter(array_map(fn ($row) => trim((string) ($row['COD'] ?? '')), $allRows));
            $existingCods = Actividad::query()->whereIn('COD', $incomingCods)->pluck('COD')->toArray();
            $existingCodsMap = array_flip($existingCods);

            $validRows = [];

            foreach ($allRows as $index => $row) {
                $rowNum = $index + 2; // Fila Excel física
                $rowErrors = [];

                // 1. Filtrar en memoria únicamente las actividades del Mes y Año Estadístico seleccionado
                $rowMes = isset($row['MES']) ? (int) $row['MES'] : null;
                $rowAno = isset($row['AÑO']) ? (int) $row['AÑO'] : null;

                if ($rowMes !== $this->mesEstadistico || $rowAno !== $this->anoEstadistico) {
                    continue; // Omitir de forma silenciosa ya que no pertenecen al M.E. actual
                }

                // Identificador único corporativo (COD) para rotular las advertencias
                $codRaw = trim((string) ($row['COD'] ?? ''));
                $rowLabel = $codRaw !== '' ? "Actividad [{$codRaw}]" : "Fila #{$rowNum} (Sin COD)";

                // 2. Validar campos obligatorios inferidos de la migración
                foreach (self::MANDATORY_FIELDS as $field) {
                    if (! isset($row[$field]) || trim((string) $row[$field]) === '') {
                        $rowErrors[] = "Falta el campo obligatorio requerido '{$field}'";
                    }
                }

                // Validar colisiones del identificador único COD en base de datos
                if ($codRaw !== '' && isset($existingCodsMap[$codRaw])) {
                    $rowErrors[] = 'El código ya se encuentra registrado y persistido en la plataforma';
                }

                // Validar correspondencia territorial de la unidad
                $unidadNombreRaw = trim($row['UNIDAD'] ?? '');
                $unidadIdAsignada = $this->resolverUnidadId(
                    $unidadNombreRaw,
                    $mapaNormalizado
                );
                if ($unidadNombreRaw === '') {
                    $rowErrors[] = "El campo 'UNIDAD' se encuentra vacío";
                } elseif ($unidadIdAsignada === null) {
                    $rowErrors[] = "La unidad '{$unidadNombreRaw}' no coincide con ningún registro del catálogo del sistema";
                }

                // Consolidar errores de la fila en un único bloque agrupado si existen
                if (! empty($rowErrors)) {
                    $this->warnings[] = "{$rowLabel}: ".implode(', ', $rowErrors).'.';
                } else {
                    $validRows[] = $row;
                }
            }

            // Excluir filas con advertencias de la previsualización y el conteo total
            $this->totalRows = count($validRows);
            $this->previewRows = array_slice($validRows, 0, 10);
            Cache::put($cacheKey, [
                'headers' => $data['headers'],
                'rows' => $validRows,
                'hash' => $this->fileHash,
            ], 1200); // Duración de 20 minutos
            $this->step = 2;
        } catch (\Exception $e) {
            $this->cleanupTempFile();
            $this->excelFile = null;
            session()->flash('error', 'Error en la validación del archivo: '.$e->getMessage());
        }
    }

    public function startCountdown()
    {
        $this->step = 3;
        $this->countdown = 10;
        $this->isCountingDown = true;
    }

    public function cancelSend()
    {
        $this->isCountingDown = false;
        $this->step = 2;
        session()->flash('success', 'El envío de notificaciones y persistencia de datos fue cancelado.');
    }

    public function processImport(ExcelImporterService $importer)
    {
        // Defensa en profundidad: Bloquear mutación si el rol del usuario es auditor
        Gate::authorize('mutate');

        if (! $this->isCountingDown) {
            return;
        }

        $this->isCountingDown = false;

        try {
            // Recuperar datos parseados directamente de la caché server-side
            $cacheKey = 'excel_import_'.Auth::id();
            $data = Cache::get($cacheKey);

            // Re-parseo defensivo de respaldo (Fallback) únicamente si la caché expiró o fue eliminada
            if (! $data) {
                return redirect()->route('actividades.importar');
            }

            $allRows = $data['rows'];
            $finalHash = $data['hash'] ?? $this->fileHash;

            // Colección para registrar los IDs únicos de las unidades que reciben actividades en este lote
            $unidadesAfectadas = [];

            DB::transaction(function () use ($allRows, $finalHash, &$unidadesAfectadas) {

                // Registrar lote de control Excel
                $carga = CargaExcel::create([
                    'user_id' => Auth::id(),
                    'nombre_archivo' => $this->originalFileName,
                    'hash_archivo' => $finalHash,
                    'total_filas' => $this->totalRows,
                    'estado' => 'PROCESADA',
                ]);

                // Cachear catálogo de unidades para emparejamiento veloz O(1) con normalización
                $mapaNormalizado = $this->obtenerMapaUnidadesNormalizado();

                $actividadesParaInsertar = [];

                // Tabla de redirecciones territoriales dinámicas en memoria (normalizadas)

                foreach ($allRows as $row) {
                    $unidadNombreRaw = trim($row['UNIDAD'] ?? '');
                    // Redirección dinámica si corresponde a Los Ángeles
                    $unidadIdAsignada = $this->resolverUnidadId(
                        $unidadNombreRaw,
                        $mapaNormalizado
                    );

                    // Omitir inserción de registros huérfanos para proteger restricciones "NOT NULL" de la BD
                    if (! $unidadIdAsignada) {
                        continue;
                    }

                    // Formatear array de atributos crudos
                    $actividadData = Actividad::fromExcelRow(
                        $row,
                        $carga->carga_id,
                        $unidadIdAsignada
                    );

                    // Estampar marcas de tiempo requeridas para Bulk Insert síncrono
                    $actividadData['created_at'] = now();
                    $actividadData['updated_at'] = now();

                    $actividadesParaInsertar[] = $actividadData;

                    // Registrar de forma única la unidad afectada si fue emparejada
                    if (! in_array($unidadIdAsignada, $unidadesAfectadas)) {
                        $unidadesAfectadas[] = $unidadIdAsignada;
                    }
                }

                // Inserción masiva en base de datos en un solo viaje redondo de red
                if (! empty($actividadesParaInsertar)) {
                    Actividad::insert($actividadesParaInsertar);
                }
            });

            // Cargar la relación 'user' para acceder al correo electrónico de las unidades afectadas
            $unidades = Unidad::query()
                ->with('user')
                ->whereIn('id', $unidadesAfectadas)
                ->get();

            // Filtrar unidades válidas y agruparlas por el email de su usuario operador asociado
            $unidadesAgrupadas = $unidades->filter(function ($u) {
                return $u->user && ! empty($u->user->email);
            })->groupBy(function ($u) {
                return $u->user->email;
            });

            foreach ($unidadesAgrupadas as $correoDestinatario => $grupoUnidades) {
                // Seleccionar la primera unidad del grupo como representante para la construcción de la plantilla
                $unidadRepresentante = $grupoUnidades->first();
                MailService::sendSafe(
                    $correoDestinatario,
                    new NuevasActividadesPendientes($unidadRepresentante),
                    ['unidad_id' => $unidadRepresentante->id]
                );
            }

            // Limpieza inmediata de la caché de importación para liberar memoria del servidor
            $cacheKey = 'excel_import_'.Auth::id();
            Cache::forget($cacheKey);

            $this->cleanupTempFile();
            $this->step = 4;
            session()->flash('success', "¡Excelente! Se han importado exitosamente {$this->totalRows} actividades e iniciado las colas de notificación.");
        } catch (\Exception $e) {
            session()->flash('error', 'Fallo al persistir registros en base de datos: '.$e->getMessage());
            $this->step = 2;
        }
    }

    public function resetForm()
    {
        // Limpiar la caché de importación si se cancela o reinicia el formulario
        $cacheKey = 'excel_import_'.Auth::id();
        Cache::forget($cacheKey);

        $this->reset(['excelFile', 'step', 'headers', 'previewRows', 'warnings', 'totalRows', 'tempFilePath', 'originalFileName', 'countdown', 'isCountingDown']);
    }

    private function cleanupTempFile()
    {
        if (! empty($this->tempFilePath) && file_exists($this->tempFilePath)) {
            @unlink($this->tempFilePath);
        }
    }

    public function render()
    {
        return view('livewire.actividades.import-actividades-form');
    }
}
