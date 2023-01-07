<?php

namespace App\FrontModule\Components\ConfirmationForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class ConfirmationForm
 * @package App\FrontModule\Components\ConfirmationForm
 */
class ConfirmationForm extends Form{

  use SmartObject;

  /**
   * ConfirmationForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $this->addHidden('cartItemId');
    $this->addSubmit('remove')
      ->getControlPrototype()
      ->setName('button')
      ->addAttributes(['class' => 'btn btn-danger'])
      ->setHtml('Odebrat z košíku');
    $this->addSubmit('cancel', "Zrušit")
      ->setValidationScope([]);
  }
}