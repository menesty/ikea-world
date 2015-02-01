<?php

/**
 * User: Menesty
 * Date: 2/1/15
 * Time: 21:38
 */
class CustomerOrderService extends AbstractService
{
    public function save(CustomerOrder $customerOrder)
    {
        $query = "INSERT INTO `client_order` (`client_id`,`created_date`,`total_price`,`currency`,`rate`,`comment`) VALUES (
        :clientId, NOW(), :totalPrice, :currency,:rate,:comment)";
        $params = array("clientId" => $customerOrder->getClientId(), "totalPrice" => $customerOrder->getTotalPrice(),
            "currency" => $customerOrder->getCurrency(), "rate" => $customerOrder->getRate(), 'comment' => $customerOrder->getComment());
        $connection = Database::get()->getConnection();
        $st = $connection->prepare($query);
        $st->execute($params);

        $customerOrder->setId($connection->lastInsertId());

        return $customerOrder->getId();
    }

    protected function newInstance()
    {
        return new CustomerOrder();
    }
}