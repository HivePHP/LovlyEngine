<?php
/*
 * Copyright (c) 2026 HivePHP LovlyEngine
 *
 *  For the full copyright and license information, please view the LICENSE
 *   file that was distributed with this source code.
 *
 */
declare(strict_types=1);

/*
|--------------------------------------------------------------------------
| Static asset builder (OldVK-style)
|--------------------------------------------------------------------------
|
| Bundles & content-hashes the source assets:
|
|   resources/assets/css/<bundle-group>  ->  public/assets/css/<bundle>.<hash>.css
|   resources/assets/js/<module-graph>   ->  public/assets/js/.../<file>.<hash>.js
|
| Relative ES-module import specifiers are rewritten to the hashed output,
| so a change anywhere in the module graph cascades new hashes up to the
| page entry — making long-lived browser caches safe.
|
| Usage:  php bin/build-assets.php
|
*/

define('BASE_PATH', dirname(__DIR__));

$config = require BASE_PATH . '/config/assets.php';

// ---------------------------------------------------------------------------
// Output bookkeeping
// ---------------------------------------------------------------------------
$manifest = [
    'css' => [],
    'js'  => [],
];

$cssOutDir  = BASE_PATH . '/public/assets/css';
$jsOutDir   = BASE_PATH . '/public/assets/js';
$sourceRoot = BASE_PATH . '/resources/assets';

echo "== HivePHP asset builder ==\n";

// ---------------------------------------------------------------------------
// CSS bundles: concatenation + content hash
// ---------------------------------------------------------------------------
if (!is_dir($cssOutDir)) {
    mkdir($cssOutDir, 0777, true);
}

foreach ($config['css_bundles'] as $bundle => $sources) {
    $parts = [];

    foreach ($sources as $file) {
        $path = $sourceRoot . '/' . $file;

        if (!is_file($path)) {
            fwrite(STDERR, "!! CSS source not found: {$file}\n");
            exit(1);
        }

        $parts[] = file_get_contents($path);
    }

    $content = implode("\n", $parts);
    $hash    = substr(sha1($content), 0, 8);
    $name    = $bundle . '.' . $hash . '.css';

    file_put_contents($cssOutDir . '/' . $name, $content);

    $manifest['css'][$bundle] = 'css/' . $name;
    echo "  css  [{$bundle}] -> assets/css/{$name}\n";
}

// ---------------------------------------------------------------------------
// JS modules: graph resolution + hashed output
// ---------------------------------------------------------------------------
if (!is_dir($jsOutDir)) {
    mkdir($jsOutDir, 0777, true);
}

// logicalKey => output hash filename (e.g. 'core/Dom.js')
$jsHashes        = [];
$jsManifestPaths = [];

// resolve one source module relative to sourceRoot/js
$resolve = null;

$resolve = static function (string $relPath) use (&$resolve, $sourceRoot, $jsOutDir, &$jsHashes, &$jsManifestPaths): void {
    // already resolved?
    if (isset($jsHashes[$relPath])) {
        return;
    }

    $relPath = ltrim(str_replace('\\', '/', $relPath), '/');
    $absPath = $sourceRoot . '/js/' . $relPath;

    if (!is_file($absPath)) {
        fwrite(STDERR, "!! JS source not found: {$relPath}\n");
        exit(1);
    }

    $source = file_get_contents($absPath);
    $dir    = dirname($relPath);
    $dir    = $dir === '.' ? '' : $dir;

    // Rewrite relative import specifiers to the target's hashed output.
    $rewritten = preg_replace_callback(
        '/(\bfrom\s+)([\'"])(\.[^"\']+\.js)([\'"])/',
        static function ($m) use (&$resolve, &$jsHashes, $dir, $relPath) {
            $specifier = $m[3];

            // normalize relative path from importer dir
            $targetRel = $dir === ''
                ? $specifier
                : $dir . '/' . $specifier;

            $targetRel = normalizePath($targetRel);

            // resolve (recursively) to get its hash
            $resolve($targetRel);

            $targetHash = $jsHashes[$targetRel];
            $targetOut  = preg_replace('/\.js$/', '.' . $targetHash . '.js', $targetRel);

            // relative path from importer output dir -> target output file
            $rel = relativePath($dir, $targetOut);

            return $m[1] . $m[2] . $rel . $m[4];
        },
        $source
    );

    $hash = substr(sha1($rewritten), 0, 8);
    $jsHashes[$relPath] = $hash;

    $outName  = preg_replace('/\.js$/', '.' . $hash . '.js', basename($relPath));
    $outDir   = ($dir === '' ? $jsOutDir : $jsOutDir . '/' . $dir);
    $outFile  = $outDir . '/' . $outName;

    if (!is_dir($outDir)) {
        mkdir($outDir, 0777, true);
    }

    file_put_contents($outFile, $rewritten);

    $manifestPath = ($dir === '' ? '' : $dir . '/') . $outName;
    $jsManifestPaths['js/' . $relPath] = 'js/' . $manifestPath;
};

// Build every source module (all are reachable from page entries).
$jsSourceDir = $sourceRoot . '/js';
$iterator = new RecursiveIteratorIterator(
    new RecursiveDirectoryIterator($jsSourceDir, FilesystemIterator::SKIP_DOTS)
);

foreach ($iterator as $fileInfo) {
    if ($fileInfo->getExtension() !== 'js') {
        continue;
    }

    $rel = substr($fileInfo->getPathname(), strlen($jsSourceDir) + 1);
    $resolve(str_replace('\\', '/', $rel));
}

$manifest['js'] = $jsManifestPaths;

echo "  js   resolved " . count($jsManifestPaths) . " module(s)\n";

// ---------------------------------------------------------------------------
// Write manifest
// ---------------------------------------------------------------------------
$manifestPath = BASE_PATH . '/' . $config['manifest'];
$manifestDir  = dirname($manifestPath);

if (!is_dir($manifestDir)) {
    mkdir($manifestDir, 0777, true);
}

$export = var_export($manifest, true);
$php    = "<?php\n\nreturn {$export};\n";

file_put_contents($manifestPath, $php);

echo "== done -> {$config['manifest']}\n";

// ---------------------------------------------------------------------------
// Helpers
// ---------------------------------------------------------------------------
/**
 * Normalize a relative path ('.' and '..', no leading slash).
 */
function normalizePath(string $path): string
{
    $parts = [];
    foreach (explode('/', $path) as $part) {
        if ($part === '' || $part === '.') {
            continue;
        }
        if ($part === '..') {
            array_pop($parts);
            continue;
        }
        $parts[] = $part;
    }

    return implode('/', $parts);
}

/**
 * Relative path from directory $from to file $to (both relative, no leading slash).
 *
 * Always returns a valid ES-module specifier: absolute-relative ("../x.js")
 * for a sibling/ancestor target, or "./x.js" for a same-directory target.
 */
function relativePath(string $from, string $to): string
{
    $from = $from === '' ? [] : explode('/', $from);
    $to   = explode('/', $to);

    // remove common prefix
    while ($from && $to && $from[0] === $to[0]) {
        array_shift($from);
        array_shift($to);
    }

    $up  = array_fill(0, count($from), '..');
    $rel = implode('/', array_merge($up, $to));

    // A bare specifier (e.g. "RegistrationValidator.js") would be treated as a
    // package name by the browser — always keep a leading "./".
    if ($rel !== '' && !str_starts_with($rel, '.')) {
        $rel = './' . $rel;
    }

    return $rel;
}
