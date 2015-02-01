<?php

/**
 * User: Menesty
 * Date: 1/1/15
 * Time: 16:59
 */
class Utils
{
    public static function getProductImagePath($artNumber)
    {
        $path = "";

        for ($i = 1; $i < 5; $i++) {
            $path .= substr($artNumber, 0, $i) . DIRECTORY_SEPARATOR;
        }

        $imgPath = Configuration::get()->getClassPath() . "data" . DIRECTORY_SEPARATOR . "images" . DIRECTORY_SEPARATOR;

        return $imgPath . $path . $artNumber . DIRECTORY_SEPARATOR;
    }

    private static function setFieldValue($field, $object, $value)
    {
        $field = str_replace("_", "", $field);
        $method = "set" . str_replace("_", "", $field);

        if (method_exists($object, $method)) {
            $object->$method($value);
        }
    }

    public static function populateObject($entity, $rawData)
    {
        foreach ($rawData as $property => $value) {
            $value = trim($value);
            self::setFieldValue($property, $entity, $value == "" ? null : $value);
        }

        return $entity;
    }
} 