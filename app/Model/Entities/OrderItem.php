<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class OrderItem
 * @package App\Model\Entities
 * @property int $orderItemId
 * @property int $orderDetailId
 * @property int $productId
 * @property int $quantity
 * @property float $price
 * @property-read OrderDetail $orderDetail m:hasOne
 * @property-read Product $product m:hasOne
 */
class OrderItem extends Entity {

}