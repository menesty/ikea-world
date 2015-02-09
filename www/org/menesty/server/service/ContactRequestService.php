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

    public function getList() {

    }

    protected function newInstance()
    {
        return new ContactRequest();
    }
}