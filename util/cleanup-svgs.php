<?php

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    if (!(error_reporting() & $errno)) {
        // Этот код ошибки не входит в error_reporting
        return;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

$tmpName = '/cleanup-svg.tmp';
$baseDir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir)
);

foreach ($files as $file) {
    if (mb_strtolower($file->getExtension()) == 'svg') {
        printf("Fix file %s\n", $file->getPathname());
        fixSvgFile($file->getPathname(), $tmpName);
    }

    if ($file->getBasename() == $tmpName) {
        printf("Deleting old tmp file %s\n", $file->getBasename());
        unlink($file->getPathname());
        continue;
    }
}

function fixSvgFile($path, $tmpName) {    
    $tmpFile = dirname($path) . '/' . $tmpName;
    $contents = file_get_contents($path);
    $contents = preg_replace('~inkscape:(export-filename|window-(x|y|width|height))\s*=\s*"[^"]+"~', '', $contents);
    $contents = preg_replace("~inkscape:export-filename\s*=\s*'[^']+'~", '', $contents);
    file_put_contents($tmpFile, $contents);
    rename($tmpFile, $path);
}
