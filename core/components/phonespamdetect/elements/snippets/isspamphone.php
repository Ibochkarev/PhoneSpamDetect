<?php

/** @var \MODX\Revolution\modX $modx */
/** @var array $scriptProperties */

$phone = $modx->getOption('phone', $scriptProperties, null);
if ($phone === '') {
    $phone = null;
}

try {
    $service = $modx->services->get('PhoneSpamDetect\\PhoneSpamDetectService');
} catch (\Throwable $e) {
    $modx->log(\MODX\Revolution\modX::LOG_LEVEL_ERROR, 'PhoneSpamDetect: ' . $e->getMessage());
    return '0';
}

$isSpam = $service->isSpamPhone($phone);
$check = $service->getLastCheck();

$placeholderPrefix = $modx->getOption('placeholderPrefix', $scriptProperties, 'phonespamdetect.');
$modx->setPlaceholder($placeholderPrefix . 'normalized', $check['normalized'] ?? '');
$modx->setPlaceholder($placeholderPrefix . 'reason', $check['reason'] ?? '');
$modx->setPlaceholder($placeholderPrefix . 'valid', $check['valid'] ?? '');
$modx->setPlaceholder($placeholderPrefix . 'region', $check['region'] ?? '');
$modx->setPlaceholder($placeholderPrefix . 'e164', $check['e164'] ?? '');

return $isSpam ? '1' : '0';
