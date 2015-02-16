<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Category.php");

/**
 * User: Menesty
 * Date: 1/2/15
 * Time: 23:04
 */
class CategoryService extends AbstractService
{
    public function getCategories($lang, $active = null, $showAll = false)
    {
        $connection = Database::get()->getConnection();

        $query = "SELECT `id`, `name_$lang` as `name`, `product_count` as productCount from `categories` " .
            "left join (select `category_id`, count(`category_id`) as `product_count` from  `product_category` left join `products` on (product_id = id) where published = 1 group by `category_id`) product_category on (`id` = `category_id`) " .

            "where parent_id IS NULL";
        if (!$showAll) {
            $query .= " and `product_count` IS NOT NULL";
        }

        $st = $connection->prepare($query);

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();
        $result = $this->transform($st->fetchAll());

        if (!is_null($active)) {
            $parents = $this->getParents($lang, $active);

            if (sizeof($parents) > 0) {
                $values = $this->getChilds($lang, $parents[0]->getId());
                foreach ($result as $rItem) {
                    if ($rItem->getId() == $parents[0]->getId()) {
                        $rItem->setSubCategories($values);
                    }
                }
            }
        }

        return $result;
    }

    public function getByIkeaUrl($lang, $ikeaUrl)
    {
        $connection = Database::get()->getConnection();
        $query = "SELECT `id`, `name_$lang` as `name` from `categories`" .
            " where ikea_url =:ikeaUrl limit 1";
        $params = array("ikeaUrl" => $ikeaUrl);

        $st = $connection->prepare($query);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute($params);
        return $this->transformRow($st->fetch());
    }


    public function getCategoriesTree($lang, $id = null)
    {
        $roots = $this->getCategories($lang, null, true);

        if (!is_null($id)) {
            $parents = $this->getParentIds($id);

            $rootElement = end($parents);

            foreach ($roots as $root) {
                if ($root->getId() == $rootElement) {
                    $val = array(&$root);
                    $this->populateSubItems($lang, $val, true);
                    break;
                }
            }
        }

        return $roots;
    }

    public function populateSubItems($lang, &$items, $showAll = false)
    {
        foreach ($items as $item) {
            $child = $this->getChilds($lang, $item->getId(), null, $showAll);
            $this->populateSubItems($lang, $child, $showAll);
            $item->setSubCategories($child);
        }
    }

    public function getChilds($lang, $id, $exclude = null, $showAll = false)
    {
        $connection = Database::get()->getConnection();
        $query = "SELECT `id`, `name_$lang` as `name` , `product_count` as productCount from `categories`" .
            "left join (select `category_id`, count(`category_id`) as `product_count` from  `product_category` left join `products` on (product_id = id) where published = 1 group by `category_id`) product_category on (`id` = `category_id`) " .
            " where parent_id=:id ";

        if (!$showAll) {
            $query .= " and `product_count` IS NOT NULL";
        }

        $params = array("id" => $id);

        if (!is_null($exclude)) {
            $query .= " and id <> :exclude";
            $params["exclude"] = $exclude;
        }

        $st = $connection->prepare($query);
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute($params);
        return $this->transform($st->fetchAll());
    }

    protected function newInstance()
    {
        return new Category();
    }

    public function getAdminCategories($activeLang, $languages, $id = null)
    {
        $fields = array('name');
        $queryPart = "SELECT ";
        $params = array();

        foreach ($fields as $field) {
            foreach ($languages as $lang) {
                $queryPart .= "if(" . $field . "_" . $lang . " IS NULL or trim(" . $field . "_" . $lang . ")='' ,false, true ) as " . $field . "_" . $lang . ", ";
            }
        }

        $queryPart .= " `id`, `name_$activeLang` as `name`, `parent_id` from `categories` where `parent_id` ";

        if (!is_null($id)) {
            $queryPart .= " = :parentId";
            $params["parentId"] = $id;
        } else {
            $queryPart .= " IS NULL ";
            $params["parentId"] = null;
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($queryPart);

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute($params);
        return $st->fetchAll();
    }

    public function isValid($id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("select `id` from `categories` where `id` = :id");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array('id' => (int)$id));
        $result = $st->fetchAll();
        return sizeof($result) > 0;
    }

    public function getAdminCategory($id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("select * from `categories` where `id` = :id");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array('id' => (int)$id));

        return $st->fetch();
    }

    public function adminUpdate($languages, $id, $data)
    {
        $params = array();
        if ($this->isValid($id)) {
            $query = "update `categories` set ";

            foreach ($languages as $lang) {
                $query .= "`name_$lang` = :name_$lang,";
                $params["name_$lang"] = trim($data["name_$lang"]);
            }

            $query = rtrim($query, ",");
            $query .= " where `id`=:id";
            $params["id"] = (int)$id;
        } else {
            $query = "INSERT INTO `categories` (`parent_id`,";
            $partQuery = ":parentId,";

            foreach ($languages as $lang) {
                $query .= "`name_$lang`,";
                $partQuery .= ":name_$lang,";
                $params["name_$lang"] = trim($data["name_$lang"]);
            }
            $query = rtrim($query, ",") . ") VALUES (" . rtrim($partQuery, ",") . ")";

            if (is_null($data["parent_id"]) || (int)$data["parent_id"] == 0 || !$this->isValid($data["parent_id"])) {
                $params["parentId"] = NULL;
            } else {
                $params["parentId"] = $data["parent_id"];
            }
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->execute($params);

        return $data["parent_id"];
    }

    public function isFourthLevel($id)
    {

        if (is_null($id)) {
            return false;
        }

        $result = $this->getParentIds($id);

        return sizeof($result) > 3;
    }

    /**
     * @return Category
     * @param mixed $lang , $id
     */
    public function getById($lang, $id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `name_$lang` as `name`, `parent_id`, `ikea_url` from `categories` where `id` = :id");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("id" => $id));

        return $this->transformRow($st->fetch());

    }

    public static function getParentIds($id)
    {
        if ((int)$id == 0) {
            return array();
        }

        do {
            $result[] = $id;
            $id = self::getParentId($id);
        } while (!is_null($id));

        return $result;
    }

    public function getParents($lang, Category $category)
    {
        if (is_null($category)) {
            return array();
        }

        $result = array($category);

        while (!is_null($category->getParentId())) {
            $category = $this->getById($lang, $category->getParentId());
            $result[] = $category;
        }

        return array_reverse($result);
    }

    private static function getParentId($id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `parent_id` from `categories` where `id` = :id");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("id" => $id));
        $result = $st->fetch();

        return $result['parent_id'];
    }

    public function save($lang, Category $category)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("INSERT INTO `categories` (`name_$lang`, `parent_id`, `ikea_url`) VALUES (:name,:parentId,:ikeaUrl)");
        $st->execute(array("name" => $category->getName(), "parentId" => $category->getParentId(), "ikeaUrl" => $category->getIkeaUrl()));

        $id = $connection->lastInsertId();
        $category->setId($id);

        return $id;

    }


} 