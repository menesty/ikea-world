<?php
/**
 * User: Menesty
 * Date: 2/22/15
 * Time: 13:09
 */

include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");


class PageContentController extends AbstractAdminController
{
    public function defaultAction()
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Page Contents");
        $breadcrumb->append("/admin/pageContent", "Page Contents");

        $pageContentService = new PageContentService();

        $template = new Template("admin/page/page_content.html");
        $template->setParam("items", $pageContentService->getPageContentList(Language::getSupported()));

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({key})
     */
    public function edit($key)
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Edit Page Contents");

        $breadcrumb->append("/admin/pageContent", "Page Contents");


        $pageContentService = new PageContentService();

        $model = $pageContentService->getAdminPageContent($key);

        if (is_array($model)) {
            $template = new Template("admin/page/page_content_edit.html");
            $template->setParam("model", $model);
        } else {
            return new Redirect("/admin/pageContent");
        }

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({key})
     */
    public function add()
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Edd Page Contents");

        $breadcrumb->append("/admin/pageContent", "Page Contents");

        $template = new Template("admin/page/page_content_edit.html");
        $template->setParam("model", array());

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Method(Post)
     */
    public function update(){
        $pageContentService = new PageContentService();
        $pageContentService->adminUpdate(Language::getSupported(), $this->getPost());
        return new Redirect("/admin/pageContent");
    }


} 