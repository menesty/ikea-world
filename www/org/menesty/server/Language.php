<?php

/**
 * User: Menesty
 * Date: 1/6/15
 * Time: 23:38
 */
class Language
{
    private static $activeLanguage;
    private static $supported = array("ru", "by", "ua");

    private static $defaultLanguage = "ru";
    private static $languageMessages;

    public static function setActiveLanguage(array &$pathParams)
    {
        self::initActiveLang($pathParams);

        self::$languageMessages = parse_ini_file(Configuration::get()->getLanguageMessagePath() . "labels_" . Language::$activeLanguage . ".ini", true);
    }

    private static function initActiveLang(array &$pathParams)
    {
        if (sizeof($pathParams) > 0) {
            $lang = trim(strtolower($pathParams[0]));

            if (in_array($lang, Language::$supported)) {
                Language::$activeLanguage = $lang;
                array_shift($pathParams);
                return;
            }

        }

        self::$activeLanguage = self::$defaultLanguage;
    }

    public static function getSupported()
    {
        return self::$supported;
    }

    public static function getActiveLanguage()
    {
        return self::$activeLanguage;
    }

    public static function getMessage($section, $key)
    {
        return self::$languageMessages[$section][$key];
    }

    public static function getMenuLabel($key)
    {
        return self::getMessage("menu", $key);
    }

    public static function getCheckoutLabel($key)
    {
        return self::getMessage("checkout", $key);
    }

    public static function getCheckoutErrorLabel($key, $fieldKey = "", $params = array())
    {
        $error = self::getMessage("checkout-error", $key);

        if ($fieldKey != "") {
            array_unshift($params, self::getCheckoutLabel($fieldKey));
        }

        return vsprintf($error, $params);
    }

    public static function getPagingLabel($key)
    {
        return self::getMessage("paging", $key);
    }

    public static function getMainLabel($key)
    {
        return self::getMessage("main", $key);
    }

    public static function getCartLabel($key)
    {
        return self::getMessage("cart", $key);
    }

    public static function getFooterLabel($key)
    {
        return self::getMessage("footer", $key);
    }

    public static function getProductLabel($key)
    {
        return self::getMessage("product", $key);
    }

    public static function getNewsLabel($key)
    {
        return self::getMessage("news", $key);
    }
}