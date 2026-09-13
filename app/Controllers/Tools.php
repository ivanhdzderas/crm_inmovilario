<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Tools extends BaseController
{
    public function isrCalculator()
    {
        $data['title'] = 'Calculadora de ISR (Estimación)';
        return view('prospects/isr_calculator', $data);
    }
}