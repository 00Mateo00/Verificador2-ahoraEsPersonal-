<?php

namespace App\Livewire\Actividades;

use App\Models\Actividad;
use App\Models\Unidad;
use App\Services\ExcelImporterService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Gate;
use Livewire\Component;
use Livewire\WithFileUploads;

class ImportActividadesForm extends Component
{
    use WithFileUploads;

    private const MANDATORY_FIELDS = Actividad::MANDATORY_FIELDS_TO_CREATE_ACTIVIDAD;

    private const NOMBRES_MESES = [
        1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
        5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
        9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre',
    ];

    public $excelFile;

    public int $step = 1; // 1: Subida, 2: Previsualización, 3: Cuenta regresiva, 4: Procesando/Enviando, 5: Éxito

    // Control de Mes Estadístico (M.E.)
    public int $mesEstadistico;

    public int $anoEstadistico;

    public string $periodoSeleccionado = '';

    public array $periodosDisponibles = [];

    public bool $todoDuplicado = false;

    public array $existingRowsComparison = [];

    // Datos de la carga
    public array $headers = [];

    public array $previewRows = [];

    public array $warnings = [];

    public int $totalRows = 0;

    public int $omittedRows = 0;

    public string $tempFilePath = '';

    public string $originalFileName = '';

    public string $fileHash = '';

    // Temporizador
    public int $countdown = 10;

    public bool $isCountingDown = false;

    public function mount()
    {
        $this->mesEstadistico = (int) date('m');
        $this->anoEstadistico = (int) date('Y');
    }

    /**
     * Reacts to changes in the selected period and updates statistics.
     */
    public function updatedPeriodoSeleccionado($value)
    {
        if ($value) {
            [$mes, $ano] = explode('_', $value);
            $this->mesEstadistico = (int) $mes;
            $this->anoEstadistico = (int) $ano;
            $this->recalcularPeriodo();
        }
    }

    public function rules()
    {
        return [
            'excelFile' => 'required|file|mimes:xlsx,xls|max:10240', // Máximo 10MB
        ];
    }

    /**
     * Hook automático de Livewire: se dispara cuando el archivo termina de subirse.
     */
    public function updatedExcelFile()
    {
        $this->uploadFile(app(ExcelImporterService::class));
    }

    public function recalcularPeriodo()
    {
        $cacheKey = 'excel_import_'.Auth::id();
        $cachedData = Cache::get($cacheKey);
        if (! $cachedData) {
            return;
        }

        $allRows = $cachedData['allRows'] ?? [];
        $this->warnings = [];
        $this->todoDuplicado = false;
        $this->existingRowsComparison = [];

        $importer = app(ExcelImporterService::class);
        $mapaNormalizado = $importer->obtenerMapaUnidadesNormalizado();

        // Filtrar las filas pertenecientes al periodo seleccionado
        $periodRows = [];
        foreach ($allRows as $row) {
            $rowMes = isset($row['MES']) ? (int) $row['MES'] : null;
            $rowAno = isset($row['AÑO']) ? (int) $row['AÑO'] : null;
            if ($rowMes === $this->mesEstadistico && $rowAno === $this->anoEstadistico) {
                $periodRows[] = $row;
            }
        }

        // Consultar colisiones de código de actividad COD en un único viaje redondo a BD
        $incomingCods = array_filter(array_map(fn ($row) => trim((string) ($row['COD'] ?? '')), $periodRows));
        $existingActividades = Actividad::query()
            ->whereIn('COD', $incomingCods)
            ->get()
            ->keyBy('COD');

        $validRows = [];
        $duplicateCods = [];

        foreach ($periodRows as $index => $row) {
            $rowNum = $index + 2; // Fila Excel física
            $rowErrors = [];

            // Identificador único corporativo (COD) para rotular las advertencias
            $codRaw = trim((string) ($row['COD'] ?? ''));
            $rowLabel = $codRaw !== '' ? "Actividad [{$codRaw}]" : "Fila #{$rowNum} (Sin COD)";

            // 1. Validar campos obligatorios
            foreach (self::MANDATORY_FIELDS as $field) {
                if (! isset($row[$field]) || trim((string) $row[$field]) === '') {
                    $rowErrors[] = "Falta el campo obligatorio requerido '{$field}'";
                }
            }

            // Almacenar duplicidad para agrupamiento posterior
            $isDuplicate = false;
            if ($codRaw !== '' && $existingActividades->has($codRaw)) {
                $duplicateCods[] = $codRaw;
                $isDuplicate = true;
            }

            // Validar correspondencia territorial de la unidad
            $unidadNombreRaw = trim($row['UNIDAD'] ?? '');
            $unidadIdAsignada = $importer->resolverUnidadId(
                $unidadNombreRaw,
                $mapaNormalizado
            );
            if ($unidadNombreRaw === '') {
                $rowErrors[] = "El campo 'UNIDAD' se encuentra vacío";
            } elseif ($unidadIdAsignada === null) {
                $rowErrors[] = "La unidad '{$unidadNombreRaw}' no coincide con ningún registro del catálogo del sistema";
            }

            // Consolidar errores de estructura (excluyendo COD para agruparlo al final)
            if (! empty($rowErrors)) {
                $this->warnings[] = "{$rowLabel}: ".implode(', ', $rowErrors).'.';
            } elseif ($isDuplicate) {
                // Omitir inserción silenciosamente por duplicidad
            } else {
                $validRows[] = $row;
            }
        }

        $duplicateCount = count($duplicateCods);
        $totalPeriodRows = count($periodRows);

        // Agrupar mensajes de advertencia de duplicados según especificaciones
        if ($duplicateCount === 1) {
            $cod1 = $duplicateCods[0];
            $this->warnings[] = "Actividad [{$cod1}]: El código ya se encuentra registrado y persistido en la plataforma.";
        } elseif ($duplicateCount > 1) {
            $codsList = implode(', ', $duplicateCods);
            if ($duplicateCount === $totalPeriodRows) {
                $this->warnings[] = "Todos los códigos ya se encuentran registrados y persistidos en la plataforma: [{$codsList}]";
            } else {
                $this->warnings[] = "Los siguientes códigos ya se encuentran registrados y persistidos en la plataforma: [{$codsList}]";
            }
        }

        // Si todos los códigos a cargar ya existen en la base de datos
        if ($totalPeriodRows > 0 && $duplicateCount === $totalPeriodRows) {
            $this->todoDuplicado = true;

            // Ordenar filas cargadas por COD para coincidencia exacta
            usort($periodRows, fn ($a, $b) => strcmp($a['COD'] ?? '', $b['COD'] ?? ''));

            $this->existingRowsComparison = [];
            foreach ($periodRows as $row) {
                $cod = trim((string) ($row['COD'] ?? ''));
                $dbRow = $existingActividades->get($cod);
                $this->existingRowsComparison[] = [
                    'cargada' => $row,
                    'existente' => $dbRow ? [
                        'MODALIDAD' => $dbRow->MODALIDAD,
                        'TIPO_ACTIVIDAD' => $dbRow->TIPO_ACTIVIDAD,
                        'SUB_TIPO_ACTIVIDAD' => $dbRow->SUB_TIPO_ACTIVIDAD,
                        'COD' => $dbRow->COD,
                        'FECHA' => $dbRow->FECHA ? $dbRow->FECHA->format('Y-m-d') : null,
                        'UNIDAD' => $dbRow->UNIDAD,
                        'FUNCIONARIO' => $dbRow->FUNCIONARIO,
                    ] : null,
                ];
            }
        }

        // Excluir filas con advertencias de la previsualización y el conteo total
        $this->totalRows = count($validRows);
        $this->omittedRows = count($periodRows) - $this->totalRows;
        $this->previewRows = array_slice($validRows, 0, 10);
    }

    public function uploadFile(ExcelImporterService $importer)
    {
        // Defensa en profundidad: Bloquear mutación si el rol del usuario es auditor
        Gate::authorize('mutate');

        $this->validate();

        $this->tempFilePath = $this->excelFile->getRealPath();
        $this->originalFileName = $this->excelFile->getClientOriginalName();

        try {
            // Importar y validar cabeceras estructuradas utilizando el pipeline unificado del servicio
            $data = $importer->importActividades($this->tempFilePath);

            $cacheKey = 'excel_import_'.Auth::id();

            $this->headers = $data['headers'];
            $allRows = $data['rows'];

            // Extraer períodos únicos directamente de las columnas MES y AÑO del Excel
            $periods = [];
            foreach ($allRows as $row) {
                $rowMes = isset($row['MES']) ? (int) $row['MES'] : null;
                $rowAno = isset($row['AÑO']) ? (int) $row['AÑO'] : null;
                if ($rowMes && $rowAno && $rowMes >= 1 && $rowMes <= 12) {
                    $key = "{$rowMes}_{$rowAno}";
                    $periods[$key] = [
                        'mes' => $rowMes,
                        'ano' => $rowAno,
                    ];
                }
            }

            if (empty($periods)) {
                throw new \Exception('No se encontraron períodos válidos (MES y AÑO) en el archivo Excel.');
            }

            // Ordenar períodos únicos para encontrar el más reciente (Año descendente, Mes descendente)
            uasort($periods, function ($a, $b) {
                if ($a['ano'] === $b['ano']) {
                    return $b['mes'] <=> $a['mes'];
                }

                return $b['ano'] <=> $a['ano'];
            });

            // Construir catálogo de opciones de período disponibles para el selector
            $this->periodosDisponibles = [];
            foreach ($periods as $key => $p) {
                $mesName = self::NOMBRES_MESES[$p['mes']] ?? 'Desconocido';
                $this->periodosDisponibles[$key] = "{$mesName} {$p['ano']}";
            }

            // Auto-seleccionar el período más reciente por defecto
            $keys = array_keys($this->periodosDisponibles);
            $this->periodoSeleccionado = $keys[0];
            [$mes, $ano] = explode('_', $this->periodoSeleccionado);
            $this->mesEstadistico = (int) $mes;
            $this->anoEstadistico = (int) $ano;

            // Almacenar todos los datos completos en caché para filtrado reactivo posterior
            Cache::put($cacheKey, [
                'headers' => $data['headers'],
                'allRows' => $allRows,
                'periodosDisponibles' => $this->periodosDisponibles,
            ], 1200); // Duración de 20 minutos

            // Recalcular datos para el período auto-seleccionado
            $this->recalcularPeriodo();

            $this->step = 2;
        } catch (\Exception $e) {
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
        session()->flash('status_neutral', 'Se ha cancelado el envío');
    }

    public function processImport(ExcelImporterService $importer)
    {
        // Defensa en profundidad: Bloquear mutación si el rol del usuario es auditor
        Gate::authorize('mutate');

        // Permitimos procesar si está en la cuenta regresiva o si se forzó el envío manual
        if (! $this->isCountingDown && $this->step !== 4) {
            return;
        }

        $this->isCountingDown = false;

        try {
            // Recuperar datos parseados directamente de la caché server-side
            $cacheKey = 'excel_import_'.Auth::id();
            $data = Cache::get($cacheKey);

            if (! $data) {
                return redirect()->route('actividades.importar');
            }

            $allRows = $data['allRows'] ?? [];
            $validRows = [];
            $mapaNormalizado = $importer->obtenerMapaUnidadesNormalizado();

            $incomingCods = array_filter(array_map(fn ($row) => trim((string) ($row['COD'] ?? '')), $allRows));
            $existingCods = Actividad::query()->whereIn('COD', $incomingCods)->pluck('COD')->toArray();
            $existingCodsMap = array_flip($existingCods);

            foreach ($allRows as $row) {
                $rowMes = isset($row['MES']) ? (int) $row['MES'] : null;
                $rowAno = isset($row['AÑO']) ? (int) $row['AÑO'] : null;

                if ($rowMes !== $this->mesEstadistico || $rowAno !== $this->anoEstadistico) {
                    continue;
                }

                $hasError = false;
                foreach (self::MANDATORY_FIELDS as $field) {
                    if (! isset($row[$field]) || trim((string) $row[$field]) === '') {
                        $hasError = true;
                        break;
                    }
                }
                if ($hasError) {
                    continue;
                }

                $codRaw = trim((string) ($row['COD'] ?? ''));
                if ($codRaw !== '' && isset($existingCodsMap[$codRaw])) {
                    continue;
                }

                $unidadNombreRaw = trim($row['UNIDAD'] ?? '');
                $unidadIdAsignada = $importer->resolverUnidadId($unidadNombreRaw, $mapaNormalizado);
                if ($unidadIdAsignada === null) {
                    continue;
                }

                $validRows[] = $row;
            }

            $importer->storeImportedRows(
                $validRows,
                Auth::id(),
                $this->originalFileName,
                $this->totalRows
            );

            Cache::forget($cacheKey);

            // Redirección inmediata al dashboard con mensaje de éxito
            return redirect()->route('dashboard')->with('success', "¡Excelente! Se han importado exitosamente {$this->totalRows} actividades correspondientes a {$this->periodosDisponibles[$this->periodoSeleccionado]}.");

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

        $this->reset(['excelFile', 'step', 'headers', 'previewRows', 'warnings', 'totalRows', 'tempFilePath', 'originalFileName', 'countdown', 'isCountingDown', 'periodoSeleccionado', 'periodosDisponibles']);
    }

    public function render(ExcelImporterService $importer)
    {
        $validRows = [];

        // Si estamos en el paso de previsualización o superior, calculamos las filas válidas para el frontend
        if ($this->step >= 2) {
            $cacheKey = 'excel_import_'.Auth::id();
            $data = Cache::get($cacheKey);

            if ($data && isset($data['allRows'])) {
                foreach ($data['allRows'] as $row) {
                    $rowMes = isset($row['MES']) ? (int) $row['MES'] : null;
                    $rowAno = isset($row['AÑO']) ? (int) $row['AÑO'] : null;

                    // Solo enviamos al frontend las filas que coinciden con el periodo seleccionado
                    if ($rowMes === $this->mesEstadistico && $rowAno === $this->anoEstadistico) {
                        $validRows[] = $row;
                    }
                }
            }
        }

        return view('livewire.actividades.import-actividades-form', [
            'validRows' => $validRows,
        ]);
    }
}
