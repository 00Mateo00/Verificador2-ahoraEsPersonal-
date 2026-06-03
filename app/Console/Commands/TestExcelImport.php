<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ExcelImporterService;

class TestExcelImport extends Command
{
    /**
     * El comando de consola para probar la importación.
     */
    protected $signature = 'import:test {file}';

    /**
     * Descripción del comando.
     */
    protected $description = 'Prueba la lectura y asignación automática de un archivo XLSX';

    /**
     * Ejecuta el comando.
     */
    public function handle(ExcelImporterService $service): int
    {
        $file = $this->argument('file');

        if (!file_exists($file)) {
            $this->error("El archivo no existe en la ruta especificada: {$file}");
            return 1;
        }

        $this->info("Iniciando lectura de: {$file}...");

        try {
            $rows = $service->parseXlsx($file);
            $total = count($rows);

            $this->info("¡Éxito! Total de registros encontrados: {$total}");

            // Tomar una muestra de las primeras 5 filas para renderizar una tabla de control
            $sample = [];
            foreach (array_slice($rows, 0, 5) as $row) {
                $sample[] = [
                    'COD' => $row['COD'] ?? 'N/A',
                    'UNIDAD_EXCEL' => $row['UNIDAD'] ?? 'N/A',
                    'ASIGNADO_ID' => $row['unidad_id_asignada'] ?? 'SIN COINCIDENCIA',
                    'FUNCIONARIO' => $row['FUNCIONARIO'] ?? 'N/A',
                    'TIPO_ACT' => $row['TIPO_ACTIVIDAD'] ?? 'N/A',
                ];
            }

            $this->table(['COD', 'UNIDAD_EXCEL', 'ASIGNADO_ID', 'FUNCIONARIO', 'TIPO_ACT'], $sample);

            $noMatchCount = collect($rows)->whereNull('unidad_id_asignada')->count();
            if ($noMatchCount > 0) {
                $this->warn("Advertencia: {$noMatchCount} filas no pudieron emparejarse con ninguna unidad de la base de datos.");
            } else {
                $this->info("¡Perfecto! Todas las filas se emparejaron con una unidad registrada.");
            }

        } catch (\Exception $e) {
            $this->error("Fallo al procesar: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}