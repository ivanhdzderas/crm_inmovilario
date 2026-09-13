<?php

namespace Tests\Unit;

use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CatalogoRoutesTest extends CIUnitTestCase
{
    public function testMetodosHomeParaCatalogoExisten(): void
    {
        $this->assertTrue(method_exists(\App\Controllers\Home::class, 'catalogo'));
        $this->assertTrue(method_exists(\App\Controllers\Home::class, 'catalogoInmueble'));
        $this->assertTrue(method_exists(\App\Controllers\Home::class, 'fichaDesarrollo'));
        $this->assertTrue(method_exists(\App\Controllers\Home::class, 'desarrollo'));
        $this->assertTrue(method_exists(\App\Controllers\Home::class, 'publicTechnicalSheet'));
    }

    public function testArchivoRoutesDefineUrlsPublicas(): void
    {
        $contenido = file_get_contents(APPPATH . 'Config/Routes.php');

        $this->assertIsString($contenido);
        $this->assertStringContainsString("get('catalogo', 'Home::catalogo')", $contenido);
        $this->assertStringContainsString("get('catalogo/(:segment)/(:segment)', 'Home::catalogoInmueble/$1/$2')", $contenido);
        $this->assertStringContainsString("get('desarrollo/(:segment)/(:segment)', 'Home::fichaDesarrollo/$1/$2')", $contenido);
        $this->assertStringContainsString("get('desarrollo/(:segment)', 'Home::desarrollo/$1')", $contenido);
        $this->assertStringContainsString("get('ficha-tecnica/(:num)', 'Home::publicTechnicalSheet/$1')", $contenido);
    }

    public function testSparkRoutesIncluyeCatalogo(): void
    {
        $output = shell_exec('php ' . escapeshellarg(ROOTPATH . 'spark') . ' routes 2>&1');

        $this->assertIsString($output);
        $this->assertStringContainsString('Home::catalogo', $output);
        $this->assertStringContainsString('Home::fichaDesarrollo', $output);
        $this->assertStringContainsString('Home::catalogoInmueble', $output);
    }
}
