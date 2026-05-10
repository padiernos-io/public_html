<?php

require __DIR__ . '/../vendor/autoload.php';

$themeRoot = dirname(__DIR__);

$loader = new \Twig\Loader\FilesystemLoader();

$namespaces = [
    'nucleus'   => $themeRoot . '/source/00-nucleus',
    'atoms'     => $themeRoot . '/source/01-atoms',
    'molecules' => $themeRoot . '/source/02-molecules',
    'organisms' => $themeRoot . '/source/03-organisms',
    'symbiosis' => $themeRoot . '/source/04-symbiosis',
    'synergy'   => $themeRoot . '/source/05-synergy',
];

foreach ($namespaces as $namespace => $path) {
    if (is_dir($path)) {
        $loader->addPath($path, $namespace);
    }
}

return new \Twig\Environment($loader);
