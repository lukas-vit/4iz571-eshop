<?php

namespace App\FrontModule\Components\DeliveryAddressForm;

/**
 * Interface DeliveryAddressFormFactory
 * @package App\FrontModule\Components\DeliveryAddressForm
 */
interface DeliveryAddressFormFactory{

  public function create():DeliveryAddressForm;

}