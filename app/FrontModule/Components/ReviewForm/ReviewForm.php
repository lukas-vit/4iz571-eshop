<?php

namespace App\FrontModule\Components\ReviewForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class ReviewForm
 * @package App\FrontModule\Components\ReviewForm
 */
class ReviewForm extends Form{

  /**
   * ReviewForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }
  
  private function createSubcomponents(){
    $this->addHidden('productId');
    $this->addHidden('count','Počet kusů')
      ->addRule(Form::RANGE,'Chybný počet kusů.',[1,100])
      ->setDefaultValue(1);

    $this->addSubmit('button')
    ->getControlPrototype()
    ->setName('button')
    ->setAttribute('class','w-100')
    ->setHtml('<i class="fa fa-shopping-cart"></i>');
  }

}