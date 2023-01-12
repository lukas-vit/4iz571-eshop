<?php

namespace App\FrontModule\Components\EmailForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class EmailForm
 * @package App\FrontModule\Components\EmailForm
 *
 * @method onFinished()
 * @method onCancel()
 */
class EmailForm extends Form{

  use SmartObject;

  /** @var callable[] $onFinished */
  public $onFinished = [];
  /** @var callable[] $onCancel */
  public $onCancel = [];

  /**
   * EmailForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $this->addEmail('email','E-mail')
      ->setRequired('Zadejte platný email');

    
    $this->addSubmit('submit','Pokračovat ke způsobu dopravy');
  }
}