<?php

namespace App\Services;

class ExcelImporterService
{

    public function __construct(
        private ExcelService $excelService
    ) {}


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
