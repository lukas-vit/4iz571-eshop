<?php

namespace App\Model\Entities;

use LeanMapper\Entity;

/**
 * Class Product
 * @package App\Model\Entities
 * @property int $productId
 * @property string $title
 * @property string $url
 * @property string $description
 * @property float $price
 * @property int $ram
 * @property string $color
 * @property int $stock = 0
 * @property bool $available = true
 * @property float|null $discount
 * @property float $discountedPrice
 * @property Category|null $category m:hasOne
 * @property ProductPhoto[] $photos m:belongsToMany
 */
class Product extends Entity implements \Nette\Security\Resource{

  /**
   * @inheritDoc
   */
  function getResourceId():string{
    return 'Product';
  }
}