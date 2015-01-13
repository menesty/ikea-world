<?php

/**
 * User: Menesty
 * Date: 1/1/15
 * Time: 16:54
 */
class ProductImage
{
    private $index;
    private $artNumber;

    public function __construct($artNumber, $index)
    {
        $this->index = $index;
        $this->artNumber = $artNumber;
    }

    public function getNormal()
    {
        //$artPath = Utils::getProductImagePath($this->artNumber);
        //$path = $artPath . "normal" . DIRECTORY_SEPARATOR . $this->artNumber . "_" . $this->index . ".jpg";

        return "/image/normal/" . $this->artNumber . ($this->index != 0 ? ("/" . $this->index) : "");

    }

    public function getZoom()
    {
        $artPath = Utils::getProductImagePath($this->artNumber);
        $path = $artPath . "zoom" . DIRECTORY_SEPARATOR . $this->artNumber . "_" . $this->index . ".jpg";

        if(!file_exists($path)){
            return $this->getNormal();
        }

        return "/image/zoom/" . $this->artNumber . ($this->index != 0 ? ("/" . $this->index) : "");;
    }
} 