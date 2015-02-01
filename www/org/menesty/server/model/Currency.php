<?php

/**
 * User: Menesty
 * Date: 2/1/15
 * Time: 11:27
 */
class Currency
{
    private $name;
    private $rate;

    public function __construct($name = "PLN", $rate = 1)
    {
        $this->name = $name;
        $this->rate = $rate;
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @param mixed $name
     */
    public function setName($name)
    {
        $this->name = $name;
    }


    /**
     * @return mixed
     */
    public function getRate()
    {
        return $this->rate;
    }

    /**
     * @param mixed $rate
     */
    public function setRate($rate)
    {
        $this->rate = $rate;
    }


} 