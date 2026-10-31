# PhoneSpamDetect — API документация

**Версия:** 1.0.0-pl

---

## Источник данных

**Google libphonenumber** — локальная библиотека `giggsey/libphonenumber-for-php`. Без API-ключей и без сетевых запросов.

Проверяет:
- валидность номера;
- регион (ISO-код);
- формат E.164.

---

## Логика блокировки

1. `validate_enabled` + `block_invalid` + невалидный → `invalid_number`
2. Регион не в `allowed_regions` → `region_not_allowed`
3. Иначе → `allowed`

При недоступности библиотеки — fail-open (`validator_unavailable_fail_open`).

---

## Плейсхолдеры isSpamPhone

- `phonespamdetect.normalized`
- `phonespamdetect.reason`
- `phonespamdetect.valid`
- `phonespamdetect.region`
- `phonespamdetect.e164`

---

## PHP

```php
$service = $modx->services->get('PhoneSpamDetect\\PhoneSpamDetectService');
$isSpam = $service->isSpamPhone('+7 (999) 123-45-67');
$check = $service->getLastCheck();
```
