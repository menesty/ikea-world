<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Product.php");
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "CategoryService.php");

/**
 * User: Menesty
 * Date: 12/23/14
 * Time: 10:13
 */
class ProductService extends AbstractService
{
    private $translatedFields = array("title", "short_description", "size", "packing", "instruction", "description");

    public function save($lang, Product $product)
    {
        $connection = Database::get()->getConnection();

        $params = array("art_number" => $product->getArtNumber(), "title" => $product->getTitle(),
            "short_description" => $product->getShortDescription(), "designer" => $product->getDesigner(),
            "size" => $product->getSize(), "packing" => $product->getPacking(), "description" => $product->getDescription(),
            "instruction" => $product->getInstruction()
        );

        if (is_null($this->getProductByArtNumber($lang, $product->getArtNumber()))) {
            $st = $connection->prepare("INSERT INTO `products` (`art_number`,`title_$lang`,`short_description_$lang`, `designer`,
        `size_$lang`, `packing_$lang`, `description_$lang`, `instruction_$lang`)
        VALUES (:art_number, :title, :short_description, :designer, :size, :packing, :description, :instruction
        )");
        } else {
            $st = $connection->prepare("UPDATE `products` set `title_$lang` = :title, `short_description_$lang` = :short_description,
        `designer` = :designer, `size_$lang` = :size, `packing_$lang` = :packing, `description_$lang` = :description,
        `instruction_$lang` = :instruction where art_number = :art_number");
        }


        if (is_null($product->getArtNumber())) {
            var_dump($params);
            die();
        }

        $st->execute($params);

    }

    public function insertGag($artNumber)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO `products` (`art_number`,`title_ru`,`short_description_ru`, `designer`,
        `size_ru`, `packing_ru`, `description_ru`, `instruction_ru`, `available`)
        VALUES (:art_number, '', '', '', '', '', '', '', 1)");
        $st->execute(array("art_number" => $artNumber));

        return $connection->lastInsertId();
    }

    public function updatePrice($artNumber, $price)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("UPDATE `products` set `price` = :price where art_number = :art_number");
        $st->execute(array("price" => $price, "art_number" => $artNumber));
    }

    public function getProductByArtNumber($lang, $artNumber)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `description_$lang` as `description`, `price`, `published`, `available` from `products` where art_number = :artNumber limit 1;");
        $st->bindParam("artNumber", $artNumber);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();
        return $this->transformRow($st->fetch());
    }

    public function getProducts()
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT * from `products`');
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();

        return $this->transform($st->fetchAll());
    }

    public function getPublishedRange($lang, $categoryId, $offset, $limit)
    {
        $join = "";
        $condition = "";
        if (!is_null($categoryId)) {
            $join = " left join `product_category` on (`id` = `product_id`)";
            $condition = " and category_id = :categoryId";
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `price`, `published`, `available` from `products` " . $join . ' where published=1 ' . $condition . " LIMIT :limit OFFSET :offset");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);

        if (!is_null($categoryId)) {
            $st->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);

        }
        $st->execute();

        return $this->transform($st->fetchAll());
    }

    public function getSearchPublishedRange($lang, $searchStrings, $offset, $limit)
    {

        $where = $this->searchBuildWhere($lang, $searchStrings);

        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `price`, `published`, `available` from `products` " . $where[0] . " and published=1 LIMIT :limit OFFSET :offset");
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($where[1] as $key => $param) {
            $st->bindValue(':' . $key, $param, PDO::PARAM_STR);
        }

        $st->execute();

        return $this->transform($st->fetchAll());
    }

    private function searchBuildWhere($lang, array $searchStrings)
    {
        $searchFields = array("art_number" => false, "title" => true, "short_description" => true, "designer" => false, "size" => true, "packing" => true, "instruction" => true);
        $where = " ";
        $params = array();

        foreach ($searchFields as $field => $translated) {
            $where .= ' (';
            $index = 0;
            foreach ($searchStrings as $searchString) {
                $fieldName = $field . ($translated ? '_' . $lang : '');
                $where .= ' `' . $fieldName . '` LIKE :' . $fieldName . $index . ' OR';
                $params[$fieldName . $index] = '%' . $searchString . '%';
                $index++;
            }
            $where = rtrim($where, 'OR') . ' ) OR';
        }

        $where = ' WHERE' . rtrim($where, 'OR');

        return array($where, $params);

    }

    public function getSearchPublishedCount($lang, array $searchStrings)
    {
        $where = $this->searchBuildWhere($lang, $searchStrings);
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(id) from `products` ' . $where[0]);
        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute($where[1]);
        $result = $st->fetch();
        return $result[0];
    }

    public function getAdminRange($languages, $offset, $limit, array $queryParams)
    {
        $params = array();
        $columns = array("title", "short_description", "description", "size", "packing", "instruction");

        $query = "SELECT `id`, `art_number`, `price`, `published`, `available`, " . $this->getBooleanConditionByField("designer")
            . "," . $this->createLangAdminQueryCheck($languages, $columns) . " from `products` ";

        $where = "";

        if (isset($queryParams["artNumber"])) {
            $where .= " `art_number` LIKE :artNumber and";
            $params["artNumber"] = "%" . $queryParams["artNumber"] . "%";
        }

        if (isset($queryParams["published"])) {
            $where .= " `published`= 1 and";
        }

        if (isset($queryParams["available"])) {
            $where .= " `available`= 1 and";
        }

        if (sizeof($params) > 0) {

            $where = rtrim($where, "and");
            $where = "where " . $where;
        }


        $query .= $where . " LIMIT :limit OFFSET :offset";

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);

        foreach ($params as $key => $value) {
            $st->bindValue(':' . $key, $value);
        }

        $st->execute();

        return $st->fetchAll();
    }

    public function getAdminProduct($id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT * from `products` where `id` = :id");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("id" => $id));
        return $st->fetch();
    }

    public function getPublishedCount($categoryId)
    {
        $join = "";
        $condition = "";
        $params = array();
        if (!is_null($categoryId)) {
            $join = " left join `product_category` on (`id` = `product_id`)";
            $condition = " and category_id = :categoryId";
            $params["categoryId"] = $categoryId;
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(id) from `products`' . $join . ' where `published` = 1 ' . $condition);
        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute($params);
        $result = $st->fetch();
        return $result[0];
    }

    public function getCount(array $queryParams = array())
    {

        $where = "";
        $params = array();

        if (isset($queryParams["artNumber"])) {
            $where .= " `art_number` LIKE :artNumber and";
            $params["artNumber"] = "%" . $queryParams["artNumber"] . "%";
        }

        if (isset($queryParams["published"])) {
            $where .= " `published`= 1 and";
        }

        if (isset($queryParams["available"])) {
            $where .= " `available`= 1 and";
        }

        if (sizeof($params) > 0) {
            $where = rtrim($where, "and");
            $where = " where " . $where;
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(id) from `products`' . $where);
        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute($params);
        $result = $st->fetch();
        return $result[0];
    }


    public function getProductEmptyPrice($lang)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `price`, `published`, `available` from `products` where price = 0");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();
        return $this->transform($st->fetchAll());
    }


    protected function newInstance()
    {
        return new Product();
    }

    public function getBestSeller($lang, $count = 1)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `price`, `published`, `available` from `products`  LIMIT :limit");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->bindValue(':limit', $count, PDO::PARAM_INT);
        $st->execute();
        return $this->transform($st->fetchAll());
    }

    /**
     * @return Product
     */
    public function getById($lang, $id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `description_$lang` as `description`, `price`, `published`, `available` from `products` where `id` = :id;");
        $st->bindParam("id", $id);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();
        return $this->transformRow($st->fetch());
    }

    public function adminUpdate(array $languages, $primaryKey, array $data)
    {
        $params["price"] = $data["price"];
        $params["designer"] = $data["designer"];
        $params["published"] = (int)$this->getBoolean(@$data["published"]);
        $params["available"] = (int)$this->getBoolean(@$data["available"]);

        if (!is_null($primaryKey)) {
            $query = "update `products` set `price` = :price, `designer` = :designer,`published`= :published, `available` = :available,";

            foreach ($this->translatedFields as $field) {
                foreach ($languages as $lang) {
                    $query .= "`" . $field . "_" . $lang . "` = :" . $field . "_" . $lang . ",";
                    $params[$field . "_" . $lang] = $data[$field . "_" . $lang];
                }
            }

            $query = rtrim($query, ",");
            $query .= " where `id` = :id";
            $params["id"] = $primaryKey;
        } else {
            $query = "INSERT INTO  `products` (";
            $params["artNumber"] = $data["art_number"];
            $queryValue = "";

            foreach ($this->translatedFields as $field) {
                foreach ($languages as $lang) {
                    $query .= "`" . $field . "_" . $lang . "`,";
                    $params[$field . "_" . $lang] = $data[$field . "_" . $lang];
                    $queryValue .= ":" . $field . "_" . $lang . ",";
                }
            }

            $query .= "`art_number`, `price`, `designer`,`available`, `published`) VALUES (" . $queryValue . ":artNumber,:price,:designer,:available,:published );";
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->execute($params);

        if (is_null($primaryKey)) {
            $primaryKey = $connection->lastInsertId();
        }

        //$this->clearCategories($primaryKey, @$data['category']);
        //$this->addCategories($primaryKey, @$data['category']);
    }

    public function getProductCategories($productId)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `category_id` from `product_category` where `product_Id` = :id");

        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute(array("id" => $productId));
        $result = $st->fetchAll();
        $joinResult = array();

        foreach ($result as $categoryId) {
            $joinResult[] = $categoryId[0];
        }

        return $joinResult;
    }

    private function clearCategories($productId)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("delete from `product_category` where product_id = :id");
        $st->execute(array("id" => $productId));
    }

    private function addCategories($productId, $categoryId)
    {
        $categories = CategoryService::getParentIds($categoryId);

        if (sizeof($categories) == 0) {
            return;
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO `product_category` (`product_id`, `category_id`) VALUES (:productId, :categoryId)");

        foreach ($categories as $category) {
            $st->execute(array("productId" => $productId, "categoryId" => $category));
        }

    }

    public function assignCategories($productId, array $categoriesIds)
    {
        $connection = Database::get()->getConnection();

        $this->clearCategories($productId);

        $st = $connection->prepare("INSERT INTO `product_category` (`product_id`, `category_id`) VALUES (:productId, :categoryId)");

        foreach ($categoriesIds as $category) {
            $st->execute(array("productId" => $productId, "categoryId" => $category));
        }
    }


    public function getNotCategorized()
    {
        $query = "select `products`.`id`, `products`.`art_number` from `products` left join `product_category` on (`id` = `product_id`) where `product_id` is NULL";
        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();

        return $st->fetchAll();
    }

    public function productForDownload()
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("select `art_number` from `products` where `image_download` = 0 limit 20");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();

        return $st->fetchAll();
    }

    public function updateImageDownload($artNumber)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("update `products` set `image_download` = 1 where `art_number` = :artNumber");
        $st->execute(array("artNumber" => $artNumber));
    }
}

