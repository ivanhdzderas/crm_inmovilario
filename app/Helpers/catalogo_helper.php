<?php

use App\Helpers\CatalogoHelper;

if (! function_exists('catalogo_slug')) {
    function catalogo_slug(string $text): string
    {
        return CatalogoHelper::slug($text);
    }
}

if (! function_exists('parse_payment_methods')) {
    function parse_payment_methods(mixed $raw): array
    {
        return CatalogoHelper::parsePaymentMethods($raw);
    }
}

if (! function_exists('url_desarrollo')) {
    function url_desarrollo(array $proyecto): string
    {
        return CatalogoHelper::urlDesarrollo($proyecto);
    }
}

if (! function_exists('url_inmueble')) {
    function url_inmueble(array $propiedad): string
    {
        return CatalogoHelper::urlInmueble($propiedad);
    }
}

if (! function_exists('url_catalogo_inmueble')) {
    function url_catalogo_inmueble(array $propiedad): string
    {
        return CatalogoHelper::urlCatalogoInmueble($propiedad);
    }
}

if (! function_exists('formato_precio_mxn')) {
    function formato_precio_mxn($precio): string
    {
        return CatalogoHelper::formatoPrecioMxn($precio);
    }
}
