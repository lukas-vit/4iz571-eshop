<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class ProductPhoto
 * @package App\Model\Entities
 * @property int $productPhotoId (product_photo_id)
 * @property Product $product m:hasOne(product)
 * @property int $productId (product_id)
 * @property string $photoExtension = ''
 * @property bool $isThumbnail = false
 */
class ProductPhoto extends Entity {

}