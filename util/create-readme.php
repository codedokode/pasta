<?php 

function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    if (!(error_reporting() & $errno)) {
        // Этот код ошибки не входит в error_reporting
        return;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

$baseDir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir)
);

foreach ($files as $file) {
    if ($file->isFile() && preg_match("/\.(md|txt)$/", $file->getFilename())) {
        $list[] = $file->getPathname();
    }
}

sort($list);
$headerList = [];

foreach ($list as $path) {
    $content = file_get_contents($path);
    if (!preg_match("/^#\s([^\n]+)$/mu", $content, $m)) {
        throw new Exception("No H1 in file $path");
    }

    $h1 = trim($m[1]);
    $relativePath = getRelativePath($path, $baseDir);
    $section = dirname($relativePath);
    $headerList[$section][] = '- ' . createLink($relativePath, $h1);
}

ksort($headerList);

$indexTemplatePath = __DIR__ . '/README.md.template';
$readmePath = dirname(__DIR__) . '/README.md';

$headerListContent = '';
foreach ($headerList as $title => $section) {
    $title = ($title && $title != '.') ? $title . '/' : 'Основное';
    $headerListContent .= "### {$title}\n\n";
    $headerListContent .= implode("\n", $section) . "\n\n";
}

$readme = file_get_contents($indexTemplatePath);
$readme = preg_replace("/{CONTENTS}/", $headerListContent, $readme);
file_put_contents($readmePath, $readme);

function getRelativePath($path, $base)
{
    $path = realpath($path);
    $base = realpath($base);

    $baseLen = mb_strlen($base);
    $head = mb_substr($path, 0, $baseLen);
    $tail = mb_substr($path, $baseLen);

    assert($head === $base);
    $tail = str_replace('\\', '/', $tail);
    $tail = ltrim($tail, '/');

    return $tail;
}

function createLink($url, $text)
{
    return "[$text]($url)";
}