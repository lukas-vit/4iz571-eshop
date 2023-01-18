<?php

namespace App\FrontModule\Components\SearchForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class SearchForm
 * @package App\FrontModule\Components\SearchForm
 */
class SearchForm extends Form{

  use SmartObject;

  /**
   * SearchForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $this->addText('search')
      ->setRequired('Zadejte hledaný výraz')
      ->setHtmlAttribute('placeholder', 'Hledat...');
    $this->addSubmit('submit', "Vyhledat")
      ->setValidationScope([]);
  }
}