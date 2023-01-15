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
 * @property string|null $paymentStatus m:Enum(self::TYPE_PAYMENT_*)
 * @property int|null $deliveryId
 * @property string|null $status m:Enum(self::TYPE_ORDER_*)
 * @property float $total
 * @property DateTime|null $created
 * @property DateTime|null $lastModified
 * @property-read User|null $user m:hasOne
 * @property-read Payment|null $payment m:hasOne
 * @property-read Delivery|null $delivery m:hasOne
 * @property-read OrderItem[] $orderItems m:belongsToMany
 */
class OrderDetail extends Entity{

    const TYPE_PAYMENT_PENDING = 'pending';
    const TYPE_PAYMENT_PAID = 'paid';
    const TYPE_ORDER_PENDING = 'pending';
    const TYPE_ORDER_DONE = 'done';
}