<?php

namespace App\Services;

use App\Models\Actividad;

class ExcelService
{

  public const REQUIRED_EXCEL_HEADERS = [
    ...Actividad::MANDATORY_FIELDS_TO_CREATE_ACTIVIDAD,
    ...Actividad::OPTIONAL_ACTIVIDAD_FIELDS,
    'TIPO_UNIDAD',
    'TIPO_ACT_COD',
  ];


  /**
   * Preserva los límites entre palabras durante la normalización.
   */
  public static function normalizarTexto(string $texto): string
  {
    $texto = trim($texto);
    $buscar     = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'];
    $reemplazar = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U'];
    $texto = str_replace($buscar, $reemplazar, $texto);
    $texto = preg_replace('/[^a-zA-Z0-9]+/', ' ', $texto);

    return strtoupper(trim($texto));
  }

  public function validateActividad(array $data): void
  {
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

    $filteredRows = [];

    foreach ($data['rows'] as $row) {
      // 1. Excluir filas donde TIPO_UNIDAD contenga "NAD" o "SENADIS"
      $tipoUnidad = strtoupper($row['TIPO_UNIDAD'] ?? '');
      if (str_contains($tipoUnidad, 'NAD') || str_contains($tipoUnidad, 'SENADIS')) {
        continue;
      }

      // 2. Preservar únicamente filas donde TIPO_ACT_COD sea 1 o 2
      $tipoActCod = (int) ($row['TIPO_ACT_COD'] ?? 0);
      if ($tipoActCod !== 1 && $tipoActCod !== 2) {
        continue;
      }

      // 3. Retener solo las cabeceras registradas
      $filteredRows[] = array_intersect_key($row, array_flip(self::REQUIRED_EXCEL_HEADERS));
    }

    $data['rows'] = $filteredRows;

    return $data;
  }
}
