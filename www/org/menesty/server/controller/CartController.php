<?php

/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 09:03
 */
class CartController extends AbstractController
{
    public function defaultAction()
    {
        $mainTemplate = $this->getBaseTemplate(array("bootstrap.touchspin"));

        $mainTemplate->setParam("main_content", new Template("cart.html"));
        $mainTemplate->setParam("bestSeller_content", $this->getLeftProductBarTemplate(Language::getActiveLanguage(), 2, 'big'));
        return $mainTemplate;
    }

    public function addItem()
    {
        $count = $this->postInt("count", 1, 0);
        $id = $this->postInt("id");

        $product = $this->productService->getById(Language::getActiveLanguage(), $id);

        $result = new stdClass();
        $result->error = false;

        if (!is_null($product) && $product->getAvailable()) {
            ShoppingCart::get()->addItem($product, $count);
            $template = new Template("content/menu_shopping_cart.html");
            $template->setParam("contextUrl", $this->getContextPath());

            $result->content = $template->getContent();
        } else {
            $result->error = true;
        }

        return $result;
    }

    public function update()
    {
        $ids = $this->postArray("productId");

        $cartItems = ShoppingCart::get()->getItems();

        foreach ($cartItems as $cartItem) {
            $product = $cartItem->getProduct();

            if (in_array($product->getId(), $ids)) {
                $count = $this->postInt($product->getId() . "_" . "count", 0, 0);

                if ($count != 0) {
                    $cartItem->setCount($count);
                    continue;
                }
            }

            ShoppingCart::get()->remove($cartItem);

        }

        return new Redirect($this->getContextPath() . "cart");
    }

}