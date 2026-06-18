<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Centralized Password Complexity Policy
    |--------------------------------------------------------------------------
    |
    | Estas opciones controlan los requisitos mínimos de complejidad para
    | la creación y actualización de contraseñas en todo el sistema.
    |
    */

    'min_length' => (int) env('PASSWORD_POLICY_MIN_LENGTH', 8),

    'require_mixed_case' => (bool) env('PASSWORD_POLICY_REQUIRE_MIXED_CASE', true),

    'require_letters' => (bool) env('PASSWORD_POLICY_REQUIRE_LETTERS', true),

    'require_numbers' => (bool) env('PASSWORD_POLICY_REQUIRE_NUMBERS', true),

    'require_symbols' => (bool) env('PASSWORD_POLICY_REQUIRE_SYMBOLS', true),

    'check_uncompromised' => (bool) env('PASSWORD_POLICY_CHECK_UNCOMPROMISED', true),
];
