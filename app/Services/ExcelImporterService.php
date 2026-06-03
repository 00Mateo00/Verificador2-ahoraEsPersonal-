<?php

namespace App\Services;

use App\Models\Unidad;
use Illuminate\Support\Facades\DB;

class ExcelImporterService
{
    /**
     * Parsea un archivo XLSX nativo de Microsoft Excel y devuelve filas mapeadas por cabecera.
     */
    public function parseXlsx(string $filePath): array
    {
        if (!file_exists($filePath) || !is_readable($filePath)) {
            throw new \Exception("Archivo XLSX no legible o inexistente.");
        }

        // SimpleXLSX es ultra liviano y rápido
        if (!$xlsx = \Shuchkin\SimpleXLSX::parse($filePath)) {
            throw new \Exception(\Shuchkin\SimpleXLSX::parseError());
        }

        $rows = [];
        $sheet = $xlsx->rows();

        if (empty($sheet)) {
            throw new \Exception("La hoja de cálculo está vacía.");
        }

        // Obtener cabeceras y limpiar espacios
        $headers = array_map('trim', array_shift($sheet));
        
        // Cargar caché de unidades en DB para optimizar velocidad de asignación
        $unidadesCache = Unidad::pluck('unidad_id', 'unidad_nombre')->toArray();

        // Convertir nombres a mayúsculas para comparación directa insensible
        $unidadesCacheUpper = [];
        foreach ($unidadesCache as $nombre => $id) {
            $unidadesCacheUpper[strtoupper(trim($nombre))] = $id;
        }

        foreach ($sheet as $rowData) {
            // Unir cabecera con datos de la fila actual
            $row = [];
            foreach ($headers as $index => $header) {
                $row[$header] = isset($rowData[$index]) ? trim($rowData[$index]) : null;
            }

            // Ignorar filas completamente vacías
            if (!array_filter($row)) {
                continue;
            }

            // Resolver asignación de Unidad automática
            $unidadNombre = $row['UNIDAD'] ?? null;
            $unidadId = null;

            if ($unidadNombre) {
                $upperNombre = strtoupper(trim($unidadNombre));
                if (isset($unidadesCacheUpper[$upperNombre])) {
                    $unidadId = $unidadesCacheUpper[$upperNombre];
                } else {
                    $match = DB::table('unidad')
                        ->whereRaw('LOWER(unidad_nombre) = ?', [strtolower(trim($unidadNombre))])
                        ->first();
                    if ($match) {
                        $unidadId = $match->unidad_id;
                    }
                }
            }

            $row['unidad_id_asignada'] = $unidadId;
            $rows[] = $row;
        }

        return $rows;
    }
}