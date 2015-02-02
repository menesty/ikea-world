<?php

include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");

/**
 * User: Menesty
 * Date: 7/3/14
 * Time: 20:26
 */
class IndexController extends AbstractController
{
    public function defaultAction()
    {
        $mainTemplate = $this->getBaseTemplate();
        $pageContentService = new PageContentService();
        $template = new Template("home.html");
        $template->setParam("content", $pageContentService->getPageContent(Language::getActiveLanguage(), "index"));
        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }
} 