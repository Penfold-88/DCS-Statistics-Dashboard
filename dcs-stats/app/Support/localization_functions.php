<?php

use DcsStats\Core\Localization;

function dcs_builtin_languages() {
    return Localization::builtInLanguages();
}

function dcs_custom_language_registry_path() {
    return Localization::customLanguageRegistryPath();
}

function dcs_custom_language_dir() {
    return Localization::customLanguageDir();
}

function dcs_normalize_language_code($language) {
    return Localization::normalizeCode($language);
}

function dcs_custom_languages() {
    return Localization::customLanguages();
}

function dcs_supported_languages() {
    return Localization::supportedLanguages();
}

function dcs_language_code($language = null) {
    return Localization::languageCode($language);
}

function dcs_default_language() {
    return Localization::defaultLanguage();
}

function dcs_site_config() {
    return Localization::siteConfig();
}

function dcs_date_format_options() {
    return Localization::dateFormatOptions();
}

function dcs_public_date_format() {
    return Localization::publicDateFormat();
}

function dcs_set_language_override($language) {
    Localization::setLanguageOverride($language);
}

function dcs_load_translations($language) {
    return Localization::loadTranslations($language);
}

function dcs_t($key, $replace = []) {
    return Localization::translate((string)$key, is_array($replace) ? $replace : []);
}

function dcs_language_name($language = null) {
    return Localization::languageName($language);
}
