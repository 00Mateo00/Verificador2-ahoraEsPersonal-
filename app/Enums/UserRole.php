<?php

namespace App\Enums;

enum UserRole: string
{
    case Admin = 'admin';
    case Auditor = 'auditor';
    case Cargador = 'cargador';
    case Unidad = 'unidad';
    case Director = 'director';
}