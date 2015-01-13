<?php

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
        $mainTemplate = $this->getBaseTemplate(array('jqBootstrapValidation','jquery.blImageCenter'));

        $mainTemplate->setParam("main_content", new Template("checkout.html"));
        $mainTemplate->setParam("clientInfo", ShoppingCart::getClientInfo());

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
} 