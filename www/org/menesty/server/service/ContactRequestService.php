<?php
include_once(Configuration::get()->getClassPath() . "model" . DIRECTORY_SEPARATOR . "ContactRequest.php");
/**
 * User: Menesty
 * Date: 2/3/15
 * Time: 21:32
 */
class ContactRequestService extends AbstractService
{

    public function save(ContactRequest $contactRequest)
    {
        $query = "INSERT INTO `contact_request` (`first_name`,`last_name`,`email`,`message`,`telephone`)
        VALUES (:firstName,:lastName,:email,:message,:telephone)";
        $params = array("firstName" => $contactRequest->getFirstName(), "lastName" => $contactRequest->getLastName(), "email" => $contactRequest->getEmail(),
            "message" => $contactRequest->getMessage(), "telephone" => $contactRequest->getTelephone());

        $connection = Database::get()->getConnection();

        $st = $connection->prepare($query);
        $st->execute($params);

        $contactRequest->setId($connection->lastInsertId());

        return $contactRequest->getId();
    }

    public function getList($offset, $limit) {
        $connection = Database::get()->getConnection();
        $st = $connection->prepare("select * from `contact_request` LIMIT :limit OFFSET :offset");
        $st->bindValue(':limit', $limit, PDO::PARAM_INT);
        $st->bindValue(':offset', $offset, PDO::PARAM_INT);
        $st->execute();

        return $this->transform($st->fetchAll());
    }

    public function getCount(array $params = array()){
        $connection = Database::get()->getConnection();
        $st = $connection->prepare('SELECT count(id) from `contact_request`');
        $st->setFetchMode(PDO::FETCH_NUM);
        $st->execute();
        $result = $st->fetch();
        return $result[0];
    }

    protected function newInstance()
    {
        return new ContactRequest();
    }
}