<?php

namespace PhoneSpamDetect;

/**
 * Проверка телефонов через Google libphonenumber (локально, без внешних API).
 */
class PhoneSpamDetectService
{
    /** @var array<string, mixed> */
    private array $lastCheck = [];

    /** @var string[] */
    private readonly array $allowedRegionCodes;

    private readonly string $defaultRegion;

    private readonly PhoneValidator $validator;

    public function __construct(
        private readonly bool $validateEnabled = true,
        string $defaultRegion = 'RU',
        string $allowedRegions = 'RU',
        private readonly bool $blockInvalid = true,
        ?PhoneValidator $validator = null,
    ) {
        $this->defaultRegion = strtoupper(trim($defaultRegion)) ?: 'RU';
        $this->allowedRegionCodes = self::parseRegions($allowedRegions);
        $this->validator = $validator ?? new PhoneValidator();
    }

    /**
     * Нормализует номер через libphonenumber или простой fallback.
     */
    public function normalizePhone(?string $phone): string
    {
        if ($phone === null || $phone === '') {
            return '';
        }

        $validation = $this->validator->validate($phone, $this->defaultRegion);
        if ($validation !== null && $validation['normalized'] !== '') {
            return $validation['normalized'];
        }

        return $this->normalizePhoneFallback($phone);
    }

    /**
     * Проверяет, нужно ли блокировать номер.
     */
    public function isSpamPhone(?string $phone): bool
    {
        $this->resetLastCheck();

        if ($phone === null || $phone === '') {
            return false;
        }

        $validation = $this->validator->validate($phone, $this->defaultRegion);

        if ($validation === null) {
            $normalized = $this->normalizePhoneFallback($phone);
            $this->lastCheck['normalized'] = $normalized;
            $this->lastCheck['reason'] = 'validator_unavailable_fail_open';

            return false;
        }

        $this->recordValidation($validation);

        if (!$this->validateEnabled) {
            $this->lastCheck['reason'] = 'allowed';

            return false;
        }

        if ($this->blockInvalid && (!$validation['valid'] || $validation['normalized'] === '')) {
            $this->lastCheck['reason'] = 'invalid_number';

            return true;
        }

        if (!$this->isAllowedRegion($validation['region'])) {
            $this->lastCheck['reason'] = 'region_not_allowed';

            return true;
        }

        $this->lastCheck['reason'] = 'allowed';

        return false;
    }

    public function getNormalizedPhone(?string $phone): string
    {
        return $this->normalizePhone($phone);
    }

    /**
     * @return array<string, mixed>
     */
    public function getLastCheck(): array
    {
        return $this->lastCheck;
    }

    private function isAllowedRegion(string $region): bool
    {
        if ($this->allowedRegionCodes === []) {
            return true;
        }

        return $region !== '' && in_array(strtoupper($region), $this->allowedRegionCodes, true);
    }

    /**
     * @return string[]
     */
    private static function parseRegions(string $raw): array
    {
        if ($raw === '') {
            return [];
        }

        $regions = preg_split('/[\s,;]+/u', strtoupper($raw), -1, PREG_SPLIT_NO_EMPTY);

        return is_array($regions) ? $regions : [];
    }

    private function resetLastCheck(): void
    {
        $this->lastCheck = [
            'normalized' => '',
            'reason' => '',
            'valid' => null,
            'region' => '',
            'e164' => '',
        ];
    }

    /**
     * @param array{valid: bool, normalized: string, region: string, e164: string} $validation
     */
    private function recordValidation(array $validation): void
    {
        $this->lastCheck['normalized'] = $validation['normalized'];
        $this->lastCheck['valid'] = $validation['valid'];
        $this->lastCheck['region'] = $validation['region'];
        $this->lastCheck['e164'] = $validation['e164'];
    }

    private function normalizePhoneFallback(?string $phone): string
    {
        if ($phone === null || $phone === '') {
            return '';
        }

        $digits = preg_replace('/\D+/', '', $phone) ?? '';
        if ($digits === '') {
            return '';
        }

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits = '7' . substr($digits, 1);
        }

        return $digits;
    }
}
