<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class Ventanilla7Controller extends Controller
{
      public function generarventanilla7 ()
    {
        return view('mensaje.ventanilla7');
    }
    public function mensajesc1 ()
    {
        return view('mensaje.smssc1');
    }
    public function mensajecb1 ()
    {
        return view('mensaje.smscb1');
    }
     public function mensajesc2 ()
    {
        return view('mensaje.smssc2');
    }
    public function mensajecb2 ()
    {
        return view('mensaje.smscb2');
    }
    
    public function mensajebeni ()
    {
        return view('mensaje.smsbeni');
    }
     public function mensajeoruro ()
    {
        return view('mensaje.smsoruro');
    }
    public function mensajetarija ()
    {
        return view('mensaje.smstarija');
    }
    public function mensajechuquisaca ()
    {
        return view('mensaje.smschuquisaca');
    }
    public function mensajepando ()
    {
        return view('mensaje.smspando');
    }
    public function mensajepotosi ()
    {
        return view('mensaje.smspotosi');
    }
}
