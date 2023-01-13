<?php

namespace App\Model\Entities;

use Dibi\DateTime;
use LeanMapper\Entity;

/**
 * Class OrderDetail
 * @package App\Model\Entities
 * @property int $orderDetailId
 * @property int|null $userId
 * @property int|null $paymentId
 * @property string|null $paymentStatus
 * @property int|null $deliveryId
 * @property string|null $status
 * @property float $total
 * @property DateTime|null $created
 * @property DateTime|null $lastModified
 * @property-read User|null $user m:hasOne
 * @property-read Payment|null $payment m:hasOne
 * @property-read Delivery|null $delivery m:hasOne
 */
class OrderDetail extends Entity{

}