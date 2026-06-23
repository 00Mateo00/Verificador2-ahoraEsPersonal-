<?php

namespace App\Services;

use App\Mail\NuevasActividadesPendientes;
use App\Models\Actividad;
use App\Models\CargaExcel;
use App\Models\Unidad;
use Illuminate\Support\Facades\DB;

class ExcelImporterService
{

    public function __construct(
        private ExcelService $excelService
    ) {}

    private function normalizarTexto(string $texto): string
    {
        return ExcelService::normalizarTexto($texto);
    }

    public function obtenerMapaUnidadesNormalizado(): array
    {
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
        $redirecciones = config('excel_ingestion.redirecciones', []);
        $resultado = [];

        foreach ($redirecciones as $origen => $destino) {
            $resultado[$this->normalizarTexto($origen)] = $this->normalizarTexto($destino);
        }

        return $resultado;
    }

    public function resolverUnidadId(
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

    /**
     * Procesa la inserción masiva en base de datos y despacha las notificaciones por correo de forma segura.
     */
    public function storeImportedRows(
        array $validRows,
        int $userId,
        string $originalFileName,
        int $totalRows
    ): void {
        $unidadesAfectadas = [];

        DB::transaction(function () use ($validRows, $userId, $originalFileName, $totalRows, &$unidadesAfectadas) {
            // Registrar lote de control Excel
            $carga = CargaExcel::create([
                'user_id' => $userId,
                'nombre_archivo' => $originalFileName,
                'total_filas' => $totalRows,
                'estado' => 'PROCESADA',
            ]);

            // Cachear catálogo de unidades para emparejamiento veloz O(1) con normalización
            $mapaNormalizado = $this->obtenerMapaUnidadesNormalizado();

            $actividadesParaInsertar = [];

            foreach ($validRows as $row) {
                $unidadNombreRaw = trim($row['UNIDAD'] ?? '');
                $unidadIdAsignada = $this->resolverUnidadId(
                    $unidadNombreRaw,
                    $mapaNormalizado
                );

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
                if (!in_array($unidadIdAsignada, $unidadesAfectadas, true)) {
                    $unidadesAfectadas[] = $unidadIdAsignada;
                }
            }

            // Inserción masiva en base de datos en un solo viaje redondo de red
            if (!empty($actividadesParaInsertar)) {
                Actividad::insert($actividadesParaInsertar);
            }
        });

        // Recuperar y agrupar las unidades afectadas para notificar de forma agrupada
        $unidades = Unidad::query()
            ->with('user')
            ->whereIn('id', $unidadesAfectadas)
            ->get();

        $unidadesAgrupadas = $unidades->filter(function ($u) {
            return $u->user && !empty($u->user->email);
        })->groupBy(function ($u) {
            return $u->user->email;
        });

        foreach ($unidadesAgrupadas as $correoDestinatario => $grupoUnidades) {
            $unidadRepresentante = $grupoUnidades->first();
            MailService::sendSafe(
                $correoDestinatario,
                new NuevasActividadesPendientes($unidadRepresentante),
                ['unidad_id' => $unidadRepresentante->id]
            );
        }
    }


    /**
     * Lee un XLSX y retorna:
     * [
     *     'headers' => [...],
     *     'rows' => [...]
     * ]
     */
    public function parseXlsx(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception('Archivo XLSX no legible o inexistente.');
        }

        if (!$xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
            throw new \Exception(\Shuchkin\SimpleXLSX::parseError());
        }

        $sheet = $xlsx->rows();

        if (empty($sheet)) {
            throw new \Exception('La hoja de cálculo está vacía.');
        }

        // Primera fila = cabeceras
        $headers = array_map(
            fn($header) => trim((string) $header),
            array_shift($sheet)
        );

        $rows = [];

        foreach ($sheet as $rowData) {
            $row = [];

            foreach ($headers as $index => $header) {
                $row[$header] = isset($rowData[$index])
                    ? trim((string) $rowData[$index])
                    : null;
            }

            // Ignorar filas completamente vacías
            if (!array_filter($row, fn($value) => $value !== null && $value !== '')) {
                continue;
            }

            $rows[] = $row;
        }

        return [
            'headers' => $headers,
            'rows' => $rows,
        ];
    }

    public function importActividades(string $filePath): array
    {
        $data = $this->parseXlsx($filePath);


        $this->excelService->validateActividad(
            $data
        );

        $result = $this->excelService->parseActividad($data);

        return $result;
    }
}
