<?php

namespace App\FrontModule\Components\DeliveryAddressForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class DeliveryAddressForm
 * @package App\FrontModule\Components\DeliveryAddressForm
 *
 * @method onFinished()
 * @method onCancel()
 */
class DeliveryAddressForm extends Form{

  use SmartObject;

  /** @var callable[] $onFinished */
  public $onFinished = [];
  /** @var callable[] $onCancel */
  public $onCancel = [];

  /**
   * DeliveryAddressForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $this->addText('name','Jméno a přijmení')
      ->setRequired('Zadejte jméno a přijmení');

    $this->addText('street','Ulice a číslo popisné')
      ->setRequired('Zadejte ulici a číslo popisné');

    $this->addText('city','Město')
      ->setRequired('Zadejte město');

    $this->addText('zip','PSČ')
      ->setRequired('Zadejte PSČ');

    $this->addText('phone','Telefon')
      ->setRequired('Zadejte telefonní číslo');
    
    $this->addSubmit('submit','Pokračovat na fakturační adresu')
      ->setValidationScope([])
      ->onClick[]=function(SubmitButton $button){
        $this->onFinished();
      };
  }
}