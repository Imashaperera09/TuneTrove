<?php
$f = 'c:/xampp/htdocs/TuneTrove/user/assets/downloads/sheet_music_test.pdf';
if (!file_exists($f)) die('Test file missing');
header('Content-Type: application/pdf');
header('Content-Disposition: attachment; filename="test.pdf"');
header('Content-Length: ' . filesize($f));
readfile($f);
exit;
