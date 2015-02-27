<?php
/**
 * User: Menesty
 * Date: 2/26/15
 * Time: 09:45
 */

include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "NewsItemService.php");

class NewsController extends AbstractAdminController
{
    public function defaultAction()
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "News");
        $breadcrumb->append("/admin/news", "News");

        $newsItemService = new NewsItemService();

        $template = new Template("admin/page/news.html");

        $itemsCount = $newsItemService->getCount($this->getGet());
        $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
        $activePage = $this->getInt("page", 1, 1, $pageCount);

        $template->setParam("items", $newsItemService->getAdminRange(Language::getSupported(),
            Language::getActiveLanguage(), ($activePage - 1) * self::ITEM_PER_PAGE, self::ITEM_PER_PAGE, $this->getGet()));

        $template->setParam("pageCount", $pageCount);
        $template->setParam("activePage", $activePage);
        $template->setParam("paramBuilder", new ParamBuilder($this->getGet()));

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({id})
     */
    public function edit($id)
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Edit");
        $breadcrumb->append("/admin/news", "News");
        $breadcrumb->append("/admin/news/edit/" . $id, "Edit");

        $template = new Template("admin/page/news_edit.html");

        $newsItemService = new NewsItemService();

        $model = $newsItemService->getAdminNews($id);

        $model["published_date"] = $newsItemService->getFormatSqlDateTime($model["published_date"], 'd/m/Y H:i');

        $template->setParam("model", $model);

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }


    public function add()
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Add");
        $breadcrumb->append("/admin/news", "News");
        $breadcrumb->append("/admin/news/add/", "Add");

        $template = new Template("admin/page/news_edit.html");

        $template->setParam("model", array());

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Method(POST)
     * @Path({id})
     */
    public function update($id = null)
    {
        $newsItemService = new NewsItemService();

        $newsItemService->adminUpdate(Language::getSupported(), $id, $this->getPost());

        return new Redirect("/admin/news");
    }
} 