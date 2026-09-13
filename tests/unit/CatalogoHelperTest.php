<?php

namespace Tests\Unit;

use App\Helpers\CatalogoHelper;
use CodeIgniter\Test\CIUnitTestCase;

/**
 * @internal
 */
final class CatalogoHelperTest extends CIUnitTestCase
{
    public function testSlugEliminaAcentos(): void
    {
        $this->assertSame(
            'lafayette-juan-ruiz-de-alarcon',
            CatalogoHelper::slug('Lafayette Juan Ruiz de Alarcón')
        );
        $this->assertSame('comonfort', CatalogoHelper::slug('Comonfort'));
        $this->assertSame('casa-veris', CatalogoHelper::slug('Casa Veris'));
        $this->assertSame('desarrollo-comonfort', CatalogoHelper::slug('Desarrollo Comonfort'));
    }

    public function testSlugNoContieneCaracteresUnicodeEnUrl(): void
    {
        $slug = CatalogoHelper::slug('Niño José María');
        $this->assertSame('nino-jose-maria', $slug);
        $this->assertSame($slug, mb_strtolower($slug));
        $this->assertDoesNotMatchRegularExpression('/[^\x20-\x7E]/', $slug);
    }

    public function testNormalizeSegmentDecodificaPorcentajeYQuitaAcentos(): void
    {
        $this->assertSame(
            'lafayette-juan-ruiz-de-alarcon',
            CatalogoHelper::normalizeSegment('lafayette-juan-ruiz-de-alarc%C3%B3n')
        );
        $this->assertSame(
            'lafayette-juan-ruiz-de-alarcon',
            CatalogoHelper::normalizeSegment('lafayette-juan-ruiz-de-alarcón')
        );
    }

    public function testCoincideSlugConVariantesDeAcentos(): void
    {
        $nombre = 'Lafayette Juan Ruiz de Alarcón';

        $this->assertTrue(CatalogoHelper::coincideSlug($nombre, 'lafayette-juan-ruiz-de-alarcon'));
        $this->assertTrue(CatalogoHelper::coincideSlug($nombre, 'lafayette-juan-ruiz-de-alarcón'));
        $this->assertTrue(CatalogoHelper::coincideSlug($nombre, 'lafayette-juan-ruiz-de-alarc%C3%B3n'));
        $this->assertFalse(CatalogoHelper::coincideSlug($nombre, 'otra-propiedad'));
    }

    public function testCoincideSlugProyectoConPrefijoDesarrollo(): void
    {
        $this->assertTrue(CatalogoHelper::coincideSlugProyecto('Comonfort', 'comonfort'));
        $this->assertTrue(CatalogoHelper::coincideSlugProyecto('Desarrollo Comonfort', 'desarrollo-comonfort'));
        $this->assertTrue(CatalogoHelper::coincideSlugProyecto('Comonfort', 'desarrollo-comonfort'));
        $this->assertFalse(CatalogoHelper::coincideSlugProyecto('Comonfort', 'general'));
    }

    public function testUrlsUsanSlugsAscii(): void
    {
        $propiedad = [
            'project_name' => null,
            'name'         => 'Lafayette Juan Ruiz de Alarcón',
        ];

        $path = CatalogoHelper::pathInmueble($propiedad);

        $this->assertSame('desarrollo/general/lafayette-juan-ruiz-de-alarcon', $path);
        $this->assertStringNotContainsString('ó', $path);
        $this->assertStringNotContainsString('%', $path);
    }

    public function testRequiereRedireccionCanonicaConAcentos(): void
    {
        $canonico = 'lafayette-juan-ruiz-de-alarcon';

        $this->assertTrue(CatalogoHelper::requiereRedireccionCanonica('lafayette-juan-ruiz-de-alarcón', $canonico));
        $this->assertTrue(CatalogoHelper::requiereRedireccionCanonica('lafayette-juan-ruiz-de-alarc%C3%B3n', $canonico));
        $this->assertFalse(CatalogoHelper::requiereRedireccionCanonica($canonico, $canonico));
    }

    public function testParsePaymentMethods(): void
    {
        $this->assertSame(['Infonavit', 'Contado'], CatalogoHelper::parsePaymentMethods('["Infonavit","Contado"]'));
        $this->assertSame(['Bancario'], CatalogoHelper::parsePaymentMethods(['Bancario']));
        $this->assertSame([], CatalogoHelper::parsePaymentMethods(null));
    }
}
