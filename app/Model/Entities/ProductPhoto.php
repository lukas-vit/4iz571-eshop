<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class ProductPhoto
 * @package App\Model\Entities
 * @property int $photoId (photo_id)
 * @property Product|null $productId m:hasOne(product_id)
 * @property string $photoExtension = ''
 * @property bool $isThumbnail = false
 */
class ProductPhoto extends Entity {

}