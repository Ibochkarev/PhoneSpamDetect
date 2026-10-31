<?php

$_lang['area_phonespamdetect_main'] = 'PhoneSpamDetect';
$_lang['area_phonespamdetect_validation'] = 'PhoneSpamDetect — libphonenumber';

$_lang['setting_phonespamdetect_phone_field'] = 'Phone field name';
$_lang['setting_phonespamdetect_phone_field_desc'] = 'Form field name used to read the phone number. Default: phone.';

$_lang['setting_phonespamdetect_block_message'] = 'Form block message';
$_lang['setting_phonespamdetect_block_message_desc'] = 'Message shown to the user when the phone number is blocked.';

$_lang['setting_phonespamdetect_log_blocked'] = 'Log blocked submissions';
$_lang['setting_phonespamdetect_log_blocked_desc'] = 'Write blocked form submissions with rejected phone numbers to the MODX log.';

$_lang['setting_phonespamdetect_validate_enabled'] = 'Enable phone validation';
$_lang['setting_phonespamdetect_validate_enabled_desc'] = 'Validate phone numbers with Google libphonenumber locally, without external APIs.';

$_lang['setting_phonespamdetect_default_region'] = 'Default region';
$_lang['setting_phonespamdetect_default_region_desc'] = 'ISO country code for national phone numbers without a country prefix, for example RU.';

$_lang['setting_phonespamdetect_allowed_regions'] = 'Allowed regions';
$_lang['setting_phonespamdetect_allowed_regions_desc'] = 'Comma-separated ISO country codes, for example RU,KZ,BY. Empty value allows any valid number.';

$_lang['setting_phonespamdetect_block_invalid'] = 'Block invalid numbers';
$_lang['setting_phonespamdetect_block_invalid_desc'] = 'Block the form when libphonenumber marks the phone number as invalid.';
