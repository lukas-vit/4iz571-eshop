<?php

namespace App\FrontModule\Components\CheckoutForm;

use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;


/**
 * Class CheckoutForm
 * @package App\FrontModule\Components\CheckoutForm
 *
 * @method onFinished()
 * @method onCancel()
 */
class CheckoutForm extends Form{

  use SmartObject;

  /**
   * CheckoutForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    //email form
    $this->addEmail('email','E-mail');
     /*  ->setRequired('Zadejte platný email'); */

    $this->addButton('emailSubmitButton','Pokračovat ke způsobu dopravy');

    //delivery form
    $deliveryMethods = [
      'dpd' => 'DPD',
      'ppl' => 'PPL',
    ];
    $this->addRadioList('delivery', 'Způsob dopravy:', $deliveryMethods);
     /*  ->setRequired('Vyberte způsob dopravy'); */
    
    $this->addButton('deliverySubmitButton','Pokračovat na doručovací adresu');

    //delivery address form
    $this->addText('delivery_name','Jméno a přijmení');
   /*  ->setRequired('Zadejte jméno a přijmení'); */

    $this->addText('delivery_street','Ulice a číslo popisné');
 /*      ->setRequired('Zadejte ulici a číslo popisné'); */

    $this->addText('delivery_city','Město');
/*       ->setRequired('Zadejte město'); */

    $this->addText('delivery_zip','PSČ')
/*       ->setRequired('Zadejte PSČ') */
      ->addFilter(function ($value) {
        return str_replace(' ', '', $value); // remove spaces from the postcode
      });

    $this->addText('delivery_phone','Telefon');
/*       ->setRequired('Zadejte telefonní číslo'); */

    $this->addCheckbox('sameAsBilling','Fakturační adresa je stejná jako dodací adresa');
    
    $this->addButton('deliveryAddressSubmitButton','Pokračovat na fakturační adresu');

    //billing address form
    $this->addText('billing_name','Jméno a přijmení');
    /* ->setRequired('Zadejte jméno a přijmení'); */

    $this->addText('billing_street','Ulice a číslo popisné');
/*       ->setRequired('Zadejte ulici a číslo popisné'); */

    $this->addText('billing_city','Město');
/*       ->setRequired('Zadejte město'); */

    $this->addText('billing_zip','PSČ')
/*       ->setRequired('Zadejte PSČ') */
      ->addFilter(function ($value) {
        return str_replace(' ', '', $value); // remove spaces from the postcode
      });

    $this->addText('billing_phone','Telefon');
/*       ->setRequired('Zadejte telefonní číslo'); */
    
    $this->addButton('billingAddressSubmitButton','Pokračovat k platební metodě');

    //payment form
    $paymentMethods = [
      'card' => 'Platební karta',
      'cash' => 'Hotovost',
    ];
    $this->addRadioList('payment', 'Platební metoda:', $paymentMethods);
/*       ->setRequired('Vyberte prosím platební metodu.'); */
    
    $this->addSubmit('paymentSubmitButton','Závazně odeslat objednávku');
  }
}