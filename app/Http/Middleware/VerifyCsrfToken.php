<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array
     */
    protected $except = [
        'callback',
        'pieraksts/*',
        'ziemas-riepas/*',
        'vasaras-riepas/*',
        'kvadraciklu-riepas/*',
        'motociklu-riepas/*',
        'lielas-riepas/*',
        'grozs',
        'pasutijums',
    ];
}
