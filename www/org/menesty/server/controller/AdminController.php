<?php
include_once(Configuration::get()->getClassPath() . "DigestAuthentication.php");
$digestAuthentication = new DigestAuthentication();
$digestAuthentication->auth();

include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "CategoryService.php");


/**
 * User: Menesty
 * Date: 12/28/14
 * Time: 22:29
 */
class AdminController extends AbstractAdminController
{
    const ITEM_PER_PAGE = 50;

    public function defaultAction()
    {
        return new Template("admin/main.html");
    }

    /**
     * @Path({action}/{key})
     */
    public function pageContent($action = "list", $key = "")
    {
        $pageContentService = new PageContentService();
        $mainTemplate = $this->getBaseTemplate();

        if ($action == "edit" || $action == "add") {
            if ($action == "edit") {
                $model = $pageContentService->getAdminPageContent($key);
            } else {
                $model = array();
            }

            if (is_array($model)) {
                $template = new Template("admin/page/page_content_edit.html");
                $template->setParam("model", $model);
            } else {
                return new Redirect("/admin/pageContent");
            }

        } elseif ($action == "update") {
            if ($this->isPost()) {
                $pageContentService->adminUpdate(Language::getSupported(), $this->getPost());
            }

            return new Redirect("/admin/pageContent");
        } else {

            $template = new Template("admin/page/page_content.html");
            $template->setParam("items", $pageContentService->getPageContentList(Language::getSupported()));
        }

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({action}/{id})
     */
    public function categories($action = "view", $id = null)
    {
        $categoryService = new CategoryService();

        if ($action == "view") {
            $template = new Template("admin/page/categories.html");
            $categories = $categoryService->getCategoriesTree(Language::getActiveLanguage());

            if (!is_null($id) && !$categoryService->isValid($id)) {
                return new Redirect("/admin/categories");
            }

            $items = $categoryService->getAdminCategories(Language::getActiveLanguage(), Language::getSupported(), $id);

            $template->setParam("categories", $categories);
            $template->setParam("items", $items);
            $template->setParam("parent_id", $id);

            $template->setParam("allowSubCategories", !$categoryService->isThirdLevel($id));
        } elseif ($action == "edit" && $categoryService->isValid($id)) {
            if ($categoryService->isThirdLevel($id)) {
                return new Redirect("/admin/categories/view/" . id);
            }

            $template = new Template("admin/page/category_edit.html");
            $model = $categoryService->getAdminCategory($id);
            $template->setParam("model", $model);
        } elseif ($action == "add" && ((!is_null($id) && $categoryService->isValid($id)) || is_null($id))) {
            $template = new Template("admin/page/category_edit.html");
            $model = array("parent_id" => is_null($id) ? "" : (int)$id);
            $template->setParam("model", $model);
        } elseif ($action == "update" && $this->isPost()) {
            $postData = $this->getPost();

            if ($categoryService->isThirdLevel($postData["parent_id"])) {
                return new Redirect("/admin/categories/view/" . id);
            }

            $parentId = (int)$categoryService->adminUpdate(Language::getSupported(), $id, $postData);

            if ($parentId != 0) {
                return new Redirect("/admin/categories/view/" . $parentId);
            }

            return new Redirect("/admin/categories");
        } else {
            return new Redirect("/admin/categories");
        }

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({action}/{id})
     */
    public function products($action = "view", $id = null)
    {

        if ($action == "view") {
            $template = new Template("admin/page/products.html");

            $itemsCount = $this->productService->getCount();
            $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
            $activePage = $this->getInt("page", 1, 1, $pageCount);

            $template->setParam("products", $this->productService->getAdminRange(Language::getSupported(),
                ($activePage - 1) * self::ITEM_PER_PAGE, self::ITEM_PER_PAGE));

            $template->setParam("pageCount", $pageCount);
            $template->setParam("activePage", $activePage);

        } elseif ($action == "edit" || $action == "add") {
            $template = new Template("admin/page/product_edit.html");
            $categoryService = new CategoryService();
            $template->setParam("categories", $categoryService->getCategoriesTree(Language::getActiveLanguage()));
            $template->setParam("activeCategories", $this->productService->getProductCategories($id));

            if (!is_null($id)) {
                $template->setParam("model", $this->productService->getAdminProduct($id));
            } else {
                $template->setParam("model", array());
            }
        } elseif ($action == "update" && $this->isPost()) {
            $this->productService->adminUpdate(Language::getSupported(), $id, $this->getPost());

            return new Redirect("/admin/products");
        } else {
            return new Redirect("/admin/products");
        }


        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

} 