<?php
/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 14:40
 */
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "CartItem.php");
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Currency.php");
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "ClientOrderInfo.php");

class ShoppingCart
{
    private static $instance;
    private static $clientOrderInfo;

    private $items = array();
    private $currency;

    public function __construct()
    {
        $this->currency = new Currency();
    }

    public function addItem(Product $product, $count)
    {
        $exist = false;

        foreach ($this->items as $item) {
            if ($item->getProduct()->getId() == $product->getId()) {
                $exist = true;
                $item->increaseCount($count);
                break;
            }
        }

        if (!$exist) {
            $this->items[] = new CartItem($product, $count);
        }

        self::update($this);
    }

    /**
     * @return mixed
     */
    public function getCurrency()
    {
        return $this->currency;
    }

    /**
     * @param mixed $currency
     */
    public function setCurrency($currency)
    {
        $this->currency = $currency;
    }


    private static function update(ShoppingCart $cart)
    {
        $_SESSION["shopping_cart"] = $cart;
    }

    public static function init()
    {
        if (!isset($_SESSION["shopping_cart"])) {
            $_SESSION["shopping_cart"] = new ShoppingCart();
        }

        if (!isset($_SESSION['client_order_info'])) {
            $_SESSION['client_order_info'] = new ClientOrderInfo();
        }

        self::$clientOrderInfo = & $_SESSION['client_order_info'];
        self::$instance = & $_SESSION["shopping_cart"];
    }

    /**
     * @return ShoppingCart
     */
    public static function get()
    {
        return self::$instance;
    }

    public function remove(CartItem $cartItem)
    {
        $key = array_search($cartItem, $this->items);
        unset($this->items[$key]);
    }

    public function getTotalPrice()
    {
        $price = 0;

        foreach ($this->items as $item) {
            $price += $item->getTotalPrice();
        }

        return $price;
    }

    public function getTotalCount()
    {
        $count = 0;

        foreach ($this->items as $item) {
            $count += $item->getCount();
        }

        return $count;
    }

    public function getItems()
    {
        return $this->items;
    }

    public static function isEmpty()
    {
        return sizeof(self::get()->getItems()) == 0;
    }

    /**
     * @return ClientOrderInfo
     */
    public static function getClientInfo()
    {
        return self::$clientOrderInfo;
    }
} 