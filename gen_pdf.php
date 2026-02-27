<?php
$f = 'c:/xampp/htdocs/TuneTrove/user/assets/downloads/sheet_music_test.pdf';
$data = "%PDF-1.1\n";
$data .= "1 0 obj << /Type /Catalog /Pages 2 0 R >> endobj\n";
$data .= "2 0 obj << /Type /Pages /Kids [3 0 R] /Count 1 >> endobj\n";
$data .= "3 0 obj << /Type /Page /Parent 2 0 R /Resources << >> /Contents 4 0 R >> endobj\n";
$data .= "4 0 obj << /Length 21 >> stream\n";
$data .= "BT /F1 12 Tf 100 700 Td (Hello World) Tj ET\n";
$data .= "endstream\n";
$data .= "endobj\n";
$data .= "xref\n";
$data .= "0 5\n";
$data .= "0000000000 65535 f \n";
$data .= "0000000009 00000 n \n";
$data .= "0000000056 00000 n \n";
$data .= "0000000111 00000 n \n";
$data .= "0000000212 00000 n \n";
$data .= "trailer << /Size 5 /Root 1 0 R >>\n";
$data .= "startxref\n";
$data .= "284\n";
$data .= "%%EOF\n";

file_put_contents($f, $data);
echo "File written. Size: " . strlen($data) . " bytes.\n";
?>
