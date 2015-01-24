<?php
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");

/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 09:40
 */
class InformationController extends AbstractController
{
    private $pageContentService;

    public function __construct()
    {
        parent::__construct();
        $this->pageContentService = new PageContentService();
    }

    public function defaultAction()
    {
        return $this->getContent("about_us");
    }

    public function policy()
    {
        return $this->getContent("policy");
    }

    public function conditions()
    {
        return $this->getContent("conditions");
    }

    public function shipping()
    {
        return $this->getContent("shipping");
    }

    private function getContent($contentKey)
    {
        $pageContent = $this->pageContentService->getPageContent(Language::getActiveLanguage(), $contentKey);

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("model", $pageContent);
        $mainTemplate->setParam("main_content", new Template("about_us.html"));
        $mainTemplate->setParam("recent_product_content", $this->getRecentProductBarTemplate(Language::getActiveLanguage(), 3));
        return $mainTemplate;
    }

} 