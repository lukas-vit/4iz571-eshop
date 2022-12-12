<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class ProductPhoto
 * @package App\Model\Entities
 * @property int $photoId
 * @property Product $productId m:hasOne
 * @property string $photoExtension = ''
 */
class ProductPhoto extends Entity {

}