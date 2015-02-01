<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "OrderItem.php");
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "CustomerOrder.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "ClientOrderInfoService.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "CustomerOrderService.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "OrderItemService.php");

/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 09:05
 */
class CheckoutController extends AbstractController
{
    /**
     * @return Template
     */
    public function defaultAction()
    {
        $mainTemplate = $this->getBaseTemplate(array('jquery.validate', 'jquery.blImageCenter'));
        $mainTemplate->setParam("main_content", new Template("checkout.html"));
        $mainTemplate->setParam("clientInfo", ShoppingCart::getClientInfo());
        $mainTemplate->setParam("recent_product_content", $this->getRecentProductBarTemplate(Language::getActiveLanguage(), 3));

        return $mainTemplate;
    }


    /**
     * @Method(POST)
     */
    public function next()
    {
        Utils::populateObject(ShoppingCart::getClientInfo(), $_POST);

        if (!($result = ShoppingCart::getClientInfo()->isValid())) {
            $mainTemplate = $this->defaultAction();
            $mainTemplate->setParam("errors", $result);

            return $mainTemplate;
        }

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", new Template("checkout_confirm.html"));
        $mainTemplate->setParam("clientInfo", ShoppingCart::getClientInfo());

        return $mainTemplate;
    }

    public function confirm()
    {
        if (!ShoppingCart::get()->getClientInfo()->isValid() || ShoppingCart::get()->isEmpty()) {
            return new Redirect("/checkout");
        }

        $currency = ShoppingCart::get()->getCurrency();

        $clientOrderInfoService = new ClientOrderInfoService();
        $customerOrderService = new CustomerOrderService();
        $orderItemService = new OrderItemService();

        $clientId = $clientOrderInfoService->save(ShoppingCart::get()->getClientInfo());


        $customerOrder = new CustomerOrder();
        $customerOrder->setClientId($clientId);
        $customerOrder->setCurrency($currency->getName());
        $customerOrder->setRate($currency->getRate());
        $customerOrder->setTotalPrice(ShoppingCart::get()->getTotalPrice());
        $customerOrder->setComment(ShoppingCart::get()->getClientInfo()->getComment());

        $orderId = $customerOrderService->save($customerOrder);

        foreach (ShoppingCart::get()->getItems() as $item) {
            $orderItem = new OrderItem();
            $orderItem->setOrderId($orderId);
            $orderItem->setCount($item->getCount());
            $orderItem->setPrice($item->getProduct()->getSellPrice());
            $orderItem->setProductId($item->getProduct()->getId());
            $orderItemService->save($orderItem);
        }

        ShoppingCart::get()->clear();

        $categoryService = new CategoryService();
        $categories = new Template("content/categories.html");
        $categories->setParam("categories", $categoryService->getCategories(Language::getActiveLanguage()));
        $categories->setParam("pageContextUrl", $this->getContextPath() . "catalog/");

        $template = new Template("content/checkout_thanks.html");
        $template->setParam("category_content", $categories);
        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", $template);
        $mainTemplate->setParam("clientInfo", ShoppingCart::getClientInfo());


        return $mainTemplate;
    }
}