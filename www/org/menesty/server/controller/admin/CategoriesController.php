<?php
/**
 * User: Menesty
 * Date: 2/22/15
 * Time: 13:24
 */

include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "CategoryService.php");

class CategoriesController extends AbstractAdminController
{
    /**
     * @Path({id})
     */
    public function defaultAction($id = null)
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Categories");
        $breadcrumb->append("/admin/categories", "Categories");

        $categoryService = new CategoryService();

        $template = new Template("admin/page/categories.html");

        if (!is_null($id) && !$categoryService->isValid($id)) {
            return new Redirect("/admin/categories");
        }

        $categories = $categoryService->getCategoriesTree(Language::getActiveLanguage(), $id);

        $items = $categoryService->getAdminCategories(Language::getActiveLanguage(), Language::getSupported(), $id);

        $template->setParam("activeCategoryName", "");

        if (!is_null($id)) {
            $activeCategory = $categoryService->getById(Language::getActiveLanguage(), $id);
            $template->setParam("activeCategoryName", $activeCategory->getName());

            $parents = $categoryService->getParents(Language::getActiveLanguage(), $activeCategory);

            foreach ($parents as $parent) {
                $breadcrumb->append("/admin/categories/view/" . $parent->getId(), $parent->getName());
            }
        }

        $template->setParam("activeCategoryId", $id);
        $template->setParam("categories", $categories);
        $template->setParam("items", $items);
        $template->setParam("parent_id", $id);

        $template->setParam("allowSubCategories", !$categoryService->isFourthLevel($id));


        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({id})
     */
    public function edit($id)
    {
        $categoryService = new CategoryService();

        if (!$categoryService->isValid($id)) {
            return new Redirect("/admin/categories");
        }

        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Edit Category");
        $breadcrumb->append("/admin/categories", "Categories");


        $template = new Template("admin/page/category_edit.html");
        $model = $categoryService->getAdminCategory($id);
        $template->setParam("model", $model);

        $activeCategory = $categoryService->getById(Language::getActiveLanguage(), $id);
        $parents = $categoryService->getParents(Language::getActiveLanguage(), $activeCategory);
        array_pop($parents);

        foreach ($parents as $parent) {
            $breadcrumb->append("/admin/categories/" . $parent->getId(), $parent->getName());
        }

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;

    }

    /**
     * @Path({id})
     */
    public function add($id = null)
    {
        $categoryService = new CategoryService();

        if (!((!is_null($id) && $categoryService->isValid($id)) || is_null($id))) {
            return new Redirect("/admin/categories");
        }

        if ($categoryService->isFourthLevel($id)) {
            return new Redirect("/admin/categories/" . id);
        }

        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Add Category");
        $breadcrumb->append("/admin/categories", "Categories");

        $template = new Template("admin/page/category_edit.html");
        $model = array("parent_id" => is_null($id) ? "" : (int)$id);
        $template->setParam("model", $model);

        $activeCategory = $categoryService->getById(Language::getActiveLanguage(), $id);
        $parents = $categoryService->getParents(Language::getActiveLanguage(), $activeCategory);

        foreach ($parents as $parent) {
            $breadcrumb->append("/admin/categories/" . $parent->getId(), $parent->getName());
        }

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Method(POST)
     * @Path({id})
     */
    public function update($id)
    {
        $categoryService = new CategoryService();
        $postData = $this->getPost();

        if ($categoryService->isFourthLevel($postData["parent_id"])) {
            return new Redirect("/admin/categories/" . $id);
        }

        $parentId = (int)$categoryService->adminUpdate(Language::getSupported(), $id, $postData);

        if ($parentId != 0) {
            return new Redirect("/admin/categories/" . $parentId);
        }

        return new Redirect("/admin/categories");
    }

}