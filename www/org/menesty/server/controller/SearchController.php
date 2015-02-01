<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Paging.php");

/**
 * User: Menesty
 * Date: 2/1/15
 * Time: 12:19
 */
class SearchController extends AbstractController
{
    const ITEM_PER_PAGE = 20;

    /**
     * @Path({searchKeyword})
     */
    public function defaultAction($searchKeyword = "")
    {
        $categoryService = new CategoryService();

        $searchKeyword = preg_replace("/\s+/", " ", $searchKeyword);
        $parts = preg_split("/\s/", $searchKeyword);

        $mainTemplate = $this->getBaseTemplate();
        $categories = new Template("content/categories.html");
        $categories->setParam("categories", $categoryService->getCategories(Language::getActiveLanguage()));
        $categories->setParam("pageContextUrl", $this->getContextPath()."catalog/");
        $itemsCount = $this->productService->getSearchPublishedCount(Language::getActiveLanguage(), $parts);

        if (sizeof($parts) == 0 || $itemsCount == 0) {
            $template = new Template("content/search_no_result.html");
        } else {
            $context = $this->getPageContextPath() . $searchKeyword;

            $template = new Template("content/search_result.html");



            $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
            $activePage = $this->getInt("page", 1, 1, $pageCount);
            $offset = ($activePage - 1) * self::ITEM_PER_PAGE;

            $template->setParam("products", $this->productService->getSearchPublishedRange(Language::getActiveLanguage(),
                $parts, $offset, self::ITEM_PER_PAGE));


            $pagingTemplate = new Template("content/paging.html");

            $pagingUrl = $context;

            $pagingTemplate->setParam("paging", new Paging($pageCount, $activePage));
            $pagingTemplate->setParam("pagingUrl", $pagingUrl);


            $template->setParam("paging_content", $pagingTemplate);

        }

        $template->setParam("category_content", $categories);
        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }
} 