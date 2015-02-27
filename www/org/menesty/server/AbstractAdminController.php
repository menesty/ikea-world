<?php
/**
 * User: Menesty
 * Date: 1/20/15
 * Time: 23:51
 */

include_once(Configuration::get()->getClassPath() . "DigestAuthentication.php");
$digestAuthentication = new DigestAuthentication();
$digestAuthentication->auth();

class AbstractAdminController extends AbstractController
{
    const ITEM_PER_PAGE = 50;

    protected function getBaseTemplate($js = array(), $css = array())
    {
        $template = new Template("admin/main.html");
        return $template;
    }

    protected function before($mainTemplate, $title)
    {
        $breadcrumb = new Breadcrumb();

        $mainTemplate->setParam("pageTitle", $title);
        $mainTemplate->setParam("breadcrumb", $breadcrumb);
        $mainTemplate->setParam("contextUrl", $this->getContextPath());

        return $breadcrumb;
    }

}

class ParamBuilder
{
    private $baseParams;

    public function __construct(array $params)
    {
        unset($params["route"]);
        $this->baseParams = $params;
    }

    public function get($key)
    {
        if (isset($this->baseParams[$key])) {
            return $this->baseParams[$key];
        }

        return "";
    }

    public function create()
    {
        $iterator = new ArrayIterator($this->baseParams);
        return new ParamBuilder($iterator->getArrayCopy());
    }

    public function append($key, $value)
    {
        $this->baseParams[$key] = $value;
        return $this;
    }

    public function toParams()
    {
        $query = "?";

        foreach ($this->baseParams as $key => $value) {
            $query .= urldecode($key) . "=" . urlencode($value) . "&";
        }

        return rtrim($query, "&");
    }
}

class Breadcrumb
{
    private $breadcrumbItems = array();

    public function append($url, $item)
    {
        $this->breadcrumbItems[$item] = $url;
    }

    public function getItems()
    {
        return $this->breadcrumbItems;
    }
}