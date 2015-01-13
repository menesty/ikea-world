<?php

/**
 * User: Menesty
 * Date: 1/3/15
 * Time: 06:56
 */
abstract class AbstractService
{
    protected function transformRow($row)
    {
        if ($row) {
            $entity = $this->newInstance();

            Utils::populateObject($entity, $row);

            return $entity;
        }
        return null;
    }

    protected function transform($rawData)
    {
        $products = array();
        foreach ($rawData as $row) {
            $products[] = $this->transformRow($row);
        }
        return $products;
    }

    protected abstract function newInstance();
} 