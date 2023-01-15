<?php

namespace App\FrontModule\Components\BillingAddressForm;

/**
 * Interface BillingAddressFormFactory
 * @package App\FrontModule\Components\BillingAddressForm
 */
interface BillingAddressFormFactory{

    public function create():BillingAddressForm;

}