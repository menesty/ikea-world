<?php

include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "ContactRequestService.php");

/**
 * User: Menesty
 * Date: 2/22/15
 * Time: 13:03
 */
class ContactRequestController extends AbstractAdminController
{
    public function defaultAction()
    {
        $breadcrumb = new Breadcrumb();
        $breadcrumb->append("/admin/contactRequest", "Client Requests");

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("pageTitle", "Client Requests");
        $mainTemplate->setParam("breadcrumb", $breadcrumb);

        $contactRequestService = new ContactRequestService();

        $template = new Template("admin/page/contact_request.html");

        $itemsCount = $contactRequestService->getCount($this->getGet());
        $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
        $activePage = $this->getInt("page", 1, 1, $pageCount);

        $template->setParam("pageCount", $pageCount);
        $template->setParam("activePage", $activePage);


        $template->setParam("items", $contactRequestService->getList(($activePage - 1) * self::ITEM_PER_PAGE, self::ITEM_PER_PAGE));
        $template->setParam("paramBuilder", new ParamBuilder($this->getGet()));

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

} 