<?php

/**
 * User: Menesty
 * Date: 1/3/15
 * Time: 06:56
 */
abstract class AbstractService
{
    public function transformRow($row)
    {
        if ($row) {
            $entity = $this->newInstance();

            Utils::populateObject($entity, $row);

            return $entity;
        }
        return null;
    }

    public function transform($rawData)
    {
        $products = array();
        foreach ($rawData as $row) {
            $products[] = $this->transformRow($row);
        }
        return $products;
    }

    protected function createLangAdminQueryCheck($languages, $fields)
    {
        $queryPart = "";
        foreach ($fields as $field) {
            foreach ($languages as $lang) {
                $queryPart .= $this->getBooleanConditionByField($field . "_" . $lang) . ",";
            }
        }

        return rtrim($queryPart, ",");
    }

    protected function getBooleanConditionByField($field)
    {
        return "if(`" . $field . "` IS NULL or trim(`" . $field . "`)='' ,false, true ) as `" . $field . "`";
    }

    protected abstract function newInstance();

    protected function getBoolean($value)
    {
        if (!is_null($value) && ((is_int($value) && $value > 0)) || trim($value) == "on" || trim($value) == "true") {
            return true;
        }
        return false;
    }

    public function getSqlDateTime($value, $inputFormat)
    {
        $value = trim($value);

        if ($value != "") {
            $date = DateTime::createFromFormat($inputFormat, $value);

            return $date? $date->format('Y-m-d H:i:s') : null;
        }

        return null;
    }

    public function getFormatSqlDateTime($value, $format){
        if(!is_null($value)) {
            $value = trim($value);

            if ($value != "") {
                $date = DateTime::createFromFormat('Y-m-d H:i:s', $value);

                return $date ? $date->format($format) : null;
            }
        }

        return null;
    }
} 