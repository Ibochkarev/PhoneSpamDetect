<?php

$_lang['area_phonespamdetect_main'] = 'PhoneSpamDetect';
$_lang['area_phonespamdetect_validation'] = 'PhoneSpamDetect — libphonenumber';

$_lang['setting_phonespamdetect_phone_field'] = 'Имя поля телефона';
$_lang['setting_phonespamdetect_phone_field_desc'] = 'Имя поля формы, из которого читается номер телефона.';

$_lang['setting_phonespamdetect_block_message'] = 'Сообщение при блокировке формы';
$_lang['setting_phonespamdetect_block_message_desc'] = 'Текст, который видит пользователь при блокировке номера.';

$_lang['setting_phonespamdetect_log_blocked'] = 'Логировать заблокированные отправки';
$_lang['setting_phonespamdetect_log_blocked_desc'] = 'Записывать в лог MODX отправки с заблокированными номерами.';

$_lang['setting_phonespamdetect_validate_enabled'] = 'Включить валидацию номера';
$_lang['setting_phonespamdetect_validate_enabled_desc'] = 'Проверять номер через Google libphonenumber без внешних API.';

$_lang['setting_phonespamdetect_default_region'] = 'Регион по умолчанию';
$_lang['setting_phonespamdetect_default_region_desc'] = 'ISO-код страны для национальных номеров, например RU.';

$_lang['setting_phonespamdetect_allowed_regions'] = 'Разрешённые регионы';
$_lang['setting_phonespamdetect_allowed_regions_desc'] = 'ISO-коды через запятую, например RU,KZ,BY. Пусто — любой валидный номер.';

$_lang['setting_phonespamdetect_block_invalid'] = 'Блокировать невалидные номера';
$_lang['setting_phonespamdetect_block_invalid_desc'] = 'Блокировать форму, если номер невалиден.';
