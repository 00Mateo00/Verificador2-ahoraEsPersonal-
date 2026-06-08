<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Actividad extends Model
{
    protected $table = 'actividad';
    protected $primaryKey = 'actividad_id';
    protected $fillable = [];

    protected $casts = [
        'PARTICIPANTES' => 'integer',
        'TOTAL_HOMBRES' => 'integer',
        'TOTAL_MUJERES' => 'integer',
        'TOTAL_NOBINARIO' => 'integer',
        'MES' => 'integer',
        'AÑO' => 'integer',

        'activo' => 'boolean',
    ];

    // TO-DO : tal vez debería moverlo a su propio archivo exclusivo de "Excel"
    // Cabeceras requeridas para la validación de la planilla Excel (vienen del archivo excel original)
    public const REQUIRED_EXCEL_HEADERS = [
        'MODALIDAD_MODIFICADO',
        'TIPO_MODIFICADO',
        'SUB_TIPO_MODIFICADO',
        'COD',
        'FECHA',
        'MODALIDAD',
        'PARTICIPANTES',
        'TOTAL_HOMBRES',
        'TOTAL_MUJERES',
        'TOTAL_NOBINARIO',
        'FUNCIONARIO',
        'UNIDAD',
        'REGION',
        'MES',
        'AÑO',
        'DET_ACTIVIDAD',
    ];

    public static function getPersistedExcelColumns(): array
    {
        return array_values(
            array_diff(
                self::REQUIRED_EXCEL_HEADERS,
                [
                    'MODALIDAD_MODIFICADO',
                    'TIPO_MODIFICADO',
                    'SUB_TIPO_MODIFICADO',
                ]
            )
        );
    }

    public static function fromExcelRow(
        array $row,
        int $cargaId,
        ?int $unidadIdAsignada
    ): array {

        $data = [];

        foreach (self::getPersistedExcelColumns() as $column) {
            $data[$column] = $row[$column] ?? null;
        }

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
            ...self::getPersistedExcelColumns(),

            'estado',
            'carga_id',
            'usuario_id_asignado',
            'unidad_id_asignada',
            'ubicacion',
            'observacion',
            'activo',
        ];
    }


    /**
     * Relación con el funcionario interno asignado para adjuntar el verificador.
     */
    public function usuarioAsignado(): BelongsTo
    {
        return $this->belongsTo(User::class, 'usuario_id_asignado', 'id');
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
