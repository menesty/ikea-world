<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "Category.php");

/**
 * User: Menesty
 * Date: 1/2/15
 * Time: 23:04
 */
class CategoryService extends AbstractService
{
    public function getCategories($lang)
    {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("SELECT `id`, `name_$lang` as `name` from `categories` where parent_id IS NULL");

        $st->setFetchMode(PDO::FETCH_ASSOC);
        $st->execute();
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


} 