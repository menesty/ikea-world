<?php

include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "NewsItem.php");

/**
 * User: Menesty
 * Date: 2/17/15
 * Time: 22:42
 */
class NewsItemService extends AbstractService
{

    public function latest($lang, $limit = 3)
    {
        $query = "select `title_$lang` as title, `description_$lang` as description, `published_date` from `news`" .
            " where `published` = 1  order by `published_date` desc LIMIT :limit";

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $st->bindValue(':limit', $limit, PDO::PARAM_INT);

        $st->execute();

        return $this->transform($st->fetchAll());
    }

    public function listRange($lang, $limit, $offset)
    {
        $query = "select `title_$lang` as title, `short_description_$lang`, `published_date` from `news`" .
            " where `published` = 1  order by `published_date` desc LIMIT :limit OFFSET :offset";

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->setFetchMode(PDO::FETCH_ASSOC);

        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);

        $st->execute();

        return $this->transform($st->fetchAll());
    }

    public function getCount()
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(id) from `news` ');
        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute();
        $result = $st->fetch();
        return $result[0];
    }

    public function getAdminNews($id)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT * from `news` where `id` = :id");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("id" => $id));

        return $st->fetch();
    }

    public function getAdminRange($languages, $lang, $offset, $limit)
    {
        $fields = array("title", "description");
        $query = "select  `id`, `title_$lang` as `title`, `published`, `published_date`, " . $this->createLangAdminQueryCheck($languages, $fields) .
            " from `news` LIMIT :limit OFFSET :offset";

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);

        $st->setFetchMode(PDO::FETCH_ASSOC);

        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);

        $st->execute();

        return $st->fetchAll();
    }

    public function adminUpdate(array $languages, $id, array $data)
    {

        $params = array();

        if (!is_null($id)) {
            $query = "update `news` set ";

            foreach ($languages as $lang) {
                $query .= "`title_$lang` = :title_$lang,";
                $query .= "`description_$lang` = :description_$lang,";
                $params["title_$lang"] = trim($data["title_$lang"]);
                $params["description_$lang"] = trim($data["description_$lang"]);

            }

            $query .= " published = :published, published_date = :publishedDate";
            $params["published"] = $this->getBoolean(@$data["published"]);
            $params["publishedDate"] = $this->getSqlDateTime($data["publishedDate"], "d/m/Y H:i");

            $query .= " where `id`=:id";
            $params["id"] = (int)$id;

        } else {
            $query = "INSERT INTO `news` (";
            $partQuery = "";

            foreach ($languages as $lang) {
                $query .= "`title_$lang`, `description_$lang`,";
                $partQuery .= ":title_$lang, :description_$lang,";

                $params["title_$lang"] = trim($data["title_$lang"]);
                $params["description_$lang"] = trim($data["description_$lang"]);
            }
            $query = $query . "`published`,`published_date`) VALUES (" . $partQuery . ":published, :publishedDate)";

            $params["published"] = $this->getBoolean(@$data["published"]);
            $params["publishedDate"] = $this->getSqlDateTime($data["publishedDate"], "d/m/Y H:i");
        }

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->execute($params);

    }


    public function newInstance()
    {
        return new NewsItem();
    }
}