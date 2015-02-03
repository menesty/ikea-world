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

    protected function getBoolean($value) {
        if(!is_null($value) && ( (is_int($value) && $value >0)) ||  trim($value) == "on" || trim($value) == "true") {
            return true;
        }
        return false;
    }
} 