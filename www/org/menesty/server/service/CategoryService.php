<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Category.php");

/**
 * User: Menesty
 * Date: 1/2/15
 * Time: 23:04
 */
class CategoryService extends AbstractService
{
    public function getCategories($lang, $active = null)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `name_$lang` as `name` from `categories` where parent_id IS NULL");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();
        $result = $this->transform($st->fetchAll());

        if (!is_null($active)) {
            $id = is_object($active) ? $active->getId() : $active;
            $previous = null;

            while (!is_null($id)) {
                $item = $this->getById($lang, $id);
                $item->setSubCategories($this->getChilds($lang, $id));

                if (!is_null($previous)) {
                    foreach ($item->getSubCategories() as $subItem) {
                        if ($subItem->getId() == $previous->getId()) {
                            $subItem->setSubCategories($previous->getSubCategories());
                        }
                    }
                }

                if (is_null($item->getParentId())) {
                    foreach ($result as $rItem) {
                        if ($rItem->getId() == $item->getId()) {
                            $rItem->setSubCategories($item->getSubCategories());
                        }
                    }
                    $id = null;
                } else {

                    $previous = $item;
                    $id = $item->getParentId();
                }
            }
        }

        return $result;
    }


    public function getCategoriesTree($lang)
    {
        $roots = $this->getCategories($lang);
        $this->populateSubItems($lang, $roots);
        return $roots;
    }

    private function populateSubItems($lang, &$items)
    {
        foreach ($items as $item) {
            $child = $this->getChilds($lang, $item->getId());
            $this->populateSubItems($lang, $child);
            $item->setSubCategories($child);
        }
    }

    private function getChilds($lang, $id, $exclude = null)
    {
        $connection = Database::get()->getConnection();
        $query = "SELECT `id`, `name_$lang` as `name` from `categories` where parent_id=:id";
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

    public function getByName($lang, $name)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `name_$lang` as `name` from `categories` where name_$lang = :name");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("name" => $name));
        return $this->transformRow($st->fetch());
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

        $queryPart .= " `id`, `name_$activeLang` as `name`, `parent_id` from `categories` ";

        if (!is_null($id)) {
            $queryPart .= " where `parent_id` = :parentId";
            $params["parentId"] = $id;
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
                $params["name_$lang"] = $data["name_$lang"];
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
                $params["name_$lang"] = $data["name_$lang"];
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

    public function isThirdLevel($id)
    {
        if (is_null($id)) {
            return false;
        }

        $connection = Database::get()->getConnection();
        $activeId = $id;
        $st = $connection->prepare("SELECT `parent_id` from `categories` where `id` = :id");
        $st->setFetchMode(PDO::FETCH_ASSOC);

        for ($lvl = 0; $lvl < 2; $lvl++) {
            $st->execute(array("id" => $activeId));
            if ($result = $st->fetch()) {
                if (is_null($result["parent_id"])) {
                    return false;
                } else {
                    $activeId = $result["parent_id"];
                }
            }
        }

        return true;
    }

    /**
     * @return Category
     * @param mixed $lang , $id
     */
    public function getById($lang, $id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `name_$lang` as `name`, `parent_id` from `categories` where `id` = :id");
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

    private static function getParentId($id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `parent_id` from `categories` where `id` = :id");
        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("id" => $id));
        $result = $st->fetch();

        return $result['parent_id'];
    }
} 