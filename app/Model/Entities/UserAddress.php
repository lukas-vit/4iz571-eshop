<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class UserAddress
 * @package App\Model\Entities
 * @property int $userAddressId
 * @property int $userId
 * @property string $name
 * @property string $street
 * @property string $city
 * @property string $zip
 * @property string $phone
 * @property string $type m:Enum(self::TYPE_*)
 */
class UserAddress extends Entity{

    const TYPE_BILLING = 'billing';
    const TYPE_DELIVERY = 'delivery';
}