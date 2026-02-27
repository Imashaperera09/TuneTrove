<?php
$files = [
    'c:/xampp/htdocs/TuneTrove/user/account/download.php',
    'c:/xampp/htdocs/TuneTrove/user/includes/db.php',
    'c:/xampp/htdocs/TuneTrove/user/includes/functions.php'
];

foreach ($files as $file) {
    if (!file_exists($file)) {
        echo "File not found: $file\n";
        continue;
    }
    $content = file_get_contents($file);
    
    // Check for UTF-8 BOM
    if (substr($content, 0, 3) === "\xef\xbb\xbf") {
        echo "BOM detected in $file. Removing...\n";
        $content = substr($content, 3);
        file_put_contents($file, $content);
    } else {
        echo "No BOM in $file\n";
    }

    // Check for leading whitespace before <?php
    if (preg_match('/^\s+<\?php/', $content)) {
        echo "Leading whitespace detected in $file. Trimming...\n";
        $content = ltrim($content);
        file_put_contents($file, $content);
    }
}
?>
