<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Payment
 * @package App\Model\Entities
 * @property int $paymentId
 * @property string $name
 * @property string $type
 * @property float $price
 * @property string|null $description
 * @property \DateTimeImmutable $lastModified
 */
class Payment extends Entity {

}