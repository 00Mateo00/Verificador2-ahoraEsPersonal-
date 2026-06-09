<?php

namespace App\Services;


class ExcelService
{
  public const REQUIRED_EXCEL_HEADERS = [
    // obligatorias
    'COD',
    'UNIDAD', // TO-DO : dejar como obligatorio
    'REGION', // TO-DO : dejar como obligatorio
    'MES', // TO-DO : dejar como obligatorio
    'AÑO', // TO-DO : dejar como obligatorio
    'FECHA_SAJ', // TO-DO : dejar como obligatorio

    // se remapean a su version no modificada (tambien son obligatorios)
    'MODALIDAD_MODIFICADO',
    'TIPO_MODIFICADO',
    'SUB_TIPO_MODIFICADO',

    // opcionales
    'FECHA',
    'PARTICIPANTES',
    'TOTAL_HOMBRES',
    'TOTAL_MUJERES',
    'TOTAL_NOBINARIO',
    'DET_ACTIVIDAD',
    'FUNCIONARIO',

  ];

  public function validateActividad(array $data): void
  {
    // validar rows no vacias
    if ($data['rows'] === []) {
      throw new \Exception('No hay filas de datos para validar.');
    }

    $missing = array_diff(
      self::REQUIRED_EXCEL_HEADERS,
      $data['headers']
    );

    if (!empty($missing)) {
      throw new \Exception(
        'Faltan columnas requeridas: '
          . implode(', ', $missing)
      );
    }
  }

  public function parseActividad(array $data): array
  {
    $data['headers'] = array_values(
      array_intersect($data['headers'], self::REQUIRED_EXCEL_HEADERS)
    );

    $data['rows'] = array_map(
      fn($row) => array_intersect_key(
        $row,
        array_flip(self::REQUIRED_EXCEL_HEADERS)
      ),
      $data['rows']
    );



    return $data;
  }
}
