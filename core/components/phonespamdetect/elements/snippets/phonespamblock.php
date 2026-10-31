<?php

/**
 * FormIt preHook: блокирует отправку при невалидном номере или запрещённом регионе (libphonenumber).
 * Добавьте в &preHooks вызова FormIt: phoneSpamBlock
 *
 * При прямом вызове [[!phoneSpamBlock]] возвращает 0 (разрешено) или 1 (заблокировано) — как isSpamPhone.
 * В контексте FormIt: true = разрешить, false = заблокировать.
 *
 * @var \MODX\Revolution\modX $modx
 * @var array $scriptProperties
 * @var object|null $hook Объект хука FormIt (если передан).
 */

if (!isset($scriptProperties)) {
    $scriptProperties = [];
}

$isFormItHook = isset($hook) && is_object($hook);
$modxClass = $modx::class;

try {
    $service = $modx->services->get('PhoneSpamDetect\\PhoneSpamDetectService');
} catch (\Throwable $e) {
    $modx->log($modxClass::LOG_LEVEL_ERROR, 'PhoneSpamDetect: ' . $e->getMessage());
    return $isFormItHook ? true : '0';
}

$phoneField = $modx->getOption('phoneField', $scriptProperties, '');
if ($phoneField === '') {
    $phoneField = $modx->getOption('phonespamdetect_phone_field', null, 'phone');
}

$phone = null;
if ($isFormItHook && method_exists($hook, 'getValue')) {
    $phone = $hook->getValue($phoneField);
}

if ($phone === null || $phone === '') {
    $phone = $_POST[$phoneField] ?? null;
}

if ($phone === null || $phone === '') {
    return $isFormItHook ? true : '0';
}

if (!$service->isSpamPhone($phone)) {
    return $isFormItHook ? true : '0';
}

$blockMessage = $modx->getOption(
    'phonespamdetect_block_message',
    null,
    'Не удалось отправить форму. Укажите другой номер телефона.'
);
$logBlocked = (bool) $modx->getOption('phonespamdetect_log_blocked', null, true);
$check = $service->getLastCheck();
$normalizedPhone = $check['normalized'] ?? $service->getNormalizedPhone($phone);
$reason = $check['reason'] ?? 'unknown';

if ($logBlocked) {
    $logMessage = 'PhoneSpamDetect: форма заблокирована. Поле: ' . $phoneField
        . ', номер: ' . $normalizedPhone
        . ', причина: ' . $reason;

    $modx->log(
        $modxClass::LOG_LEVEL_INFO,
        $logMessage
    );
}

if ($isFormItHook && method_exists($hook, 'addError')) {
    $hook->addError($phoneField, $blockMessage);
}

$modx->setPlaceholder('fi.validation_error_message', $blockMessage);

return $isFormItHook ? false : '1';
