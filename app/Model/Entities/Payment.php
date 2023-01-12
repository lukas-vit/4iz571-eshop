<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Payment
 * @package App\Model\Entities
 * @property int $paymentId
 * @property decimal $amount
 * @property string|null $provider
 * @property string|null $status
 * @property \DateTimeImmutable $lastModified
 */
class Payment extends Entity {

}