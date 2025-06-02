<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class MensajeController extends Controller
{
     public function generarmensajes ()
    {
        return view('mensaje.mensaje');
    }
    public function generarmensajeswpp ()
    {
        return view('mensaje.mensajes-node');
    }
    public function generarmensajeswppbot ()
    {
        return view('mensaje.whatsapp-bot');
    }
}
