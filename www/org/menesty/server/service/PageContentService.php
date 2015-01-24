<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "PageContent.php");

/**
 * User: Menesty
 * Date: 1/20/15
 * Time: 09:53
 */
class PageContentService extends AbstractService
{

    public function getPageContent($lang, $contentKey){
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `content_key`, `title_$lang` as `title`, `content_$lang` as `content` from `page_content` where `content_key` = :contentKey");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("contentKey" => $contentKey));
        return $this->transformRow($st->fetch());
    }

    public function getPageContentList($languages = array())
    {
        $fields = array('title', 'content');
        $queryPart = "SELECT ";

        foreach ($fields as $field) {
            foreach ($languages as $lang) {
                $queryPart .= "if(" . $field . "_" . $lang . " IS NULL or trim(" . $field . "_" . $lang . ")='' ,false, true ) as " . $field . "_" . $lang . ", ";
            }
        }

        $queryPart .= " content_key from `page_content`";

        $connection = Database::get()->getConnection();
        $st = $connection->prepare($queryPart);

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();
        return $st->fetchAll();
    }

    public function getAdminPageContent($key)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT * from page_content where `content_key` = :contentKey");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute(array("contentKey" => $key));
        return $st->fetch();
    }

    public function adminUpdate($languages = array(), &$data)
    {
        $primaryKey = "content_key";
        $model = $this->getAdminPageContent(@$data["content_key"]);
        $query = "";
        $fields = array('title', 'content');
        $params = array();

        if ($model) {
            $query = "update `page_content` set ";
            foreach ($fields as $field) {
                foreach ($languages as $lang) {
                    $query .= "`" . $field . "_" . $lang . "` = :" . $field . "_" . $lang . ",";
                    $params[$field . "_" . $lang] = $data[$field . "_" . $lang];
                }
            }
            $query = rtrim($query, ",");
            $query .= " where `" . $primaryKey . "` = :" . $primaryKey;
            $params[$primaryKey] = $data[$primaryKey];

        } else {
            $query = "INSERT INTO  `page_content` (";
            $queryValue = "";

            foreach ($fields as $field) {
                foreach ($languages as $lang) {
                    $query .= "`" . $field . "_" . $lang . "`,";
                    $params[$field . "_" . $lang] = $data[$field . "_" . $lang];
                    $queryValue .= ":" . $field . "_" . $lang . ",";
                }
            }
            $query .= "`" . $primaryKey . "`) VALUES (" . $queryValue . ":" . $primaryKey . ");";
            $params[$primaryKey] = $data[$primaryKey];


        }
        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->execute($params);
    }

    protected function newInstance()
    {
        return new PageContent();
    }
} 