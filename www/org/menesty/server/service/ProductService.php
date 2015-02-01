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
    private $translatedFields = array("title", "short_description", "size", "packing", "instruction");

    public function save($lang, Product $product)
    {
        $connection = Database::get()->getConnection();

        $params = array("art_number" => $product->getArtNumber(), "title" => $product->getTitle(),
            "short_description" => $product->getShortDescription(), "designer" => $product->getDesigner(),
            "size" => $product->getSize(), "packing" => $product->getPacking(), "description" => $product->getDescription(),
            "instruction" => $product->getInstruction(), "price" => $product->getPrice()
        );

        if (is_null($this->getProductByArtNumber($lang, $product->getArtNumber()))) {
            $st = $connection->prepare("INSERT INTO `products` (`art_number`,`title_$lang`,`short_description_$lang`, `designer`,
        `size_$lang`, `packing_$lang`, `description_$lang`, `instruction_$lang`,`price`)
        VALUES (:art_number, :title, :short_description, :designer, :size, :packing, :description, :instruction
        , :price)");
        } else {
            $st = $connection->prepare("UPDATE `products` set `title_$lang` = :title, `short_description_$lang` = :short_description,
        `designer` = :designer, `size_$lang` = :size, `packing_$lang` = :packing, `description_$lang` = :description,
        `instruction_$lang` = :instruction, `price` = :price where art_number = :art_number");
        }

        $st->execute($params);

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
            $condition = " where category_id = :categoryId";
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `price`, `published`, `available` from `products` " . $join . $condition . " LIMIT :limit OFFSET :offset");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);

        if (!is_null($categoryId)) {
            $st->bindValue(':categoryId', $categoryId, PDO::PARAM_INT);

        }
        $st->execute();

        return $this->transform($st->fetchAll());
    }

    public function getAdminRange($languages, $offset, $limit)
    {
        $columns = array("title", "short_description", "size", "packing", "instruction");

        $query = "SELECT `id`, `art_number`, `price`, `published`, `available`, " . $this->getBooleanConditionByField("designer")
            . "," . $this->createLangAdminQueryCheck($languages, $columns) . " from `products` LIMIT :limit OFFSET :offset";

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
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
            $condition = " where category_id = :categoryId";
            $params["categoryId"] = $categoryId;
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(id) from `products`' . $join . $condition);
        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute($params);
        $result = $st->fetch();
        return $result[0];
    }

    public function getCount()
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(id) from `products`');
        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute();
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

        $this->clearCategories($primaryKey, @$data['category']);
        $this->addCategories($primaryKey, @$data['category']);
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


}

