<?php

include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "NewsItemService.php");

/**
 * User: Menesty
 * Date: 7/3/14
 * Time: 20:26
 */
class IndexController extends AbstractController
{
    public function defaultAction()
    {
        $newsItemService = new NewsItemService();

        $mainTemplate = $this->getBaseTemplate();
        $pageContentService = new PageContentService();

        $mainTemplate->setParam("contextUrl", $this->getContextPath());

        $template = new Template("home.html");
        $template->setParam("items", $newsItemService->latest(Language::getActiveLanguage()));

        $template->setParam("content", $pageContentService->getPageContent(Language::getActiveLanguage(), "index"));


        $mainTemplate->setParam("main_content", $template);


        return $mainTemplate;
    }
} 