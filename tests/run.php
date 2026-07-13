<?php

declare(strict_types=1);

$root = dirname(__DIR__);
$iterator = new RecursiveIteratorIterator(new RecursiveDirectoryIterator($root, FilesystemIterator::SKIP_DOTS));
foreach ($iterator as $file) {
    if (!$file->isFile() || $file->getExtension() !== 'php' || str_contains($file->getPathname(), '/.git/')) {
        continue;
    }
    passthru('php -l ' . escapeshellarg($file->getPathname()), $code);
    if ($code !== 0) {
        exit($code);
    }
}
foreach (glob(__DIR__ . '/*Test.php') as $test) {
    passthru('php ' . escapeshellarg($test), $code);
    if ($code !== 0) {
        exit($code);
    }
}
echo "OrgSuite PHP tests passed\n";
