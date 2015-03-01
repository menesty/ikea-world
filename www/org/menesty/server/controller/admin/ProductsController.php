<?php
/**
 * User: Menesty
 * Date: 2/22/15
 * Time: 14:01
 */

include_once(Configuration::get()->getClassPath() . "AbstractAdminController.php");
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Paging.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PageContentService.php");


class ProductsController extends AbstractAdminController
{

    public function defaultAction()
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Products");
        $breadcrumb->append("/admin/products", "Products");

        $template = new Template("admin/page/products.html");

        $itemsCount = $this->productService->getCount($this->getGet());
        $pageCount = ceil($itemsCount / self::ITEM_PER_PAGE);
        $activePage = $this->getInt("page", 1, 1, $pageCount);

        $template->setParam("products", $this->productService->getAdminRange(Language::getSupported(),
            ($activePage - 1) * self::ITEM_PER_PAGE, self::ITEM_PER_PAGE, $this->getGet()));

        $template->setParam("pageCount", $pageCount);
        $template->setParam("activePage", $activePage);
        $template->setParam("paramBuilder", new ParamBuilder($this->getGet()));


        $pagingTemplate = new Template("admin/paging.html");

        $pagingTemplate->setParam("paging", new Paging($pageCount, $activePage));
        $pagingTemplate->setParam("pagingUrl", $this->getContextPath() . "admin/products");

        $template->setParam("paging_content", $pagingTemplate);


        $mainTemplate->setParam("main_content", $pagingTemplate);

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }

    /**
     * @Path({id})
     */
    public function edit($id)
    {
        $mainTemplate = $this->getBaseTemplate();
        $breadcrumb = $this->before($mainTemplate, "Edit Product");
        $breadcrumb->append("/admin/products", "Products");

        $template = new Template("admin/page/product_edit.html");

        $template->setParam("model", $this->productService->getAdminProduct($id));
        $breadcrumb->append("/admin/products/edit/" . $id, "Edit");

        $mainTemplate->setParam("main_content", $template);

        return $mainTemplate;
    }


    /**
     * @Method(POST)
     * @Path({id})
     */
    public function update($id)
    {
        $this->productService->adminUpdate(Language::getSupported(), $id, $this->getPost());

        return new Redirect("/admin/products");
    }

    public function downloadArtImages()
    {
        for ($i = 0; $i < 100; $i++) {
            $artNumbers = $this->productService->productForDownload();

            foreach ($artNumbers as $artNumber) {
                $this->downloadPhotos($artNumber["art_number"]);
                $this->productService->updateImageDownload($artNumber["art_number"]);
            }

            sleep(10);
        }
    }

    /**
     * @Path({artNumber})
     */
    public function downloadPhotos($artNumber)
    {
        $product = $this->productService->getProductByArtNumber(Language::getActiveLanguage(), $artNumber);

        if (!is_null($product)) {
            $url = "http://www.ikea.com/pl/pl/catalog/products/" . $product->getArtNumber() . "/";
        }

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

    protected function downloadContent($url)
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

    protected function downloadImages($artNumber, $folderPath, $imgList)
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

    protected function createFolderStructure($artNumber)
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