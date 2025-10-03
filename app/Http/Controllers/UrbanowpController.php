<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UrbanowpController extends Controller
{
    public function mensajesurbano ()
    {
        return view('mensaje.urbanowp');
    }
    public function mensajesurbano2 ()
    {
        return view('mensaje.urbano2');
    }
    public function mensajesurbano3 ()
    {
        return view('mensaje.urbano3');
    }
    public function mensajesurbano4 ()
    {
        return view('mensaje.urbano4');
    }
    public function mensajesurbano5 ()
    {
        return view('mensaje.urbano5');
    }
}
