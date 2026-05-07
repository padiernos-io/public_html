<?php
declare(strict_types=1);

namespace Twiggy;

require_once __DIR__ . DIRECTORY_SEPARATOR . 'getTwigMetadata.php';

[, $TWIG_ENVIRONMENT_FILE] = $argv;

/** @var \Twig\Environment $twig */
$twig = require $TWIG_ENVIRONMENT_FILE;

$twigMetadata = \Twiggy\Metadata\getTwigMetadata($twig);

echo json_encode($twigMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) . PHP_EOL;
