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

class ImportActividadesForm extends Component
{
    use WithFileUploads;

    public $excelFile;
    public int $step = 1; // 1: Subida, 2: Previsualización, 3: Cuenta regresiva (Confirmación), 4: Éxito

    // Datos de la carga
    public array $headers = [];
    public array $previewRows = [];
    public int $totalRows = 0;
    public string $tempFilePath = '';
    public string $originalFileName = '';

    // Temporizador
    public int $countdown = 10;
    public bool $isCountingDown = false;

    public function rules()
    {
        return [
            'excelFile' => 'required|file|mimes:xlsx,xls|max:10240', // Máximo 10MB
        ];
    }

    public function uploadFile(ExcelImporterService $importer)
    {
        $this->validate();

        // Guardar archivo de forma segura en disco temporal
        $path = $this->excelFile->store('temp-imports');
        $this->tempFilePath = Storage::path($path);
        $this->originalFileName = $this->excelFile->getClientOriginalName();

        try {
            // Importar y validar cabeceras estructuradas utilizando el pipeline unificado del servicio
            $data = $importer->importActividades($this->tempFilePath);

            $this->headers = $data['headers'];
            $allRows = $data['rows'];
            $this->totalRows = count($allRows);

            // Muestra limitada de 10 filas para proteger la transmisión de red del componente Livewire
            $this->previewRows = array_slice($allRows, 0, 10);

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
        if (!$this->isCountingDown) {
            return;
        }

        $this->isCountingDown = false;

        try {
            if (!file_exists($this->tempFilePath)) {
                throw new \Exception('La ruta temporal del archivo de actividades ha expirado.');
            }

            // Volver a parsear el archivo completo desde el disco para asegurar persistencia íntegra
            $data = $importer->importActividades($this->tempFilePath);
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

                // Cachear catálogo de unidades para emparejamiento veloz O(1)
                $unidadesMap = Unidad::pluck(
                    'unidad_id',
                    'unidad_nombre'
                )->toArray();

                foreach ($allRows as $row) {

                    $unidadNombre = trim($row['UNIDAD'] ?? '');

                    // Emparejar ID de unidad si coincide con el catálogo
                    $unidadIdAsignada =
                        $unidadesMap[$unidadNombre] ?? null;

                    Actividad::createFromExcelRow(
                        $row,
                        $carga->carga_id,
                        $unidadIdAsignada
                    );

                    // Registrar de forma única la unidad afectada si fue emparejada
                    if ($unidadIdAsignada && !in_array($unidadIdAsignada, $unidadesAfectadas)) {
                        $unidadesAfectadas[] = $unidadIdAsignada;
                    }
                }
            });

            // Despachar un único correo por cada unidad afectada al finalizar con éxito la persistencia
            $unidades = Unidad::whereIn('unidad_id', $unidadesAfectadas)
                ->whereNotNull('unidad_correo')
                ->get();

            foreach ($unidades as $unidad) {
                Mail::to($unidad->unidad_correo)->queue(new NuevasActividadesPendientes($unidad));
            }

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
        $this->reset(['excelFile', 'step', 'headers', 'previewRows', 'totalRows', 'tempFilePath', 'originalFileName', 'countdown', 'isCountingDown']);
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
