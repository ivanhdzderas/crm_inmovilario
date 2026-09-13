<?php

namespace App\Controllers;

/**
 * @deprecated Usar Home::catalogo* — se mantiene por compatibilidad.
 */
class Catalogo extends Home
{
    public function index(): string
    {
        return $this->catalogo();
    }

    public function inmueble(string $slugProyecto, string $slugInmueble): string
    {
        return $this->catalogoInmueble($slugProyecto, $slugInmueble);
    }

    public function desarrollo(string $slugProyecto): string
    {
        return parent::desarrollo($slugProyecto);
    }

    public function ficha(string $slugProyecto, string $slugInmueble): string
    {
        return $this->fichaDesarrollo($slugProyecto, $slugInmueble);
    }
}
