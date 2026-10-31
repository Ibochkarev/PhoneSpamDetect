<?php

namespace PhoneSpamDetect;

use libphonenumber\NumberParseException;
use libphonenumber\PhoneNumberFormat;
use libphonenumber\PhoneNumberUtil;

/**
 * Локальная валидация через Google libphonenumber (бесплатно, без внешних API).
 */
class PhoneValidator
{
    private ?PhoneNumberUtil $util = null;

    /**
     * @return array{valid: bool, normalized: string, region: string, e164: string}|null null если библиотека недоступна.
     */
    public function validate(?string $phone, string $defaultRegion = 'RU'): ?array
    {
        if ($phone === null || $phone === '') {
            return [
                'valid' => false,
                'normalized' => '',
                'region' => '',
                'e164' => '',
            ];
        }

        $util = $this->getUtil();
        if ($util === null) {
            return null;
        }

        try {
            $number = $util->parse($phone, $defaultRegion);
            $isValid = $util->isValidNumber($number);
            $region = $util->getRegionCodeForNumber($number) ?? '';
            $e164 = $util->format($number, PhoneNumberFormat::E164);
            $normalized = preg_replace('/\D+/', '', $e164) ?? '';

            return [
                'valid' => $isValid,
                'normalized' => $normalized,
                'region' => $region,
                'e164' => $e164,
            ];
        } catch (NumberParseException) {
            return [
                'valid' => false,
                'normalized' => '',
                'region' => '',
                'e164' => '',
            ];
        }
    }

    private function getUtil(): ?PhoneNumberUtil
    {
        if ($this->util !== null) {
            return $this->util;
        }

        if (!class_exists(PhoneNumberUtil::class)) {
            return null;
        }

        $this->util = PhoneNumberUtil::getInstance();

        return $this->util;
    }
}
