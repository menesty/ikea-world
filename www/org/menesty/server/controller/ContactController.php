<?php
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "ContactRequestService.php");


/**
 * User: Menesty
 * Date: 1/9/15
 * Time: 08:59
 */
class ContactController extends AbstractController
{
    public function defaultAction($errors = array())
    {
        $pageContentService = new PageContentService();

        $mainTemplate = $this->getBaseTemplate();

        $template = new Template("contact_us.html");
        $template->setParam("errors", $errors);
        $template->setParam("content", $pageContentService->getPageContent(Language::getActiveLanguage(), "contact_us"));
        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }


    /**
     * @Method(POST)
     */
    public function send()
    {
        $contactRequestService = new ContactRequestService();
        $contactRequest = $contactRequestService->transformRow($this->getPost());

        if (is_array($errors = $contactRequest->isValid())) {
            $this->defaultAction($errors);
            return;
        }

        $contactRequestService->save($contactRequest);
        return new Redirect($this->getContextPath() . "contact/thanks");
    }

    public function thanks()
    {
        $pageContentService = new PageContentService();
        $mainTemplate = $this->getBaseTemplate();

        $template = new Template("contact_us.html");
        $template->setParam("content", $pageContentService->getPageContent(Language::getActiveLanguage(), "contact_us_thanks"));
        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }
} 