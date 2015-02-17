<?php
include_once(Configuration::get()->getClassPath() . "DigestAuthentication.php");
$digestAuthentication = new DigestAuthentication();
$digestAuthentication->auth();

include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "CategoryService.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "ContactRequestService.php");


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

    public function contactRequest(){
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

    /**
     * @Path({action}/{key})
     */
    public function pageContent($action = "list", $key = "")
    {

        $breadcrumb = new Breadcrumb();
        $breadcrumb->append("/admin/pageContent", "Page Contents");

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("pageTitle", "Page Contents");
        $mainTemplate->setParam("breadcrumb", $breadcrumb);

        $pageContentService = new PageContentService();


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
        $breadcrumb = new Breadcrumb();
        $breadcrumb->append("/admin/categories", "Categories");

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("pageTitle", "Categories");
        $mainTemplate->setParam("breadcrumb", $breadcrumb);

        $categoryService = new CategoryService();

        if ($action == "view") {
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
        } elseif ($action == "edit" && $categoryService->isValid($id)) {
            $template = new Template("admin/page/category_edit.html");
            $model = $categoryService->getAdminCategory($id);
            $template->setParam("model", $model);

            $activeCategory = $categoryService->getById(Language::getActiveLanguage(), $id);
            $parents = $categoryService->getParents(Language::getActiveLanguage(), $activeCategory);
            array_pop($parents);

            foreach ($parents as $parent) {
                $breadcrumb->append("/admin/categories/view/" . $parent->getId(), $parent->getName());
            }

        } elseif ($action == "add" && ((!is_null($id) && $categoryService->isValid($id)) || is_null($id))) {
            if ($categoryService->isFourthLevel($id)) {
                return new Redirect("/admin/categories/view/" . id);
            }

            $template = new Template("admin/page/category_edit.html");
            $model = array("parent_id" => is_null($id) ? "" : (int)$id);
            $template->setParam("model", $model);

            $activeCategory = $categoryService->getById(Language::getActiveLanguage(), $id);
            $parents = $categoryService->getParents(Language::getActiveLanguage(), $activeCategory);

            foreach ($parents as $parent) {
                $breadcrumb->append("/admin/categories/view/" . $parent->getId(), $parent->getName());
            }

        } elseif ($action == "update" && $this->isPost()) {
            $postData = $this->getPost();

            if ($categoryService->isFourthLevel($postData["parent_id"])) {
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

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({action}/{id})
     */
    public function products($action = "view", $id = null)
    {
        $breadcrumb = new Breadcrumb();
        $breadcrumb->append("/admin/products", "Products");

        $mainTemplate = $this->getBaseTemplate();
        $mainTemplate->setParam("pageTitle", "Products");
        $mainTemplate->setParam("breadcrumb", $breadcrumb);


        if ($action == "view") {
            $template = new Template("admin/page/products.html");

            $itemsCount = $this->productService->getCount($this->getGet());
            $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
            $activePage = $this->getInt("page", 1, 1, $pageCount);

            $template->setParam("products", $this->productService->getAdminRange(Language::getSupported(),
                ($activePage - 1) * self::ITEM_PER_PAGE, self::ITEM_PER_PAGE, $this->getGet()));

            $template->setParam("pageCount", $pageCount);
            $template->setParam("activePage", $activePage);
            $template->setParam("paramBuilder", new ParamBuilder($this->getGet()));
        } elseif ($action == "edit" || $action == "add") {
            $template = new Template("admin/page/product_edit.html");

            if (!is_null($id)) {
                $template->setParam("model", $this->productService->getAdminProduct($id));
                $breadcrumb->append("/admin/products/edit/" . $id, "Edit");
            } else {
                $template->setParam("model", array());
                $breadcrumb->append("/admin/products/add", "Add");
            }
        } elseif ($action == "update" && $this->isPost()) {
            $this->productService->adminUpdate(Language::getSupported(), $id, $this->getPost());

            return new Redirect("/admin/products");
        } else {
            return new Redirect("/admin/products");
        }


        $mainTemplate->setParam("main_content", $template);
        $mainTemplate->setParam("contextUrl", $this->getContextPath());

        return $mainTemplate;
    }


    /**
     * @Path({artNumber})
     */
    public function downloadPhotos($artNumber)
    {
        $product = $this->productService->getProductByArtNumber(Language::getActiveLanguage(), $artNumber);

        if (!is_null($product))
            $url = "http://www.ikea.com/pl/pl/catalog/products/" . $product->getArtNumber() . "/";
        $content = $this->downloadContent($url);

        if ($content) {
            preg_match("/var jProductData = ({.*?});/", $content, $matches);
            $data = json_decode($matches[1]);
            $items = $data->product->items;
            $itemData = null;

            foreach ($items as $item) {
                if ($item->partNumber == $artNumber) {
                    $itemData = $item;
                    break;
                }
            }

            $images = $itemData->images;

            $path = $this->createFolderStructure($artNumber);

            $failedDownloadCount = 0;
            $failedDownloadCount += $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "large" . DIRECTORY_SEPARATOR), $images->large);
            $failedDownloadCount += $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "normal" . DIRECTORY_SEPARATOR), $images->normal);
            $failedDownloadCount += $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "zoom" . DIRECTORY_SEPARATOR), $images->zoom);
            $failedDownloadCount += $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR), $images->thumb);
            $failedDownloadCount += $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "small" . DIRECTORY_SEPARATOR), $images->small);

            return $failedDownloadCount;
        }
    }

    private function downloadContent($url)
    {
        $agent = 'Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1; SV1; .NET CLR 1.0.3705; .NET CLR 1.1.4322)';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_VERBOSE, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, $agent);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_URL, $url);

        $result = curl_exec($ch);

        $http_code = curl_getinfo($ch);

        if ($http_code["http_code"] == "200") {
            return $result;
        }

        return false;
    }

    private function downloadImages($artNumber, $folderPath, $imgList)
    {
        $failDownload = 0;

        for ($i = 0; $i < sizeof($imgList); $i++) {
            $imgUrl = "http://www.ikea.com" . $imgList[$i];
            $imgName = strtolower($folderPath) . $artNumber . "_" . $i . ".jpg";

            if (!file_exists($imgName)) {
                $content = $this->downloadContent($imgUrl);

                if (!$content) {
                    $failDownload++;
                } else {
                    file_put_contents($imgName, $content);
                }
            }

        }

        return $failDownload;
    }

    private function createFolderStructure($artNumber)
    {
        $artPath = Utils::getProductImagePath($artNumber);

        if (!file_exists($artPath)) {
            mkdir($artPath, 0777, true);
        }

        $subFolders = array("large", "normal", "zoom", "thumb", "small");

        foreach ($subFolders as $subFolder) {
            $path = $artPath . DIRECTORY_SEPARATOR . $subFolder . DIRECTORY_SEPARATOR;

            if (!file_exists($path)) {
                mkdir($path);
            }
        }

        return $artPath;
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