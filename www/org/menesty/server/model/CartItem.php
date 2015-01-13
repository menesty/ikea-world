<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Product.php");

/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 14:41
 */
class CartItem
{
    private $count;
    private $product;

    public function __construct(Product $product, $count)
    {
        $this->product = $product;
        $this->count = $count;
    }

    public function getTotalPrice()
    {
        return $this->count * $this->product->getSellPrice();
    }

    public function getCount()
    {
        return $this->count;
    }

    public function setCount($count)
    {
        $this->count = $count;
    }

    public function getTitle()
    {
        return $this->product->getTitle();
    }

    public function getProduct()
    {
        return $this->product;
    }

    public function getArtNumber()
    {
        return $this->product->getArtNumber();
    }

    public function increaseCount($count){
        $this->count += $count;
    }
}