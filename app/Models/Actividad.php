<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use App\Services\ExcelService;


class Actividad extends Model
{

    /** 
     *Columnas obligatorias que debe contener el excel para poder luego crear una actividad
     */
    public const MANDATORY_FIELDS_TO_CREATE_ACTIVIDAD = [
        'COD',
        'UNIDAD',
        'REGION',
        'MES',
        'AÑO',
        'FECHA_SAJ',
        'MODALIDAD_MODIFICADO',
        'TIPO_MODIFICADO',
        'SUB_TIPO_MODIFICADO',
    ];
    /**
     * Columnas que si no vienen en el excel se guardan con un valor por defecto o null
     */
    public const OPTIONAL_ACTIVIDAD_FIELDS = [
        'FECHA',
        'PARTICIPANTES',
        'TOTAL_HOMBRES',
        'TOTAL_MUJERES',
        'TOTAL_NOBINARIO',
        'DET_ACTIVIDAD',
        'FUNCIONARIO',
    ];


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
        'FECHA' => 'date',
        'FECHA_SAJ' => 'date',

        'activo' => 'boolean',
    ];


    // Cabeceras requeridas minimas


    /* TO-DO : preguntar a Felipe si con "opcional" se refiera a:
     * 1.- No es requerido en la carga?
     * 2.- Puede tener un valor nulo?
    
    */
    //To-do: si estamos en enero 
    // ya existen archivos de enero 2026 (A.E anterior) estas seguro que quieres subirlo? (si sube algo de enero 2026 y estamos realmente a 2027, pero solo en enero )
    // si estamos en enero 2027 y en el excel aparece un M.E de enero 2026, estas seguro que quieres subirlo? (si sube algo de enero 2026 y estamos realmente a 2027, pero solo en enero )
    private const EXCEL_COLUMN_MAPPING = [
        'MODALIDAD_MODIFICADO' => 'MODALIDAD',
        'TIPO_MODIFICADO' => 'TIPO_ACTIVIDAD',
        'SUB_TIPO_MODIFICADO' => 'SUB_TIPO_ACTIVIDAD',
    ];

    private static function excelColumnsToPersist(): array
    {
        return [
            ...array_map(
                fn(string $header) => self::EXCEL_COLUMN_MAPPING[$header] ?? $header,
                self::MANDATORY_FIELDS_TO_CREATE_ACTIVIDAD
            ),
            ...self::OPTIONAL_ACTIVIDAD_FIELDS,
        ];
    }


    /**
     * Acepta una fila del excel y lo remapea. Tambien filtra lo que no exista en excelColumnsToPersist
     * 
     */
    private static function mapRowToPersistableData(array $row): array
    {
        // esto debe tener las topersist lists
        $allowedColumns = array_flip(self::excelColumnsToPersist());

        $data = [];

        foreach (ExcelService::REQUIRED_EXCEL_HEADERS as $header) {
            $column = self::EXCEL_COLUMN_MAPPING[$header] ?? $header;

            if (!isset($allowedColumns[$column])) {
                continue;
            }
            $data[$column] = $row[$header];
        }
        return $data;
    }


    public static function fromExcelRow(
        array $row,
        int $cargaId,
        ?int $unidadIdAsignada
    ): array {
        // mapeo y filtro
        $data = [...self::mapRowToPersistableData($row)];
        // control interno 
        $data['estado'] = 'CARGADA';
        $data['carga_id'] = $cargaId;
        $data['unidad_id_asignada'] = $unidadIdAsignada;
        $data['activo'] = true;

        return $data;
    }

    /*     public static function createFromExcelRow(
        array $row,
        int $cargaId,
        ?int $unidadIdAsignada
    ): array {


        return self::create(
            self::fromExcelRow(
                $row,
                $cargaId,
                $unidadIdAsignada
            )
        );
    } */

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
        return $this->belongsTo(Unidad::class, 'unidad_id_asignada', 'id');
    }

    /**
     * Relación con los archivos de respaldo o verificadores adjuntos.
     */
    public function archivos(): HasMany
    {
        return $this->hasMany(Archivo::class, 'actividad_id', 'actividad_id');
    }
}
