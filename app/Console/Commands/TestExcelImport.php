<?php

namespace App\Console\Commands;

use App\Models\Actividad;
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
            $data = $service->importActividades($file);

            $rows = $data['rows'];

            $NumberOfRows = 5;
            $sample = [];
            $contador = 1;
            foreach (array_slice($rows, 0, $NumberOfRows) as $row) {
                $sample[] = Actividad::createFromExcelRow(
                    $row,
                    1,
                    2
                );
                $contador += 1;
            }

            dump($sample);
        } catch (\Exception $e) {
            $this->error("Fallo al procesar: " . $e->getMessage());
            return 1;
        }

        return 0;
    }
}
