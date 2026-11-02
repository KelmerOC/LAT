<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

/**
 * ============================================================================
 * CONTROLADOR: PORTAL PÚBLICO INSTITUCIONAL
 * ============================================================================
 * Presenta la vitrina comunitaria, propuesta de valor real y selector trilingüe.
 */
class HomeController extends Controller
{
    public function index()
    {
        return view('inicio');
    }
}
