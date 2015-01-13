<?php
set_time_limit(0);
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Product.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "ProductService.php");

/*59-300  26

 43 */

/**
 * User: Menesty
 * Date: 12/22/14
 * Time: 18:08
 */
class ImportController
{

    public function defaultAction()
    {
        $path = Configuration::get()->getClassPath() . "data" . DIRECTORY_SEPARATOR . "art_100-200";
        $productService = new ProductService();

        $folders = array("200_RU", "100_RU");

        foreach ($folders as $folder) {
            $ss = $path . DIRECTORY_SEPARATOR . $folder;

            foreach (new DirectoryIterator($ss) as $fileInfo) {
                if ($fileInfo->isDot()) continue;

                $product = $this->getContent($fileInfo);
                $productService->save("ru", $product);
            }
        }
    }

    public function products()
    {
        $productService = new ProductService();
        $result = $productService->getProducts();
        var_dump($result);
    }

    public function curl()
    {

    }

    public function importPhoto()
    {
        $productService = new ProductService();

        for ($i = 0; $i < 5; $i++) {
            $products = $productService->getProductEmptyPrice("ru");

            foreach ($products as $product) {
                $artNumber = $product->getArtNumber();

                var_dump($artNumber);
                $content = file_get_contents("http://www.ikea.com/pl/pl/catalog/products/" . $artNumber);

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

                    $prices = null;

                    if ($itemData->prices->hasFamilyPrice) {
                        $prices = $itemData->prices->familyNormal->priceNormal;
                    } else {
                        $prices = $itemData->prices->normal->priceNormal;
                    }

                    $result = preg_replace("/[A-z\s]/", "", $prices->priceExclVat);
                    $result = str_replace(",", ".", $result);

                    $productService->updatePrice($artNumber, $result);

                    $images = $itemData->images;

                    $path = $this->createFolderStructure($artNumber);
                    $this->downloadImage($artNumber, ($path . DIRECTORY_SEPARATOR . "large" . DIRECTORY_SEPARATOR), $images->large);
                    $this->downloadImage($artNumber, ($path . DIRECTORY_SEPARATOR . "normal" . DIRECTORY_SEPARATOR), $images->normal);
                    $this->downloadImage($artNumber, ($path . DIRECTORY_SEPARATOR . "zoom" . DIRECTORY_SEPARATOR), $images->zoom);
                    $this->downloadImage($artNumber, ($path . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR), $images->thumb);
                    $this->downloadImage($artNumber, ($path . DIRECTORY_SEPARATOR . "small" . DIRECTORY_SEPARATOR), $images->small);

                    sleep(5);
                }
            }
        }
    }

    private function downloadImage($artNumber, $folderPath, $imgList)
    {
        for ($i = 0; $i < sizeof($imgList); $i++) {
            $imgUrl = "http://www.ikea.com" . $imgList[$i];
            $imgName = $folderPath . $artNumber . "_" . $i . ".jpg";
            $content = file_get_contents($imgUrl);

            if (!$content) {
                error_log($imgUrl . " " . $imgName . "\n", 3, "download.log");
            } else {
                file_put_contents($imgName, file_get_contents($imgUrl));
            }

        }
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

    private function getContent($fileInfo)
    {
        $lines = file($fileInfo->getPathname());

        $fieldArray = array("art." => "artNumber", "title" => "title", "description" => "shortDescription",
            "дизайнер" => "designer", "Дизайнер" => "designer", "Размеры и характеристика товара:" => "size", "Характеристика упаковки:" => "packing",
            "Описание:" => "description", "Инструкция по уходу:" => "instruction");

        $activeField = null;
        $activeContent = "";
        $product = new Product();

        foreach ($lines as $line) {

            $content = trim(iconv("cp1251", "utf-8", $line));

            if ($content != "") {

                if (array_key_exists($content, $fieldArray)) {
                    if (!is_null($activeField)) {
                        $this->setFieldValue($activeField, $product, $activeContent);
                    }

                    $activeField = $fieldArray[$content];
                    $activeContent = "";
                } else {
                    $activeContent .= ($activeContent != "" ? "<br />" : "") . $content;
                }


            }

        }


        $this->setFieldValue($activeField, $product, $activeContent);

        return $product;
    }

    private function setFieldValue($field, $object, $value)
    {
        $method = "set" . ucfirst($field);
        $object->$method($value);
    }


}

