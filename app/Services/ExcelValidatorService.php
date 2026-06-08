<?php

namespace App\Services;

use App\Models\Actividad;

class ExcelValidatorService
{
  public function validateActividad(array $data): void
  {

    // TO-DO cambiar la validacion para que puedan existir columans extras a las estipuladas pero no pueden faltar las requeridas.
    $missing = array_diff(
      Actividad::REQUIRED_EXCEL_HEADERS,
      $data['headers']
    );

    if (!empty($missing)) {
      throw new \Exception(
        'Faltan columnas requeridas: '
          . implode(', ', $missing)
      );
    }

    // validar rows no vacias
    if ($data['rows'] === []) {
      throw new \Exception('No hay filas de datos para validar.');
    }

    //TO-DO validar que no hayan celdas vacías en columnas críticas como UNIDAD, TIPO_ACTIVIDAD, etc.
  }
}
