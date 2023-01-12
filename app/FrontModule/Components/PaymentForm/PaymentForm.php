<?php

namespace App\FrontModule\Components\PaymentForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class PaymentForm
 * @package App\FrontModule\Components\PaymentForm
 *
 * @method onFinished()
 * @method onCancel()
 */
class PaymentForm extends Form{

  use SmartObject;

  /** @var callable[] $onFinished */
  public $onFinished = [];
  /** @var callable[] $onCancel */
  public $onCancel = [];

  /**
   * PaymentForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $paymentMethods = [
      'card' => 'Platební karta',
      'cash' => 'Hotovost',
    ];
    $this->addRadioList('payment', 'Platební metoda:', $paymentMethods)
      ->setRequired('Vyberte prosím platební metodu.');
    
    $this->addSubmit('submit','Závazně odeslat objednávku')
      ->onClick[]=function(SubmitButton $button){
        $this->onFinished();
      };
  }
}