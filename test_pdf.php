<?php
define("FCPATH", "c:/Proyectos/tony/public/");
define("ROOTPATH", "c:/Proyectos/tony/");
define("APPPATH", "c:/Proyectos/tony/app/");

$realEstate = [
    "name" => "Duplex de Lujo en Venta",
    "clave" => "TEP-0005",
    "address" => "Centro - Paseo, Tepic",
    "price" => 1890000,
    "description_general" => "Aire acondicionado\nAlarma inteligente\nPiscina comunitaria\nGimnasio privado\nDomotica\nChimenea",
    "photos" => [],
    "amenities" => [
        ["value" => "210 m2", "amenity_type" => "Superficie util"],
        ["value" => "5", "amenity_type" => "Habitaciones"],
        ["value" => "4", "amenity_type" => "Banos"],
        ["value" => "2", "amenity_type" => "Estacionamientos"],
    ],
    "project_name" => null,
    "project_location" => null,
    "project_description" => null,
];
$show_branding = false;
$agent = null;
$company_name = "Pacific Inmobiliaria";
$company_website = "pacific.com";
$qrCodeUrl = "";

ob_start();
include "app/Views/admin/real_estates/pdf/technical_sheet_pdf.php";
$html = ob_get_clean();

require "vendor/autoload.php";
$options = new Dompdf\Options();
$options->set("isRemoteEnabled", false);
$dompdf = new Dompdf\Dompdf($options);
$dompdf->loadHtml($html);
$dompdf->setPaper("A4", "portrait");
$dompdf->render();
file_put_contents("writable/test_output.pdf", $dompdf->output());
echo "OK - " . strlen($dompdf->output()) . " bytes\n";
?>
