<?php

/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 09:40
 */
class InformationController extends AbstractController
{
    public function defaultAction(){
        $mainTemplate = $this->getBaseTemplate();

        $mainTemplate->setParam("main_content", new Template("about_us.html"));

        return $mainTemplate;
    }

    public function policy(){
        $mainTemplate = $this->getBaseTemplate();

        $mainTemplate->setParam("main_content", new Template("about_us.html"));

        return $mainTemplate;
    }

    public function conditions(){
        $mainTemplate = $this->getBaseTemplate();

        $mainTemplate->setParam("main_content", new Template("about_us.html"));

        return $mainTemplate;
    }

    public function shipping(){
        $mainTemplate = $this->getBaseTemplate();

        $mainTemplate->setParam("main_content", new Template("about_us.html"));

        return $mainTemplate;
    }

} 