<?php 

/**
 * Generates PNGs from SVGs
 */
function exception_error_handler($errno, $errstr, $errfile, $errline ) {
    if (!(error_reporting() & $errno)) {
        // Этот код ошибки не входит в error_reporting
        return;
    }
    throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}
set_error_handler("exception_error_handler");

if (empty($argv[1])) {
    echo "Usage: script inkscape-command [ --force ]\n";
    exit(1);
}

$tmpFile = '.tmp-png';
$commandFile = __DIR__ . '/inkscape-commands.tmp';
$inkscapeCommand = $argv[1];
$forceUpdate = in_array('--force', $argv);

$baseDir = dirname(__DIR__);
$files = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($baseDir)
);

$commandList = [];

/*
    Generate a batch file and execute it with inkscape
 */
foreach ($files as $file) {
    if (mb_strtolower($file->getExtension()) == 'svg') {
        $commands = exportSvg($file->getPathname(), $tmpFile, $forceUpdate);
        if ($commands) {
            $commandList = array_merge($commandList, $commands);
        }
    }

    if ($file->getBasename() == $tmpFile) {
        printf("Deleting old tmp file %s\n", $file->getBasename());
        unlink($file->getPathname());
        continue;
    }
}

// printf("Have %d inkscape commands\n", count($commandList));
if (!$commandList) {
    exit(0);
}

file_put_contents($commandFile, createInkscapeShellFile($commandList));
$cmd = buildCommandLine([$inkscapeCommand, '--shell']) . 
    ' < ' . escapeArgument(turnSlashes($commandFile));
printf("Exec: %s\n", $cmd);
passthru($cmd, $exitCode);
echo "\n";
usleep(1500000); // To let inkscape release commands file
unlink($commandFile);

if ($exitCode) {
    printf("Inkscape failed with code %d\n", $exitCode);
    exit(1);
}

exit(0);

function exportSvg($path, $tmpFile, $forceUpdate)
{
    $tmpPath = dirname($path) . '/' . $tmpFile;
    $outPath = preg_replace('/\.svg$/i', '', $path) . '.png';

    if (!$forceUpdate && file_exists($outPath)) {
        if (filemtime($outPath) >= filemtime($path)) {
            // printf("File already exists for %s\n", $path);
            return [];
        }

        // printf("File exists, but needs update %s\n", $path);
    }

    // printf("Export %s -> %s\n", $path, $outPath);
    $fixedPath = turnSlashes($path);
    $fixedOutPath = turnSlashes($outPath);

    $command = [
        "--file=$fixedPath", 
        "--export-png=$fixedOutPath", 
        '--export-area-page', 
        '--export-background=#ffffff',
        '--export-background-opacity=1.0'
    ];

    return [$command];
}

function turnSlashes($path)
{
    return str_replace('\\', '/', $path);
}

function buildCommandLine(array $args)
{
    $args = array_map('escapeArgument', $args);
    return implode(' ', $args);
}

function escapeArgument($arg)
{
    if (preg_match("!\A[a-z0-9A-Z\-._=/:]+\Z!", $arg)) {
        return $arg;
    }

    return escapeshellarg($arg);
}

function createInkscapeShellFile(array $commands)
{
    $text = '';
    foreach ($commands as $command) {
        $text .= buildCommandLine($command) . "\n";
    }

    $text .= "quit\n";

    return $text;
}


