<?php

namespace App\FrontModule\Components\PaymentForm;

/**
 * Interface PaymentFormFactory
 * @package App\FrontModule\Components\PaymentForm
 */
interface PaymentFormFactory{

  public function create():PaymentForm;

}