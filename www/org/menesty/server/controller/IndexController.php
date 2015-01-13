<?php
/**
 * User: Menesty
 * Date: 7/3/14
 * Time: 20:26
 */

class IndexController extends AbstractController {
    public function defaultAction() {
        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", new Template("home.html"));

        return $mainTemplate;
    }
} 