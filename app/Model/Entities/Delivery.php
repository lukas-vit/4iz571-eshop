<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Delivery
 * @package App\Model\Entities
 * @property int $deliveryId
 * @property string $name
 * @property float $price
 * @property int $deliveryTime
 * @property \DateTimeImmutable $lastModified
 */
class Delivery extends Entity {

}