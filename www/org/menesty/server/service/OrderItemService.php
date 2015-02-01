<?php

/**
 * User: Menesty
 * Date: 2/1/15
 * Time: 21:48
 */
class OrderItemService extends AbstractService
{
    public function save(OrderItem $orderItem)
    {
        $query = "INSERT INTO `order_item` (`order_id`,`product_id`,`price`,`item_count`) VALUES (:orderId, :productId,:price,:itemCount)";
        $params = array("orderId" => $orderItem->getOrderId(), "productId" => $orderItem->getProductId(), "price" => $orderItem->getPrice(),
            "itemCount" => $orderItem->getCount());

        $connection = Database::get()->getConnection();

        $st = $connection->prepare($query);

        $st->execute($params);

        $orderItem->setId($connection->lastInsertId());

        return $orderItem->getId();
    }

    protected function newInstance()
    {
        return new OrderItem();
    }
}