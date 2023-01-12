<?php

namespace App\FrontModule\Components\DeliveryForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class DeliveryForm
 * @package App\FrontModule\Components\DeliveryForm
 *
 * @method onFinished()
 * @method onCancel()
 */
class DeliveryForm extends Form{

  use SmartObject;

  /** @var callable[] $onFinished */
  public $onFinished = [];
  /** @var callable[] $onCancel */
  public $onCancel = [];

  /**
   * DeliveryForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $deliveryMethods = [
      'dpd' => 'DPD',
      'ppl' => 'PPL',
    ];
    $this->addRadioList('delivery', 'Způsob dopravy:', $deliveryMethods)
      ->setRequired('Vyberte způsob dopravy');
    
    $this->addSubmit('submit','Pokračovat na doručovací adresu')
      ->onClick[]=function(SubmitButton $button){
        $this->onFinished();
      };
  }
}