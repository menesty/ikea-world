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
        $activeCategory = $this->categoryService->getByName(Language::getActiveLanguage(), $categoryId);

        $context = $this->getPageContextPath();
        $template = new Template("catalog_list.html");
        $template->setParam("pageContextUrl", $context);

        $itemsCount = $this->productService->getPublishedCount();
        $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
        $activePage = $this->getInt("page", 1, 1, $pageCount);


        $template->setParam("products", $this->productService->getPublishedRange(Language::getActiveLanguage(),
            ($activePage - 1) * self::ITEM_PER_PAGE, self::ITEM_PER_PAGE));

        $categories = new Template("content/categories.html");
        $categories->setParam("categories", $this->categoryService->getCategories(Language::getActiveLanguage()));

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