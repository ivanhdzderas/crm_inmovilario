<?php

use App\Helpers\CatalogoFunerarioHelper;

if (! function_exists('funerario_slug')) {
    function funerario_slug(string $text): string
    {
        return CatalogoFunerarioHelper::slug($text);
    }
}

if (! function_exists('url_funerario_producto')) {
    function url_funerario_producto(array $producto): string
    {
        return CatalogoFunerarioHelper::urlProducto($producto);
    }
}

if (! function_exists('formato_precio_funerario')) {
    function formato_precio_funerario($precio): string
    {
        return CatalogoFunerarioHelper::formatoPrecio($precio);
    }
}
