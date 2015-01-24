<?php
include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");

/**
 * User: Menesty
 * Date: 12/28/14
 * Time: 22:29
 */
class AdminController extends AbstractAdminController
{
    private $pageContentService;

    public function __construct()
    {
        $this->pageContentService = new PageContentService();
    }

    public function defaultAction()
    {
        return new Template("admin/main.html");
    }

    /**
     * @Path({action}/{key})
     */
    public function pageContent($action = "list", $key = "")
    {
        $mainTemplate = $this->getBaseTemplate();

        if ($action == "edit" || $action == "add") {
            if ($action == "edit") {
                $model = $this->pageContentService->getAdminPageContent($key);
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
                $this->pageContentService->adminUpdate(Language::getSupported(), $this->getPost());
            }

            return new Redirect("/admin/pageContent");
        } else {

            $template = new Template("admin/page/page_content.html");
            $template->setParam("items", $this->pageContentService->getPageContentList(Language::getSupported()));
        }

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    public function categories(){
        $mainTemplate = $this->getBaseTemplate();
        $template = new Template("admin/page/categories.html");
        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }
} 