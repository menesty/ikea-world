<?php
/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 08:59
 */

class ContactController extends AbstractController{
    public function defaultAction(){
        $mainTemplate = $this->getBaseTemplate();

        $mainTemplate->setParam("main_content", new Template("contact_us.html"));

        return $mainTemplate;
    }
} 