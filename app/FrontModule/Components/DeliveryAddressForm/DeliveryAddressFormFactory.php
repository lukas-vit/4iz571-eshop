<?php

namespace App\FrontModule\Components\DeliveryAddressForm;

/**
 * Interface DeliveryAddressFormFactory
 * @package App\FrontModule\Components\DeliveryAddressFormFactory
 */
interface DeliveryAddressFormFactory{

    public function create():DeliveryAddressForm;
}