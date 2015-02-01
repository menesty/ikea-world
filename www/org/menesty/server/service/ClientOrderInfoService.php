<?php
include_once(Configuration::get()->getClassPath() . "service" . DIRECTORY_SEPARATOR . "PasswordService.php");

/**
 * User: Menesty
 * Date: 2/1/15
 * Time: 21:13
 */
class ClientOrderInfoService extends AbstractService
{

    public function save(ClientOrderInfo $client)
    {
        $params = array("firstName" => $client->getFirstName(), "lastName" => $client->getLastName(),
            "telephone" => $client->getTelephone(), "address" => $client->getAddress(), "city" => $client->getCity(), "postCode" => $client->getPostCode(),
            "regionState" => $client->getRegionState(), "country" => $client->getComment());
        if (is_null($client->getId())) {
            $query = "INSERT INTO `client`(`first_name`,`last_name`,`telephone`,`address`,`city`,`post_code`,`region_state`,`country`)
            VALUES (:firstName,:lastName,:telephone,:address,:city,:postCode,:regionState,:country)";
        } else {
            $query = "update `client` set `first_name` = :firstName, `last_name` = :lastName, `telephone` = :telephone,
            `address` = :address,`city` = :city,`post_code` = :postCode,`region_state` = :regionState, `country` = :country
            WHERE `id` = :id";
            $params['id'] = $client->getId();
        }

        $connection = Database::get()->getConnection();

        $st = $connection->prepare($query);
        $st->execute($params);

        if (is_null($client->getId())) {
            $client->setId($connection->lastInsertId());

            $passwordService = new PasswordService(Configuration::get()->getPublicKey());
            $query = "INSERT INTO `credentials` (`client_id`,`login`,`password`) VALUES (:clientId,:login,:password)";
            $params = array("clientId" => $client->getId(), "login" => $client->getEmail(), "password" => $passwordService->generatePassword());
            $st = $connection->prepare($query);
            $st->execute($params);
        }

        return $client->getId();
    }

    protected function newInstance()
    {
        return new ClientOrderInfo();
    }
}