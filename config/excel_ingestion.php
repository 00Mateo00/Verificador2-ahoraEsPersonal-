<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Redirecciones Territoriales de Unidades
    |--------------------------------------------------------------------------
    |
    | Permite redirigir unidades identificadas en el Excel de origen hacia
    | las unidades operativas equivalentes registradas en la base de datos.
    |
    */
    'redirecciones' => [
        'PMA LOS ANGELES' => 'PMA CONCEPCIÓN',
    ],

    /*
    |--------------------------------------------------------------------------
    | Filtros de Exclusión por Tipo de Unidad
    |--------------------------------------------------------------------------
    |
    | Excluye de la ingesta aquellas filas donde la columna 'TIPO_UNIDAD'
    | contenga alguno de los siguientes strings (sin distinción de mayúsculas).
    |
    */
    'exclusiones_tipo_unidad' => [
        'NAD',
        'SENADIS',
    ],

    /*
    |--------------------------------------------------------------------------
    | Códigos de Actividad Permitidos
    |--------------------------------------------------------------------------
    |
    | Únicos códigos numéricos en la columna 'TIPO_ACT_COD' admitidos
    | para ser guardados e indexados en el sistema.
    |
    */
    'codigos_actividad_permitidos' => [
        1,
        2,
    ],
];