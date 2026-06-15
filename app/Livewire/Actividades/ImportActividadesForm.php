<?php

namespace App\Livewire\Actividades;

use Livewire\Component;
use Livewire\WithFileUploads;
use App\Services\ExcelImporterService;
use App\Models\CargaExcel;
use App\Models\Actividad;
use App\Models\Unidad;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use App\Mail\NuevasActividadesPendientes;
use App\Services\ExcelService;
use Illuminate\Support\Facades\Cache;

class ImportActividadesForm extends Component
{
    use WithFileUploads;
    private const MANDATORY_FIELDS = Actividad::MANDATORY_FIELDS_TO_CREATE_ACTIVIDAD;
    public  $excelFile;
    public int $step = 1; // 1: Subida, 2: Previsualización, 3: Cuenta regresiva (Confirmación), 4: Éxito

    // Datos de la carga
    public array $headers = [];
    public array $previewRows = [];
    public array $warnings = [];
    public int $totalRows = 0;
    public string $tempFilePath = '';
    public string $originalFileName = '';

    // Temporizador
    public int $countdown = 10;
    public bool $isCountingDown = false;

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
            $this->normalizarTexto('PMA LOS ANGELES')
            => $this->normalizarTexto('PMA CONCEPCIÓN'),
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
        \Illuminate\Support\Facades\Gate::authorize('mutate');

        $this->validate();

        // Guardar archivo de forma segura en disco temporal
        $path = $this->excelFile->store('temp-imports');
        $this->tempFilePath = Storage::path($path);
        $this->originalFileName = $this->excelFile->getClientOriginalName();

        try {
            // Importar y validar cabeceras estructuradas utilizando el pipeline unificado del servicio
            $data = $importer->importActividades($this->tempFilePath);

            $cacheKey = 'excel_import_' . Auth::id();

            $this->headers = $data['headers'];
            $allRows = $data['rows'];

            // Ejecutar análisis de advertencias en memoria O(1) con normalización
            $this->warnings = [];
            $mapaNormalizado = $this->obtenerMapaUnidadesNormalizado();

            // Consultar preventivamente colisiones de código de actividad COD en un único viaje redondo a BD (O(1))
            $incomingCods = array_filter(array_map(fn($row) => trim((string)($row['COD'] ?? '')), $allRows));
            $existingCods = Actividad::query()->whereIn('COD', $incomingCods)->pluck('COD')->toArray();
            $existingCodsMap = array_flip($existingCods);

            $validRows = [];

            foreach ($allRows as $index => $row) {
                $rowNum = $index + 2; // Fila Excel física
                $hasError = false;

                // Validar campos obligatorios inferidos de la migración
                foreach (self::MANDATORY_FIELDS as $field) {
                    if (!isset($row[$field]) || trim((string)$row[$field]) === '') {
                        $this->warnings[] = "Fila #{$rowNum}: Falta el campo obligatorio requerido '{$field}'";
                        $hasError = true;
                    }
                }

                // Validar colisiones del identificador único COD en base de datos
                $codRaw = trim((string)($row['COD'] ?? ''));
                if ($codRaw !== '' && isset($existingCodsMap[$codRaw])) {
                    $this->warnings[] = "Fila #{$rowNum}: El código de actividad '{$codRaw}' ya se encuentra registrado y persistido en la plataforma";
                    $hasError = true;
                }
                // Validar correspondencia territorial de la unidad
                $unidadNombreRaw = trim($row['UNIDAD'] ?? '');
                $unidadIdAsignada = $this->resolverUnidadId(
                    $unidadNombreRaw,
                    $mapaNormalizado
                );
                if ($unidadNombreRaw === '') {
                    $this->warnings[] = "Fila #{$rowNum}: El campo 'UNIDAD' se encuentra vacío";
                    $hasError = true;
                } elseif ($unidadIdAsignada === null) {
                    $this->warnings[] = "Fila #{$rowNum}: La unidad '{$unidadNombreRaw}' no coincide con ningún registro del catálogo del sistema";
                    $hasError = true;
                }

                // Solo agregar a la colección limpia si no presenta errores estructurales
                if (!$hasError) {
                    $validRows[] = $row;
                }
            }

            // Excluir filas con advertencias de la previsualización y el conteo total
            $this->totalRows = count($validRows);
            $this->previewRows = array_slice($validRows, 0, 10);
            Cache::put($cacheKey, [
                    'headers' => $data['headers'],
                    'rows' => $validRows,
            ], 1200); // Duración de 20 minutos
            $this->step = 2;
        } catch (\Exception $e) {
            $this->cleanupTempFile();
            session()->flash('error', 'Error en la validación del archivo: ' . $e->getMessage());
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
        \Illuminate\Support\Facades\Gate::authorize('mutate');

        if (!$this->isCountingDown) {
            return;
        }

        $this->isCountingDown = false;

        try {
            // Recuperar datos parseados directamente de la caché server-side
            $cacheKey = 'excel_import_' . Auth::id();
            $data = \Illuminate\Support\Facades\Cache::get($cacheKey);

            // Re-parseo defensivo de respaldo (Fallback) únicamente si la caché expiró o fue eliminada
            if (!$data) {
               return redirect()->route('actividades.importar');
            }

            $allRows = $data['rows'];

            // Colección para registrar los IDs únicos de las unidades que reciben actividades en este lote
            $unidadesAfectadas = [];

            DB::transaction(function () use ($allRows, &$unidadesAfectadas) {

                // Registrar lote de control Excel
                $carga = CargaExcel::create([
                    'user_id' => Auth::id(),
                    'nombre_archivo' => $this->originalFileName,
                    'total_filas' => $this->totalRows,
                    'estado' => 'PROCESADA'
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
                    if (!$unidadIdAsignada) {
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
                    if (!in_array($unidadIdAsignada, $unidadesAfectadas)) {
                        $unidadesAfectadas[] = $unidadIdAsignada;
                    }
                }

                // Inserción masiva en base de datos en un solo viaje redondo de red
                if (!empty($actividadesParaInsertar)) {
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
                return $u->user && !empty($u->user->email);
            })->groupBy(function ($u) {
                return $u->user->email;
            });

            foreach ($unidadesAgrupadas as $correoDestinatario => $grupoUnidades) {
                // Seleccionar la primera unidad del grupo como representante para la construcción de la plantilla
                $unidadRepresentante = $grupoUnidades->first();
                Mail::to($correoDestinatario)->queue(new NuevasActividadesPendientes($unidadRepresentante));
            }

            // Limpieza inmediata de la caché de importación para liberar memoria del servidor
            $cacheKey = 'excel_import_' . Auth::id();
            \Illuminate\Support\Facades\Cache::forget($cacheKey);

            $this->cleanupTempFile();
            $this->step = 4;
            session()->flash('success', "¡Excelente! Se han importado exitosamente {$this->totalRows} actividades e iniciado las colas de notificación.");
        } catch (\Exception $e) {
            session()->flash('error', 'Fallo al persistir registros en base de datos: ' . $e->getMessage());
            $this->step = 2;
        }
    }

    public function resetForm()
    {
        // Limpiar la caché de importación si se cancela o reinicia el formulario
        $cacheKey = 'excel_import_' . Auth::id();
        \Illuminate\Support\Facades\Cache::forget($cacheKey);

        $this->reset(['excelFile', 'step', 'headers', 'previewRows', 'warnings', 'totalRows', 'tempFilePath', 'originalFileName', 'countdown', 'isCountingDown']);
    }

    private function cleanupTempFile()
    {
        if (!empty($this->tempFilePath) && file_exists($this->tempFilePath)) {
            @unlink($this->tempFilePath);
        }
    }

    public function render()
    {
        return view('livewire.actividades.import-actividades-form');
    }
}
