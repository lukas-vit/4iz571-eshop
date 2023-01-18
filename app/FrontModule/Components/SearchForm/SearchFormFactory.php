<?php

namespace App\FrontModule\Components\SearchForm;

/**
 * Interface SearchFormFactory
 * @package App\FrontModule\Components\SearchForm
 */
interface SearchFormFactory{

  public function create():SearchForm;

}