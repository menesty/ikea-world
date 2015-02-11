<?php
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "CategoryService.php");

include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Paging.php");

/**
 * User: Menesty
 * Date: 12/30/14
 * Time: 07:00
 */
class CatalogController extends AbstractController
{
    private $categoryService;

    const ITEM_PER_PAGE = 20;

    public function __construct()
    {
        parent::__construct();
        $this->categoryService = new CategoryService();
    }

    /**
     * @Path({categoryId})
     */
    public function defaultAction($categoryId = "")
    {
        $activeCategory = $this->categoryService->getById(Language::getActiveLanguage(), $categoryId);
        $activeCategoryId = !is_null($activeCategory) ? $activeCategory->getId() : null;

        $context = $this->getPageContextPath();
        $template = new Template("catalog_list.html");
        $template->setParam("pageContextUrl", $context);

        $itemsCount = $this->productService->getPublishedCount($activeCategoryId);
        $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
        $activePage = $this->getInt("page", 1, 1, $pageCount);
        $offset = ($activePage - 1) * self::ITEM_PER_PAGE;

        $listCategories = array();
        $lastCategories = array();
        $parentsCategories = array();
        if (!is_null($activeCategory)) {
            $parentsCategories = $this->categoryService->getParents(Language::getActiveLanguage(), $activeCategory);

            if (sizeof($parentsCategories) > 1) {
                /*
                 * [current] 2
                 * [parent ] 1
                 */
                $listCategories = $this->categoryService->getChilds(Language::getActiveLanguage(), $parentsCategories[1]->getId());

                if (sizeof($parentsCategories) > 2) {
                    $lastCategories = $this->categoryService->getChilds(Language::getActiveLanguage(), $parentsCategories[2]->getId());
                }


            }
        }

        $template->setParam("parents_categories", $parentsCategories);
        $template->setParam("list_categories", $listCategories);
        $template->setParam("last_list_categories", $lastCategories);
        $template->setParam("products", $this->productService->getPublishedRange(Language::getActiveLanguage(),
            $activeCategoryId, $offset, self::ITEM_PER_PAGE));

        $categories = new Template("content/categories.html");
        $categories->setParam("categories", $this->categoryService->getCategories(Language::getActiveLanguage(), $activeCategory));

        $pagingTemplate = new Template("content/paging.html");

        $pagingUrl = !is_null($activeCategory) ? ($context . $activeCategory->getName() . "/") : $context;


        $pagingTemplate->setParam("paging", new Paging($pageCount, $activePage));
        $pagingTemplate->setParam("pagingUrl", $pagingUrl);

        $template->setParam("category_content", $categories);
        $template->setParam("paging_content", $pagingTemplate);
        $template->setParam("bestSeller_content", $this->getLeftProductBarTemplate(Language::getActiveLanguage(), 3));


        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", $template);
        return $mainTemplate;
    }

    /**
     * @Path({id})
     */
    public function detail($id)
    {

        $product = $this->productService->getProductByArtNumber(Language::getActiveLanguage(), $id);
        $template = new Template("product_detail.html");
        $template->setParam("product", $product);
        $template->setParam("bestSeller_content", $this->getLeftProductBarTemplate(Language::getActiveLanguage(), 2, 'big'));

        $mainTemplate = $this->getBaseTemplate(array("jquery.blImageCenter", "bootstrap.touchspin", "smoothproducts"),
            array("smoothproducts"));
        $mainTemplate->setParam("main_content", $template);
        return $mainTemplate;
    }

}