<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\ExcelService;


class Actividad extends Model
{

    protected $table = 'actividad';
    protected $primaryKey = 'actividad_id';
    protected $fillable = [];

    protected $casts = [
        // TO-DO dejar como opcionales, ya que no siempre vienen en el excel
        'PARTICIPANTES' => 'integer',
        'TOTAL_HOMBRES' => 'integer',
        'TOTAL_MUJERES' => 'integer',
        'TOTAL_NOBINARIO' => 'integer',
        'MES' => 'integer',
        'AÑO' => 'integer',

        'activo' => 'boolean',
    ];

    /**
     * Mapeo de columnas del Excel hacia columnas persistidas.
     *
     * Cuando exista una columna *_MODIFICADO, su valor tendrá prioridad
     * sobre la columna original.
     */
    private const EXCEL_COLUMN_MAPPING = [
        'MODALIDAD_MODIFICADO' => 'MODALIDAD',
        'TIPO_MODIFICADO' => 'TIPO_ACTIVIDAD',
        'SUB_TIPO_MODIFICADO' => 'SUB_TIPO_ACTIVIDAD',
    ];

    // Cabeceras requeridas minimas


    /* TO-DO : preguntar a Felipe si con "opcional" se refiera a:
     * 1.- No es requerido en la carga?
     * 2.- Puede tener un valor nulo?
    
    */

    // tipo_unidad hay que quitarlo ??
    // detActividad podria no venir ??
    // añadir filtro por mes operativo
    //To-do: si estamos en enero 
    // ya existen archivos de enero 2026 (A.E anterior) estas seguro que quieres subirlo? (si sube algo de enero 2026 y estamos realmente a 2027, pero solo en enero )
    // si estamos en enero 2027 y en el excel aparece un M.E de enero 2026, estas seguro que quieres subirlo? (si sube algo de enero 2026 y estamos realmente a 2027, pero solo en enero )

    public static function excelColumnsToPersist(): array
    {
        $columns = [];

        foreach (ExcelService::REQUIRED_EXCEL_HEADERS as $header) {

            $columns[] =
                self::EXCEL_COLUMN_MAPPING[$header]
                ?? $header;
        }

        return array_values(array_unique($columns));
    }

    public static function fromExcelRow(
        array $row,
        int $cargaId,
        ?int $unidadIdAsignada
    ): array {

        $data = [];

        foreach (ExcelService::REQUIRED_EXCEL_HEADERS as $header) {
            if (isset(self::EXCEL_COLUMN_MAPPING[$header])) {
                $data[self::EXCEL_COLUMN_MAPPING[$header]] = $row[$header];
            } else {

                $data[$header] = $row[$header];
            }
        }
        // control interno 
        $data['estado'] = 'CARGADA';
        $data['carga_id'] = $cargaId;
        $data['unidad_id_asignada'] = $unidadIdAsignada;
        $data['activo'] = true;




        return $data;
    }

    public static function createFromExcelRow(
        array $row,
        int $cargaId,
        ?int $unidadIdAsignada
    ): self {
        /*    esto lo uso como test */
        /* return self::fromExcelRow($row, $cargaId, $unidadIdAsignada); */

        return self::create(
            self::fromExcelRow(
                $row,
                $cargaId,
                $unidadIdAsignada
            )
        );
    }

    public function __construct(array $attributes = [])
    {
        parent::__construct($attributes);

        $this->fillable = [
            ...self::excelColumnsToPersist(),
            'estado',
            'carga_id',
            'unidad_id_asignada',
            'activo',
        ];
    }



    /**
     * Relación con la unidad del sistema que debe gestionar esta actividad.
     */
    public function unidadAsignada(): BelongsTo
    {
        return $this->belongsTo(Unidad::class, 'unidad_id_asignada', 'unidad_id');
    }

    /**
     * Relación con los archivos de respaldo o verificadores adjuntos.
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'actividad_id', 'actividad_id');
    }
}
