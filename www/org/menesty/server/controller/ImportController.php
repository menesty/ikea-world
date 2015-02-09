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
        $path = Configuration::get()->getClassPath() . "data" . DIRECTORY_SEPARATOR;
        $productService = new ProductService();

        $folders = array("by");

        foreach ($folders as $folder) {
            $ss = $path . DIRECTORY_SEPARATOR . $folder;

            foreach (new DirectoryIterator($ss) as $fileInfo) {
                if ($fileInfo->isDot()) continue;

                $product = $this->getContent($fileInfo);
                $productService->save("by", $product);
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
        $products = $productService->getProductEmptyPrice("ru");

        foreach ($products as $product) {
            $artNumber = $product->getArtNumber();
            $url = "http://www.ikea.com/pl/pl/catalog/products/" . $artNumber . "/";
            $content = $this->downloadContent($url);

            if ($content) {
                preg_match("/var jProductData = ({.*?});/", $content, $matches);
                echo $artNumber;
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
                $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "large" . DIRECTORY_SEPARATOR), $images->large);
                $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "normal" . DIRECTORY_SEPARATOR), $images->normal);
                $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "zoom" . DIRECTORY_SEPARATOR), $images->zoom);
                $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "thumb" . DIRECTORY_SEPARATOR), $images->thumb);
                $this->downloadImages($artNumber, ($path . DIRECTORY_SEPARATOR . "small" . DIRECTORY_SEPARATOR), $images->small);

                sleep(2);
            }
        }
    }

    private function downloadImages($artNumber, $folderPath, $imgList)
    {
        for ($i = 0; $i < sizeof($imgList); $i++) {
            $imgUrl = "http://www.ikea.com" . $imgList[$i];
            $imgName = strtolower($folderPath) . $artNumber . "_" . $i . ".jpg";

            if (!file_exists($imgName)) {
                $content = $this->downloadContent($imgUrl);

                if (!$content) {
                    error_log($imgUrl . " " . $imgName . "\n", 3, "download.log");
                } else {
                    file_put_contents($imgName, $content);
                }
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

        echo $fileInfo->getFilename() . "<br/>";

        $fieldArray = array("art." => "artNumber", "title" => "title", "description" => "shortDescription",
            "дизайнер" => "designer", "Дизайнер" => "designer", "Дызайнер" => "designer", "Дызайнер:" => "designer",
            "Размеры и характеристика товара:" => "size",
            "Характеристика упаковки:" => "packing","Характарыстыка пакавання:"=> "packing",
            "Апісанне:"=>"description","Апысанне:"=>"description",
            "Памер і характарыстыка тавару:"=>"size",
            "Памеры і характарыстыка тавару:"=>"size","Памеры і характарыстыка"=>"size",
            "Памеры і характарыстыка пакавання:"=>"size","Харатарстыка пакавання:"=>"packing",
            "Характарстыка пакавання:"=>"packing","Характарыстыка ўпакоўкі:" => "packing",
            "Памер і характарстыка тавару:"=>"size","Інструкцыя па догляду:"=>"instruction",
            "Описание:" => "description", "Инструкция по уходу:" => "instruction", "Размеры и характеристика товару:" => "size");

        $activeField = null;
        $activeContent = "";
        $product = new Product();

        foreach ($lines as $line) {
            if (mb_detect_encoding($line, 'UTF-8', true) != 'UTF-8') {
                $content = trim(iconv("cp1251", "utf-8", $line));
            } else {
                $content = trim($line);
            }

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


    public function retryDownloadPhotos()
    {
        $filePath = Configuration::get()->getSiteRoot() . "/download.log";
        $lines = file($filePath);

        $error = "";

        foreach ($lines as $line) {
            $parts = preg_split("/\s/", $line);

            $data = $this->downloadContent($parts[0]);
            $path = str_replace("//", "/", $parts[1]);
            $path = str_replace("ikea-world", "ikea-world/www", $path);

            if ($data) {
                file_put_contents($path, $data);
                sleep(3);
            } else {
                $error .= $line . "\n";
            }
        }

        file_put_contents(Configuration::get()->getSiteRoot() . "/test.log", $error);
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

    public function sortProducts()
    {
        $productService = new ProductService();
        $products = $productService->getNotCategorized();
        $categoryService = new CategoryService();
        $categories = $categoryService->getCategoriesTree(Language::getActiveLanguage());


        $data = array();
        foreach ($categories as $category) {
            foreach ($category->getSubCategories() as $subCategory) {
                foreach ($subCategory->getSubCategories() as $dno) {
                    $data[$dno->getId()] = array("name" => $dno->getName(), "parent" => array("id" => $subCategory->getId(), "name" => $subCategory->getName(),
                        "parent" => array("id" => $category->getId(), "name" => $category->getName())));
                }
            }
        }

        $lines = file(Configuration::get()->getDataPath() . "test_output.txt");
        $content = "";
        $i=0;
        foreach ($lines as $line) {
            $parts = preg_split("/##/", $line);
            $found = false;
            foreach ($data as $key => $value) {
                $index = 1;
                if(sizeof($parts) > 4) {
                    $index = 2;
                }

                if (trim($value["name"]) == trim($parts[$index]) && trim($value["parent"]["name"]) == trim($parts[$index+1]) && trim($value["parent"]["parent"]["name"]) == trim($parts[$index+2])) {
                        $i++;
                        echo "found $i: " . $line . "<br />";
                    $found = true;

                    $productId = $parts[0];
                    $categoriesId = array($key, $value["parent"]["id"], $value["parent"]["parent"]["id"]);

                    $productService->assignCategories($productId, $categoriesId);
                    break;
                }
            }

            if(!$found) {
                $content .= $line;
            }

        }

        file_put_contents(Configuration::get()->getDataPath() . "not_sorted.txt", $content);
    }

    public function info()
    {
        echo getPreparedArtNumber("S09874436");
    }
}

