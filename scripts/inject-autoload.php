<?php

// Always the project root when Composer runs scripts
$root = getcwd();
$composerFile = $root . '/composer.json';

$composer = json_decode(file_get_contents($composerFile), true);

// Ensure autoload + psr-4 exist
if (!isset($composer['autoload'])) {
    $composer['autoload'] = [];
}
if (!isset($composer['autoload']['psr-4'])) {
    $composer['autoload']['psr-4'] = [];
}

// Add (or update) your namespaces
$composer['autoload']['psr-4']['RestInPeace\\Models\\'] = 'models/';
$composer['autoload']['psr-4']['RestInPeace\\Models\\Traits\\'] = 'traits/';

// Save back composer.json
file_put_contents(
    $composerFile,
    json_encode($composer, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL
);

echo "[restinpeace] Autoload injected into project composer.json\n";

// Rebuild autoloader
exec('composer dump-autoload');
