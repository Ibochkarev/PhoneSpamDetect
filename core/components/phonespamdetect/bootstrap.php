<?php

/**
 * @var \MODX\Revolution\modX $modx
 * @var array $namespace
 */

$componentPath = $namespace['path'];
$vendorPath = $componentPath . 'vendor/';

if (file_exists($vendorPath . 'autoload.php')) {
    require_once $vendorPath . 'autoload.php';
}

$modx->addPackage('PhoneSpamDetect\\', $componentPath . 'src/', null, 'PhoneSpamDetect\\');

$modx->services->add('PhoneSpamDetect\\PhoneSpamDetectService', function ($c) use ($modx) {
    return new \PhoneSpamDetect\PhoneSpamDetectService(
        (bool) $modx->getOption('phonespamdetect_validate_enabled', null, true),
        (string) $modx->getOption('phonespamdetect_default_region', null, 'RU'),
        (string) $modx->getOption('phonespamdetect_allowed_regions', null, 'RU'),
        (bool) $modx->getOption('phonespamdetect_block_invalid', null, true),
        new \PhoneSpamDetect\PhoneValidator()
    );
});
