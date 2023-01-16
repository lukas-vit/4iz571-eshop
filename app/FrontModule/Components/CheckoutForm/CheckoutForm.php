<?php

namespace App\FrontModule\Components\CheckoutForm;

use App\Model\Entities\UserAddress;
use App\Model\Facades\DeliveriesFacade;
use App\Model\Facades\PaymentsFacade;
use App\Model\Facades\UsersFacade;
use App\Model\Repositories\UserAddressRepository;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;
use Nette\Security\User;


/**
 * Class CheckoutForm
 * @package App\FrontModule\Components\CheckoutForm
 *
 * @method onFinished()
 * @method onCancel()
 */
class CheckoutForm extends Form{

  use SmartObject;

  private DeliveriesFacade $deliveriesFacade;
  private PaymentsFacade $paymentsFacade;
  private UsersFacade $usersFacade;
  private UserAddressRepository $userAddressRepository;

  private User $user;
  /**
   * CheckoutForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, DeliveriesFacade $deliveriesFacade, PaymentsFacade $paymentsFacade, User $user, UsersFacade $usersFacade, UserAddressRepository $userAddressRepository){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->deliveriesFacade = $deliveriesFacade;
    $this->paymentsFacade = $paymentsFacade;
    $this->usersFacade = $usersFacade;
    $this->userAddressRepository = $userAddressRepository;
    $this->user = $user;
    $this->createSubcomponents();
  }

  private function createSubcomponents(){

    if ($this->user->isLoggedIn()) {
      $user = $this->usersFacade->getUser($this->user->getId());
      $userAddresses = $this->userAddressRepository->findAllBy(['user_id' => $this->user->getId()]);

      if (count($userAddresses) != 0) {
        foreach ($userAddresses as $userAddress) {
          if ($userAddress->type == UserAddress::TYPE_DELIVERY) {
            $deliveryAddress = $userAddress;
          } else {
            $billingAddress = $userAddress;
          }
        }
      }
    }
      
    //email form
    $this->addEmail('email','E-mail')
      ->setDefaultValue($user->email ?? '')
      ->setRequired('Zadejte platný email');

    $this->addButton('emailSubmitButton','Pokračovat ke způsobu dopravy');

    //delivery form
    // get values from database
    $deliveryMethods = $this->deliveriesFacade->findAllDeliveries();
    $deliveryMethods = array_map(function ($delivery) {
      return $delivery->name;
    }, $deliveryMethods);

    $this->addRadioList('delivery', 'Způsob dopravy:', $deliveryMethods)
      ->setRequired('Vyberte způsob dopravy');
    
    $this->addButton('deliverySubmitButton','Pokračovat na doručovací adresu');

    //delivery address form
    $this->addText('delivery_name','Jméno a přijmení')
      ->setDefaultValue($deliveryAddress->name ?? '')
      ->setRequired('Zadejte jméno a přijmení');

    $this->addText('delivery_street','Ulice a číslo popisné')
      ->setDefaultValue($deliveryAddress->street ?? '')
      ->setRequired('Zadejte ulici a číslo popisné');

    $this->addText('delivery_city','Město')
      ->setDefaultValue($deliveryAddress->city ?? '')
      ->setRequired('Zadejte město');

    $this->addText('delivery_zip','PSČ')
      ->setDefaultValue($deliveryAddress->zip ?? '')
      ->setRequired('Zadejte PSČ')
      ->addFilter(function ($value) {
        return str_replace(' ', '', $value); // remove spaces from the postcode
      });

    $this->addText('delivery_phone','Telefon')
      ->setDefaultValue($deliveryAddress->phone ?? '')
      ->addFilter(function ($value) {
        return str_replace(' ', '', $value);
      })
      ->setRequired('Zadejte telefonní číslo');

    $this->addCheckbox('sameAsBilling','Fakturační adresa je stejná jako dodací adresa');
    
    $this->addButton('deliveryAddressSubmitButton','Pokračovat na fakturační adresu');

    //billing address form
    $this->addText('billing_name','Jméno a přijmení')
      ->setDefaultValue($billingAddress->name ?? '')
      ->setRequired('Zadejte jméno a přijmení');

    $this->addText('billing_street','Ulice a číslo popisné')
      ->setDefaultValue($billingAddress->street ?? '')
      ->setRequired('Zadejte ulici a číslo popisné');

    $this->addText('billing_city','Město')
      ->setDefaultValue($billingAddress->city ?? '')
      ->setRequired('Zadejte město');

    $this->addText('billing_zip','PSČ')
      ->setDefaultValue($billingAddress->zip ?? '')
      ->setRequired('Zadejte PSČ')
      ->addFilter(function ($value) {
        return str_replace(' ', '', $value); // remove spaces from the postcode
      });

    $this->addText('billing_phone','Telefon')
      ->setDefaultValue($billingAddress->phone ?? '')
      ->setRequired('Zadejte telefonní číslo')
      ->addFilter(function ($value) {
        return str_replace(' ', '', $value); // remove spaces from the phone
      });
    
    $this->addButton('billingAddressSubmitButton','Pokračovat k platební metodě');

    //payment form
    // get values from database

    $paymentMethods = $this->paymentsFacade->findAllPayments();
    $paymentMethods = array_map(function ($payment) {
      return $payment->name;
    }, $paymentMethods);

    $this->addRadioList('payment', 'Platební metoda:', $paymentMethods)
        ->setRequired('Vyberte prosím platební metodu.');
    
    $this->addSubmit('paymentSubmitButton','Závazně odeslat objednávku');
  }
}