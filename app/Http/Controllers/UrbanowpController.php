<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UrbanowpController extends Controller
{
    public function mensajesurbano ()
    {
        return view('mensaje.urbanowp');
    }
}
