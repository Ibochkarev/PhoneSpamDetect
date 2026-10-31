# Руководство пользователя PhoneSpamDetect

**Версия:** 1.0.0-pl
**Для:** MODX Revolution 3.x

---

## Содержание

1. [Введение](#1-введение)
2. [Быстрый старт](#2-быстрый-старт)
3. [Установка](#3-установка)
4. [Защита форм от невалидных телефонов](#4-защита-форм-от-невалидных-телефонов)
5. [Проверка номера в шаблонах](#5-проверка-номера-в-шаблонах)
6. [Настройка](#6-настройка)
7. [Типовые сценарии](#7-типовые-сценарии)
8. [Решение проблем](#8-решение-проблем)
9. [Часто задаваемые вопросы](#9-часто-задаваемые-вопросы)
10. [Ссылки](#10-ссылки)

---

## 1. Введение

### Что такое PhoneSpamDetect?

PhoneSpamDetect — дополнение для MODX, которое проверяет телефонные номера в формах через локальную библиотеку Google libphonenumber. Это позволяет:

- **Отсекать мусорные номера** — блокировать заведомо невалидные телефоны до отправки формы
- **Ограничивать страны** — принимать номера только из разрешённых регионов
- **Нормализовать телефоны** — получать номер в едином формате E.164 для CRM, писем и логов

### Что именно проверяется

Дополнение использует только [Google libphonenumber](https://github.com/google/libphonenumber) через PHP-пакет `giggsey/libphonenumber-for-php`.

Проверяется:

- корректность формата номера;
- реальный регион номера по ISO-коду страны;
- нормализация в E.164, например `+79991234567`.

> libphonenumber не определяет «спам» по базам жалоб и не отправляет номер во внешние API. Она локально отсекает невалидные и нежелательные по региону номера.

### Для кого эта документация

| Роль | Что найдёте |
|------|-------------|
| **Владелец сайта** | Защита форм, настройка стран, понятные сообщения пользователю |
| **Разработчик** | Примеры FormIt, Fenom, FetchIt, SendIt и плейсхолдеры проверки |
| **Маркетолог / CRM-специалист** | Более чистые заявки и номера в едином формате |

### Требования

- MODX Revolution 3.x
- PHP 8.2+
- FormIt (для защиты форм)
- FetchIt (опционально, для AJAX-форм)
- SendIt (опционально, для AJAX-форм)

---

## 2. Быстрый старт

### За 2 минуты: защитить контактную форму

**Шаг 1.** Откройте страницу с формой в редакторе MODX.

**Шаг 2.** Найдите вызов FormIt и добавьте в него `&preHooks=\`phoneSpamBlock\``:

**MODX (теги):**
```modx
[[!FormIt?
    &preHooks=`phoneSpamBlock`
    &hooks=`email,redirect`
    &validate=`name:required,phone:required,email:required:email`
    &redirectTo=`[[*id]]`
    &emailTo=`[[++emailsender]]`
    &emailSubject=`Обратная связь`
]]
[[+fi.validation_error_message]]
<form action="[[~[[*id]]]]" method="post">
    <input type="text" name="name" value="[[+fi.name]]" />
    <input type="tel" name="phone" value="[[+fi.phone]]" />
    <input type="email" name="email" value="[[+fi.email]]" />
    <button type="submit" name="submit">Отправить</button>
</form>
```

**Fenom:**
```fenom
{$modx->runSnippet('FormIt', [
    'preHooks' => 'phoneSpamBlock',
    'hooks' => 'email,redirect',
    'validate' => 'name:required,phone:required,email:required:email',
    'redirectTo' => $modx->resource->id,
    'emailTo' => $modx->getOption('emailsender'),
    'emailSubject' => 'Обратная связь'
])}
{if $modx->getPlaceholder('fi.validation_error_message')}
    <div class="error">{$modx->getPlaceholder('fi.validation_error_message')}</div>
{/if}
<form action="{$modx->makeUrl($modx->resource->id)}" method="post">
    <input type="text" name="name" value="{$modx->getPlaceholder('fi.name')}" />
    <input type="tel" name="phone" value="{$modx->getPlaceholder('fi.phone')}" />
    <input type="email" name="email" value="{$modx->getPlaceholder('fi.email')}" />
    <button type="submit">Отправить</button>
</form>
```

**Шаг 3.** Если нужны страны кроме России, откройте системную настройку `phonespamdetect_allowed_regions` и укажите ISO-коды через запятую:

```text
RU,KZ,BY
```

**Шаг 4.** Сохраните страницу. Форма блокирует невалидные телефоны и номера вне разрешённых регионов.

---

## 3. Установка

### Установка через Менеджер пакетов

1. Управление пакетами → **Установить пакеты**
2. Найдите **PhoneSpamDetect** в репозитории
3. Нажмите **Установить**

**Важно:** зависимости `giggsey/libphonenumber-for-php` уже входят в пакет. Запускать `composer install` на сервере не нужно.

### Проверка установки

После установки в меню «Элементы» → «Сниппеты» должны появиться:

- `phoneSpamBlock` — preHook для FormIt
- `isSpamPhone` — проверка номера в шаблонах и логике сайта

Также должны появиться системные настройки с namespace `phonespamdetect`.

---

## 4. Защита форм от невалидных телефонов

### Как это работает

1. Пользователь отправляет форму
2. FormIt вызывает preHook `phoneSpamBlock` до валидации и отправки
3. Номер проверяется локально через libphonenumber
4. Если номер невалиден или регион запрещён → форма не обрабатывается, показывается сообщение
5. Если номер прошёл проверку → форма обрабатывается как обычно

### Обычная форма (FormIt)

Добавьте `phoneSpamBlock` в `&preHooks` вызова FormIt. Если у вас уже есть другие preHooks, перечислите их через запятую:

**MODX:**
```modx
&preHooks=`phoneSpamBlock,другойХук`
```

**Fenom:**
```fenom
'preHooks' => 'phoneSpamBlock,другойХук'
```

### Если поле телефона называется не phone

По умолчанию preHook читает поле `phone`. Можно изменить глобально через системную настройку `phonespamdetect_phone_field` или локально в вызове FormIt:

**MODX:**
```modx
[[!FormIt?
    &preHooks=`phoneSpamBlock`
    &phoneField=`mobile`
    &validate=`name:required,mobile:required,email:required:email`
]]
```

**Fenom:**
```fenom
{$modx->runSnippet('FormIt', [
    'preHooks' => 'phoneSpamBlock',
    'phoneField' => 'mobile',
    'validate' => 'name:required,mobile:required,email:required:email'
])}
```

### AJAX-форма (FetchIt)

FetchIt обрабатывает формы через FormIt на сервере. Чтобы защитить форму:

1. В конфигурации FetchIt укажите URL/сниппет, который вызывает FormIt
2. В вызов FormIt добавьте `&preHooks="phoneSpamBlock"`
3. Убедитесь, что поле телефона совпадает с `phonespamdetect_phone_field` или параметром `phoneField`
4. При блокировке FetchIt получит ошибку FormIt и покажет сообщение из настроек PhoneSpamDetect

**Пример:** если FetchIt отправляет данные на `[[~123]]` (страница с FormIt), убедитесь, что на этой странице FormIt вызывается с `phoneSpamBlock` в preHooks.

### AJAX-форма (SendIt)

SendIt обрабатывает формы через FormIt; параметры вызова задаются в пресетах (файл из настройки **si_path_to_presets**). Чтобы защитить форму:

1. Откройте файл пресетов (свою копию, не стандартный `core/components/sendit/presets/sendit.inc.php` — при обновлении SendIt он перезаписывается).
2. Добавьте в нужный пресет `preHooks` с `phoneSpamBlock`.
3. Если поле телефона не `phone`, добавьте `phoneField`.
4. При блокировке SendIt вернёт ошибку и покажет сообщение из настроек PhoneSpamDetect.

**Пример пресета:**
```php
return [
    'contact' => [
        'preHooks' => 'phoneSpamBlock',
        'phoneField' => 'phone',
        'hooks' => 'email,FormItSaveForm',
        'validate' => 'name:required,phone:required,email:email:required',
        'emailTo' => 'manager@site.ru',
        'emailSubject' => 'Обратная связь',
        // ...
    ],
];
```

Если preHooks уже есть, перечислите через запятую: `'preHooks' => 'phoneSpamBlock,другойХук'`.

### Сообщение при блокировке

Текст сообщения настраивается в **Системные настройки** → `phonespamdetect` → `phonespamdetect_block_message`.

По умолчанию: «Не удалось отправить форму. Укажите другой номер телефона.»

Вывод сообщения: через плейсхолдер FormIt `[[+fi.validation_error_message]]` (MODX) или `{$modx->getPlaceholder('fi.validation_error_message')}` (Fenom).

---

## 5. Проверка номера в шаблонах

### Сниппет isSpamPhone

Проверяет переданный номер и возвращает:

- `"1"` — номер нужно блокировать
- `"0"` — номер разрешён или проверка недоступна

**Важно:** для пользовательских данных вызывайте без кэша: `[[!isSpamPhone]]`.

### Проверить конкретный номер

**MODX:**
```modx
[[!isSpamPhone? &phone=`+7 (999) 123-45-67`]]
```

**Fenom:**
```fenom
{$modx->runSnippet('isSpamPhone', [
    'phone' => '+7 (999) 123-45-67'
])}
```

### Получить результат проверки

После вызова `isSpamPhone` доступны плейсхолдеры:

| Плейсхолдер | Описание |
|-------------|----------|
| `phonespamdetect.normalized` | Номер только цифрами |
| `phonespamdetect.reason` | Причина результата |
| `phonespamdetect.valid` | Валиден ли номер по libphonenumber |
| `phonespamdetect.region` | ISO-код региона |
| `phonespamdetect.e164` | Номер в формате E.164 |

**MODX:**
```modx
[[!isSpamPhone? &phone=`+7 (999) 123-45-67`]]
Регион: [[+phonespamdetect.region]]
E.164: [[+phonespamdetect.e164]]
Причина: [[+phonespamdetect.reason]]
```

**Fenom:**
```fenom
{set $isBlocked = $modx->runSnippet('isSpamPhone', [
    'phone' => '+7 (999) 123-45-67'
])}
{if $isBlocked == '1'}
    Номер заблокирован: {$modx->getPlaceholder('phonespamdetect.reason')}
{else}
    Номер принят: {$modx->getPlaceholder('phonespamdetect.e164')}
{/if}
```

### Свой префикс плейсхолдеров

Если на странице нужно проверить несколько номеров, задайте `placeholderPrefix`:

```modx
[[!isSpamPhone?
    &phone=`+7 (999) 123-45-67`
    &placeholderPrefix=`lead_phone.`
]]
[[+lead_phone.region]]
```

---

## 6. Настройка

### Системные настройки (namespace: phonespamdetect)

| Настройка | Описание | По умолчанию |
|-----------|----------|--------------|
| `phonespamdetect_phone_field` | Имя поля формы, из которого читается номер телефона | `phone` |
| `phonespamdetect_block_message` | Текст при блокировке формы | «Не удалось отправить форму. Укажите другой номер телефона.» |
| `phonespamdetect_log_blocked` | Логировать заблокированные отправки в лог MODX | Да |
| `phonespamdetect_validate_enabled` | Проверять номер через Google libphonenumber | Да |
| `phonespamdetect_default_region` | Регион для национальных номеров без `+` | `RU` |
| `phonespamdetect_allowed_regions` | Разрешённые ISO-коды стран; пусто — любой валидный номер | `RU` |
| `phonespamdetect_block_invalid` | Блокировать форму, если номер невалиден | Да |

### Где изменить

**Система** → **Системные настройки** → фильтр по namespace `phonespamdetect`.

### Как указать один регион или список

`phonespamdetect_allowed_regions` принимает ISO-коды стран. Основной формат — через запятую:

```text
RU
```

```text
RU,KZ,BY
```

```text
US,CA
```

Допускаются пробелы и переносы строк, но для читаемости лучше использовать запятые без пробелов.

Если настройку оставить пустой, PhoneSpamDetect будет принимать любой валидный номер, независимо от страны.

### Разница между default_region и allowed_regions

`phonespamdetect_default_region` нужен только для номеров без международного префикса.

Например, пользователь ввёл:

```text
8 (999) 123-45-67
```

При `phonespamdetect_default_region = RU` библиотека будет разбирать этот номер как российский. При `phonespamdetect_default_region = KZ` — как казахстанский национальный номер.

`phonespamdetect_allowed_regions` отвечает за блокировку по стране после разбора номера. Например:

```text
RU,KZ,BY
```

означает: российские, казахстанские и белорусские номера разрешены; остальные валидные номера будут заблокированы с причиной `region_not_allowed`.

### Свойства сниппетов

| Сниппет | Свойство | Описание | По умолчанию |
|---------|----------|----------|--------------|
| `phoneSpamBlock` | `phoneField` | Имя поля формы с телефоном | системная настройка `phonespamdetect_phone_field` |
| `isSpamPhone` | `phone` | Номер телефона для проверки | — |
| `isSpamPhone` | `placeholderPrefix` | Префикс плейсхолдеров результата | `phonespamdetect.` |

---

## 7. Типовые сценарии

### Сценарий 1: Контактная форма только для РФ

Оставьте настройки по умолчанию:

```text
phonespamdetect_default_region = RU
phonespamdetect_allowed_regions = RU
```

Добавьте `phoneSpamBlock` в preHooks FormIt (см. [Быстрый старт](#2-быстрый-старт)).

### Сценарий 2: Принимать РФ, Казахстан и Беларусь

В системной настройке `phonespamdetect_allowed_regions` укажите:

```text
RU,KZ,BY
```

Если большинство пользователей вводят российские номера без `+7`, оставьте:

```text
phonespamdetect_default_region = RU
```

### Сценарий 3: Принимать любые валидные номера

Оставьте `phonespamdetect_allowed_regions` пустым.

Такой режим полезен для международных сайтов: невалидные номера блокируются, но страна не ограничивается.

### Сценарий 4: Несколько форм с разными полями телефона

Глобально оставьте:

```text
phonespamdetect_phone_field = phone
```

В форме, где поле называется иначе, задайте `phoneField` локально:

```modx
[[!FormIt?
    &preHooks=`phoneSpamBlock`
    &phoneField=`mobile`
    &validate=`mobile:required`
]]
```

### Сценарий 5: Совместно с CrawlerDetect

Можно использовать оба preHook в одной форме:

```modx
&preHooks=`crawlerDetectBlock,phoneSpamBlock`
```

Рекомендуемый порядок: сначала `crawlerDetectBlock`, затем `phoneSpamBlock`. Так бот будет отсечён до проверки телефона.

### Сценарий 6: Сохранить нормализованный номер

Для сохранения в CRM или письмо лучше использовать E.164. Вызовите `isSpamPhone` перед выводом или сохранением и возьмите плейсхолдер `phonespamdetect.e164`.

```modx
[[!isSpamPhone? &phone=`[[+fi.phone]]`]]
[[+phonespamdetect.e164]]
```

---

## 8. Решение проблем

### Форма не блокирует неправильные номера

**Проверьте:**

1. `phoneSpamBlock` указан в `&preHooks` вызова FormIt
2. Форма отправляется через FormIt, а не через другой обработчик
3. Поле телефона называется так же, как `phonespamdetect_phone_field` или параметр `phoneField`
4. Настройка `phonespamdetect_validate_enabled` включена
5. Настройка `phonespamdetect_block_invalid` включена

### Номер из другой страны проходит

**Проверьте:**

1. В `phonespamdetect_allowed_regions` нет лишнего ISO-кода страны
2. Настройка не пустая: пустое значение означает «любой валидный номер»
3. Код страны указан в формате ISO, например `RU`, `KZ`, `BY`, `US`, `CA`

### Нормальный номер блокируется

**Возможные причины:**

1. Пользователь ввёл национальный номер без `+`, а `phonespamdetect_default_region` настроен не под его страну
2. Регион номера не добавлен в `phonespamdetect_allowed_regions`
3. Номер выглядит привычно пользователю, но libphonenumber считает его невалидным для этой страны

**Что сделать:** временно включите `phonespamdetect_log_blocked`, отправьте форму и проверьте причину в системном журнале MODX.

### Сообщение при блокировке не показывается

**Проверьте:**

1. В шаблоне формы выводится `[[+fi.validation_error_message]]` (MODX) или `{$modx->getPlaceholder('fi.validation_error_message')}` (Fenom)
2. Плейсхолдер не перезаписывается другими хуками
3. AJAX-обработчик корректно показывает ошибки FormIt

### isSpamPhone всегда возвращает 0

**Возможные причины:**

1. В параметр `phone` не передан номер
2. Проверка выключена настройкой `phonespamdetect_validate_enabled`
3. Библиотека недоступна; в этом случае дополнение работает fail-open и не блокирует отправку

### Просмотр логов

**Управление** → **Системный журнал** — при включённой настройке `phonespamdetect_log_blocked` заблокированные попытки будут в логе.

В логе указывается поле телефона, нормализованный номер и причина блокировки.

---

## 9. Часто задаваемые вопросы

### Нужно ли запускать composer install на сервере?

Нет. Зависимости уже входят в пакет. Установите PhoneSpamDetect через Менеджер пакетов — этого достаточно.

### Отправляет ли PhoneSpamDetect номера во внешние сервисы?

Нет. Проверка выполняется локально через встроенную библиотеку `giggsey/libphonenumber-for-php`.

### Можно ли указать несколько разрешённых стран?

Да. В `phonespamdetect_allowed_regions` укажите ISO-коды через запятую:

```text
RU,KZ,BY
```

### Что будет, если allowed_regions оставить пустым?

Будут разрешены любые валидные номера. Блокировка по стране отключится, но проверка валидности останется включённой.

### Чем отличаются RU и +7?

`RU` — ISO-код страны для настройки региона. `+7` — телефонный код страны. В настройках PhoneSpamDetect нужно использовать ISO-коды: `RU`, `KZ`, `BY`, `US`, `CA`.

### Работает ли с CAPTCHA?

Да. PhoneSpamDetect можно использовать вместе с reCAPTCHA или другими CAPTCHA: добавьте оба preHook в FormIt.

**MODX:** `&preHooks=\`phoneSpamBlock,recaptcha\``

**Fenom:** `'preHooks' => 'phoneSpamBlock,recaptcha'`

Если также используется CrawlerDetect, обычно порядок такой:

```modx
&preHooks=`crawlerDetectBlock,phoneSpamBlock,recaptcha`
```

### Работает ли с AjaxForm?

AjaxForm — альтернатива FormIt. PhoneSpamDetect интегрирован с FormIt. Если AjaxForm вызывает FormIt на сервере, добавьте `phoneSpamBlock` в preHooks FormIt — защита будет работать.

### Работает ли с SendIt?

Да. SendIt использует FormIt; параметры задаются в пресетах. Добавьте в пресет `'preHooks' => 'phoneSpamBlock'` — при блокировке SendIt вернёт ошибку и покажет сообщение из настроек PhoneSpamDetect (см. [Защита форм от невалидных телефонов → SendIt](#ajax-форма-sendit)).

### Поддерживается ли MODX 2.x?

Нет. Только MODX Revolution 3.x.

### Можно ли проверять номера по базе спамеров?

В текущей версии — нет. PhoneSpamDetect использует только Google libphonenumber: формат, валидность, регион и нормализацию. Базы жалоб, HLR-запросы и внешние скоринговые API не используются.

---

## 10. Ссылки

- [Google libphonenumber](https://github.com/google/libphonenumber) — библиотека проверки телефонных номеров
- [giggsey/libphonenumber-for-php](https://github.com/giggsey/libphonenumber-for-php) — PHP-порт libphonenumber
- [FormIt (MODX 3.x)](https://docs.modx.com/3.x/ru/extras/formit) — документация FormIt
- [FetchIt](https://docs.modx.pro/components/fetchit/) — документация FetchIt
- [API-документация](core/components/phonespamdetect/docs/api.md) — плейсхолдеры и PHP-примеры
