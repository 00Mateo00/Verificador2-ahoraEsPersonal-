<?php

namespace App\Services;

use App\Models\Actividad;
use Carbon\Carbon;

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
        $buscar = ['á', 'é', 'í', 'ó', 'ú', 'Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ', 'ü', 'Ü'];
        $reemplazar = ['a', 'e', 'i', 'o', 'u', 'A', 'E', 'I', 'O', 'U', 'n', 'N', 'u', 'U'];
        $texto = str_replace($buscar, $reemplazar, $texto);
        $texto = preg_replace('/[^a-zA-Z0-9]+/', ' ', $texto);

        return strtoupper(trim($texto));
    }

    /**
     * Normaliza formatos de fecha habituales en Excel para SQL (YYYY-MM-DD).
     */
    public static function normalizarFecha(?string $fecha): ?string
    {
        if (empty($fecha)) {
            return null;
        }

        $fecha = trim($fecha);
        $fecha = str_replace('\\', '', $fecha);

        $fecha = preg_replace('/,\d+$/', '', $fecha);

        if (preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            return $fecha;
        }

        // Formatos habituales encontrados en Excel/CSV
        $formatos = [
            'd-m-Y',
            'd/m/Y',
            'Y-m-d',
            'Y/m/d',
            'j-n-Y',
            'j/n/Y',

            // Fechas con hora
            'd/m/y H:i:s',
            'd/m/Y H:i:s',
            'd-m-y H:i:s',
            'd-m-Y H:i:s',
        ];

        foreach ($formatos as $formato) {
            try {
                return Carbon::createFromFormat($formato, $fecha)
                    ->format('Y-m-d');
            } catch (\Throwable $e) {
                continue;
            }
        }

        // Fallback para otros formatos válidos que Carbon pueda interpretar
        try {
            return Carbon::parse($fecha)->format('Y-m-d');
        } catch (\Throwable $e) {
            return null;
        }
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

        if (! empty($missing)) {
            throw new \Exception(
                'Faltan columnas requeridas: '
                  .implode(', ', $missing)
            );
        }
    }

    public function parseActividad(array $data): array
    {
        $data['headers'] = array_values(
            array_intersect($data['headers'], self::REQUIRED_EXCEL_HEADERS)
        );

        $filteredRows = [];

        $exclusiones = config('excel_ingestion.exclusiones_tipo_unidad', []);
        $permitidos = config('excel_ingestion.codigos_actividad_permitidos', []);

        foreach ($data['rows'] as $row) {
            // 1. Excluir filas donde TIPO_UNIDAD contenga alguno de los criterios parametrizados
            $tipoUnidad = strtoupper($row['TIPO_UNIDAD'] ?? '');

            $excluir = collect($exclusiones)
                ->map(fn ($e) => strtoupper($e))
                ->contains(fn ($exclusion) => str_contains($tipoUnidad, $exclusion));

            if ($excluir) {
                continue;
            }

            // 2. Preservar únicamente filas donde TIPO_ACT_COD sea permitido
            $tipoActCod = (int) ($row['TIPO_ACT_COD'] ?? 0);
            if (! in_array($tipoActCod, $permitidos, true)) {
                continue;
            }

            // 3. Normalizar campos de fecha a formato SQL YYYY-MM-DD
            if (isset($row['FECHA_SAJ'])) {
                $row['FECHA_SAJ'] = self::normalizarFecha($row['FECHA_SAJ']);
            }
            if (isset($row['FECHA'])) {
                $row['FECHA'] = self::normalizarFecha($row['FECHA']);
            }

            // 4. Retener solo las cabeceras registradas
            $filteredRows[] = array_intersect_key($row, array_flip(self::REQUIRED_EXCEL_HEADERS));
        }

        $data['rows'] = $filteredRows;

        return $data;
    }
}
