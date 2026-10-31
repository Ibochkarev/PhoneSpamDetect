# PhoneSpamDetect

Валидация телефонов в формах MODX через **Google libphonenumber** — локально, без API-ключей и без внешних запросов.

**Требования:** MODX Revolution 3.x, PHP 8.2+

**Текущая версия:** 1.0.1-pl (обновлена база libphonenumber до v9.0.41)

## Источник данных

Единственный источник — [Google libphonenumber](https://github.com/google/libphonenumber) (пакет `giggsey/libphonenumber-for-php`). Библиотека встроена в пакет и работает на сервере без интернета.

Проверяется:
- валидность формата номера;
- регион (ISO-код);
- нормализация в E.164.

## Быстрый старт

1. Установите пакет через **Менеджер пакетов**.
2. Добавьте preHook в FormIt:

```modx
[[!FormIt? &preHooks=`phoneSpamBlock` &hooks=`email,redirect` ...]]
```

По умолчанию блокируются:
- невалидные номера;
- номера вне разрешённого региона (`RU` по умолчанию).

Разрешённые регионы задаются в системной настройке `phonespamdetect_allowed_regions` ISO-кодами через запятую, например `RU,KZ,BY`. Если оставить настройку пустой, будут разрешены любые валидные номера.

## Документация

- [Руководство пользователя](user-guide.md)
- [API](core/components/phonespamdetect/docs/api.md)

## Сборка

```bash
php _build/build.php
```
