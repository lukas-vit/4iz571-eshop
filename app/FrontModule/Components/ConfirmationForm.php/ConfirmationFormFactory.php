<?php

namespace App\FrontModule\Components\ConfirmationForm;

/**
 * Interface ConfirmationFormFactory
 * @package App\FrontModule\Components\ConfirmationForm
 */
interface ConfirmationFormFactory{

  public function create():ConfirmationForm;

}