<?php

/**
 * User: Menesty
 * Date: 2/27/15
 * Time: 21:03
 */
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "NewsItemService.php");
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Paging.php");

class NewsController extends AbstractController
{
    const ITEM_PER_PAGE = 5;

    public function defaultAction()
    {
        $newsItemService = new NewsItemService();

        $context = $this->getPageContextPath();

        $template = new Template("content/news.html");

        $itemsCount = $newsItemService->getPublishedCount(Language::getActiveLanguage());

        $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
        $activePage = $this->getInt("page", 1, 1, $pageCount);
        $offset = ($activePage - 1) * self::ITEM_PER_PAGE;

        $template->setParam("items", $newsItemService->getPublishedRange(Language::getActiveLanguage(),
            self::ITEM_PER_PAGE, $offset));


        $pagingTemplate = new Template("content/paging.html");

        $pagingUrl = $context;

        $pagingTemplate->setParam("paging", new Paging($pageCount, $activePage));
        $pagingTemplate->setParam("pagingUrl", $pagingUrl);


        $template->setParam("paging_content", $pagingTemplate);


        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", $template);
        $mainTemplate->setParam("bestSeller_content", $this->getLeftProductBarTemplate(Language::getActiveLanguage(), 2, 'big'));

        return $mainTemplate;
    }

    /**
     * @Path({id})
     */
    public function details($id = null)
    {
        $newsItemService = new NewsItemService();

        $context = $this->getPageContextPath();

        $template = new Template("content/news_details.html");

        $template->setParam("model", $newsItemService->getById(Language::getActiveLanguage(), $id));

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", $template);
        $mainTemplate->setParam("bestSeller_content", $this->getLeftProductBarTemplate(Language::getActiveLanguage(), 2, 'big'));

        return $mainTemplate;
    }
} 