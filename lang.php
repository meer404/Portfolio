<?php
/**
 * Language Helper System
 * Handles language switching, detection, and translation
 */

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Available languages
define('AVAILABLE_LANGUAGES', ['en', 'ku']);
define('DEFAULT_LANGUAGE', 'en');
define('RTL_LANGUAGES', ['ku', 'ar', 'fa']);

/**
 * Get current language from session or URL parameter
 */
function getCurrentLanguage() {
    // Check URL parameter first
    if (isset($_GET['lang']) && in_array($_GET['lang'], AVAILABLE_LANGUAGES)) {
        $_SESSION['lang'] = $_GET['lang'];
        return $_GET['lang'];
    }
    
    // Check session
    if (isset($_SESSION['lang']) && in_array($_SESSION['lang'], AVAILABLE_LANGUAGES)) {
        return $_SESSION['lang'];
    }
    
    // Default language
    return DEFAULT_LANGUAGE;
}

/**
 * Load translations for current language
 */
function loadTranslations() {
    static $translations = null;
    
    if ($translations === null) {
        $lang = getCurrentLanguage();
        $langFile = __DIR__ . '/lang/' . $lang . '.php';
        
        if (file_exists($langFile)) {
            $translations = require $langFile;
        } else {
            // Fallback to English
            $translations = require __DIR__ . '/lang/en.php';
        }
    }
    
    return $translations;
}

/**
 * Get translation for a key
 * @param string $key Translation key (supports dot notation: 'nav.home')
 * @param array $replace Replacement values for placeholders
 * @return string Translated text or key if not found
 */
function t($key, $replace = []) {
    $translations = loadTranslations();
    
    // Support dot notation
    $keys = explode('.', $key);
    $value = $translations;
    
    foreach ($keys as $k) {
        if (isset($value[$k])) {
            $value = $value[$k];
        } else {
            return $key; // Return key if translation not found
        }
    }
    
    // Replace placeholders
    foreach ($replace as $placeholder => $replacement) {
        $value = str_replace(':' . $placeholder, $replacement, $value);
    }
    
    return $value;
}

/**
 * Check if current language is RTL
 */
function isRTL() {
    return in_array(getCurrentLanguage(), RTL_LANGUAGES);
}

/**
 * Get HTML direction attribute value
 */
function getDir() {
    return isRTL() ? 'rtl' : 'ltr';
}

/**
 * Get language name in its own script
 */
function getLanguageName($code = null) {
    $names = [
        'en' => 'English',
        'ku' => 'کوردی'
    ];
    
    $code = $code ?? getCurrentLanguage();
    return $names[$code] ?? $code;
}

/**
 * Generate language switcher URL
 */
function langUrl($lang) {
    $url = $_SERVER['REQUEST_URI'];
    $parsed = parse_url($url);
    $path = $parsed['path'] ?? '';
    
    parse_str($parsed['query'] ?? '', $query);
    $query['lang'] = $lang;
    
    return $path . '?' . http_build_query($query);
}

/**
 * Get the opposite language for switching
 */
function getOtherLanguage() {
    return getCurrentLanguage() === 'en' ? 'ku' : 'en';
}

/**
 * Get localized field value based on current language
 * Falls back to English if Kurdish translation is empty
 */
function getLocalizedField($item, $field) {
    $lang = getCurrentLanguage();
    $kuField = $field . '_ku';
    if ($lang === 'ku' && !empty($item[$kuField])) {
        return $item[$kuField];
    }
    return $item[$field] ?? '';
}

/**
 * Get localized site setting value based on current language
 * Falls back to English if Kurdish translation is empty
 */
function getLocalizedSetting($key, $default = '') {
    $db = Database::getInstance();
    $lang = getCurrentLanguage();
    
    if ($lang === 'ku') {
        $kuValue = $db->getSetting($key . '_ku');
        if (!empty($kuValue)) {
            return $kuValue;
        }
    }
    
    $value = $db->getSetting($key);
    return $value !== null ? $value : $default;
}
