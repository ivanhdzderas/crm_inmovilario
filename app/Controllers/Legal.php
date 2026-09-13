<?php

namespace App\Controllers;

use App\Controllers\BaseController;

class Legal extends BaseController
{
    /**
     * Mostrar las políticas de privacidad
     */
    public function privacyPolicy()
    {
        $data = [
            'title' => 'Políticas de Privacidad - Pacific Inmobiliaria'
        ];
        
        return view('legal/privacy_policy', $data);
    }
    
    /**
     * Mostrar los términos y condiciones
     */
    public function termsOfService()
    {
        $data = [
            'title' => 'Términos y Condiciones para Clientes - Pacific Inmobiliaria'
        ];
        
        return view('legal/terms_client', $data);
    }

    /**
     * Mostrar los términos y condiciones específicos para usuarios del CRM
     */
    public function crmTermsOfService()
    {
        $data = [
            'title' => 'Términos y Condiciones del CRM - Pacific Inmobiliaria'
        ];
        
        return view('legal/terms_crm', $data);
    }
    
    /**
     * Mostrar el aviso de cookies
     */
    public function cookiePolicy()
    {
        $data = [
            'title' => 'Política de Cookies - Pacific Inmobiliaria'
        ];
        
        return view('legal/cookie_policy', $data);
    }
    
    public function conditionsOfUse()
    {
        $data = [
            'title' => 'Condiciones de Uso - Pacific Inmobiliaria'
        ];
        
        return view('legal/conditions_of_use', $data);
    }
}