<?php

namespace App\Model\Entities;

use Dibi\DateTime;
use LeanMapper\Entity;

/**
 * Class OrderDetail
 * @package App\Model\Entities
 * @property int $orderId
 * @property int|null $userId = null
 * @property int $paymentId
 * @property int $cartId
 * @property decimal $total
 * @property Cart $cart m:hasOne()
 * @property User $user m:hasOne()
 * @property Payment $payment m:hasOne()
 * @property DateTime|null $created
 * @property DateTime|null $lastModified
 */
class OrderDetail extends Entity{

/*   public function updateCartItems(){
    $this->row->cleanReferencingRowsCache('cart_item'); //smažeme cache, aby se položky v košíku znovu načetly z DB bez nutnosti načtení celého košíku
  } */

/*   public function getTotalCount():int {
    $result = 0;
    if (!empty($this->items)){
      foreach ($this->items as $item){
        $result+=$item->count;
      }
    }
    return $result;
  }

  public function getTotalPrice():float {
    $result=0;
    if (!empty($this->items)){
      foreach ($this->items as $item){
        $result+=$item->product->price*$item->count;
      }
    }
    return $result;
  }
 */
}