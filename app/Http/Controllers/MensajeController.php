<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MensajeController extends Controller
{
     public function generarmensajes ()
    {
        return view('mensaje.mensaje');
    }
    public function generarmensajeswppbot ()
    {
        return view('mensaje.whatsapp-bot');
    }
    public function mensajessc1 ()
    {
        return view('mensaje.sc1');
    }
}
