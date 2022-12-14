<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Review
 * @package App\Model\Entities
 * @property int $reviewId
 * @property int $productId
 * @property int $userId
 * @property string|null $description
 * @property int $rating
 * @property \DateTimeImmutable $date
 * @property-read Product $product m:hasOne
 * @property-read User $user m:hasOne
 */
class Review extends Entity {

}