<?php

namespace App\FrontModule\Components\ProductCartFormBig;

use App\FrontModule\Components\CartControl\CartControl;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class ProductCartFormBig
 * @package App\FrontModule\Components\ProductCartFormBig
 */
class ProductCartFormBig extends Form{

  use SmartObject;

  private CartControl $cartControl;

  /**
   * ProductCartForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  /**
   * Metoda pro předání komponenty košíku jako závislosti
   * @param CartControl $cartControl
   */
  public function setCartControl(CartControl $cartControl):void {
    $this->cartControl=$cartControl;
  }

  private function createSubcomponents(){
    $this->addHidden('productId');
    $this->addHidden('count','Počet kusů')
      ->addRule(Form::RANGE,'Chybný počet kusů.',[1,100])
      ->setDefaultValue(1);

    $this->addSubmit('button', "Přidat do košíku")
      ->getControlPrototype()
      ->setAttribute('class','w-100');
  }
}