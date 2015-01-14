<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Product.php");

/**
 * User: Menesty
 * Date: 12/23/14
 * Time: 10:13
 */
class ProductService extends AbstractService
{
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

    public function getPublishedRange($lang, $offset, $limit)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `art_number`, `title_$lang` as `title`, `short_description_$lang` as `short_description`,
                                    `designer`, `size_$lang` as `size`, `packing_$lang` as `packing`, `instruction_$lang` as `instruction`,
                                    `price`, `published`, `available` from `products` LIMIT :limit OFFSET :offset");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();

        return $this->transform($st->fetchAll());
    }

    public function getPublishedCount()
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
                                    `price`, `published`, `available` from `products` where price <> 0 LIMIT :limit");
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


    public static function get()
    {
        return new ProductService();
    }


} 