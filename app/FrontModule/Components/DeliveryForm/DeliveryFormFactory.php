<?php

namespace App\FrontModule\Components\DeliveryForm;

/**
 * Interface DeliveryFormFactory
 * @package App\FrontModule\Components\DeliveryForm
 */
interface DeliveryFormFactory{

  public function create():DeliveryForm;

}