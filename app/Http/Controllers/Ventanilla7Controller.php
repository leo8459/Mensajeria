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
    
    public function mensajebeni ()
    {
        return view('mensaje.smsbeni');
    }
     public function mensajeoruro ()
    {
        return view('mensaje.smsoruro');
    }
}
