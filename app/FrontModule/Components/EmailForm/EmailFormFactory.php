<?php

namespace App\FrontModule\Components\EmailForm;

/**
 * Interface EmailFormFactory
 * @package App\FrontModule\Components\EmailForm
 */
interface EmailFormFactory{

  public function create():EmailForm;

}