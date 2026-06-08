<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class ExportUnidadTest extends Command
{
  /**
   * php artisan unidad:export
   */
  protected $signature = 'unidad:export';

  protected $description = 'Exporta la tabla unidad_test a un archivo array.txt';

  public function handle(): int
  {
    $this->info('Leyendo tabla unidad_test...');

    try {
      $rows = DB::table('unidad_test')->get();

      $data = $rows
        ->map(fn($row) => array_values((array) $row))
        ->toArray();

      $path = storage_path('array.txt');

      File::put(
        $path,
        json_encode(
          $data,
          JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        )
      );

      $this->info('Exportación completada.');
      $this->line("Filas exportadas: " . count($data));
      $this->line("Archivo generado: {$path}");

      // Mostrar muestra de control
      $sample = array_slice($data, 0, 5);

      if (!empty($sample)) {
        $this->newLine();
        $this->info('Primeras 5 filas:');

        foreach ($sample as $index => $row) {
          $this->line("#" . ($index + 1) . " => " . json_encode($row, JSON_UNESCAPED_UNICODE));
        }
      }

      return self::SUCCESS;
    } catch (\Throwable $e) {

      $this->error(
        'Error al exportar: ' . $e->getMessage()
      );

      return self::FAILURE;
    }
  }
}
