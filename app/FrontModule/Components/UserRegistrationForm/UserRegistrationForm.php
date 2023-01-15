<?php

namespace App\FrontModule\Components\UserRegistrationForm;

use App\Model\Entities\Category;
use App\Model\Entities\User;
use App\Model\Entities\UserAddress;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class UserRegistrationForm
 * @package App\FrontModule\Components\UserRegistrationForm
 *
 * @method onFinished()
 * @method onCancel()
 */
class UserRegistrationForm extends Form{

  use SmartObject;

  /** @var callable[] $onFinished */
  public $onFinished = [];
  /** @var callable[] $onCancel */
  public $onCancel = [];
  /** @var UsersFacade $usersFacade */
  private $usersFacade;
  /** @var Nette\Security\Passwords $passwords */
  private $passwords;

  /**
   * UserRegistrationForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   * @param UsersFacade $usersFacade
   * @noinspection PhpOptionalBeforeRequiredParametersInspection
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, UsersFacade $usersFacade, Nette\Security\Passwords $passwords){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->usersFacade=$usersFacade;
    $this->passwords=$passwords;
    $this->createSubcomponents();
  }

  private function createSubcomponents(){
    $this->addText('name','Jméno a příjmení:')
      ->setRequired('Zadejte své jméno')
      ->setHtmlAttribute('maxlength',40)
      ->addRule(Form::MAX_LENGTH,'Jméno je příliš dlouhé, může mít maximálně 40 znaků.',40);
    $this->addEmail('email','E-mail')
      ->setRequired('Zadejte platný email')
      ->addRule(function(Nette\Forms\Controls\TextInput $input){
        try{
          $this->usersFacade->getUserByEmail($input->value);
        }catch (\Exception $e){
          //pokud nebyl uživatel nalezen (tj. je vyhozena výjimka), je to z hlediska registrace v pořádku
          return true;
        }
        return false;
      },'Uživatel s tímto e-mailem je již registrován.');
    $password=$this->addPassword('password','Heslo');
    $password
      ->setRequired('Zadejte požadované heslo')
      ->addRule(Form::MIN_LENGTH,'Heslo musí obsahovat minimálně 5 znaků.',5);
    $this->addPassword('password2','Heslo znovu:')
      ->addRule(Form::EQUAL,'Hesla se neshodují',$password);

    //Doručovací adresa
      $this->addText('nameDelivery','Jméno a příjmení:')
          ->setRequired('Zadejte své jméno doručovací adresy');
      $this->addText('cityDelivery','Město')
          ->setRequired('Zadejte město doručovací adresy');
      $this->addText('streetDelivery','Ulice a číslo')
          ->setRequired('Zadejte ulici a číslo popisné doručovací adresy');
      $this->addInteger('zipDelivery', 'PSČ')
          ->setRequired('Zadejte PSČ doručovací adresy');
      $this->addInteger('phoneDelivery', 'Telefonní číslo')
          ->setRequired('Zadejte telefonní číslo doručovací adresy');

      $this->addCheckbox('sameAsBilling', 'Je Vaše fakturační adresa stejná jako doručovací?');

      //Fakturační adresa
      $this->addText('nameBilling','Jméno a příjmení:')
          ->setRequired('Zadejte své jméno doručovací adresy');
      $this->addText('cityBilling','Město')
          ->setRequired('Zadejte město doručovací adresy');
      $this->addText('streetBilling','Ulice a číslo')
          ->setRequired('Zadejte ulici a číslo popisné doručovací adresy');
      $this->addInteger('zipBilling', 'PSČ')
          ->setRequired('Zadejte PSČ doručovací adresy');
      $this->addInteger('phoneBilling', 'Telefonní číslo')
          ->setRequired('Zadejte telefonní číslo doručovací adresy');

    $this->addSubmit('ok','registrovat se')
      ->onClick[]=function(SubmitButton $button){

        //uložení uživatele
        $values=$this->getValues('array');
        $user = new User();
        $user->name=$values['name'];
        $user->email=$values['email'];
        $user->password=$this->passwords->hash($values['password']); //heslo samozřejmě rovnou hashujeme :)
        $this->usersFacade->saveUser($user);

        $userDeliveryAddress = new UserAddress();
        $userDeliveryAddress->name = $values['nameDelivery'];
        $userDeliveryAddress->city = $values['cityDelivery'];
        $userDeliveryAddress->street = $values['streetDelivery'];
        $userDeliveryAddress->userId = $user->userId;
        $userDeliveryAddress->zip = (string)$values['zipDelivery'];
        $userDeliveryAddress->phone = (string)$values['phoneDelivery'];
        $userDeliveryAddress->type = UserAddress::TYPE_DELIVERY;
        $this->usersFacade->saveUserAddress($userDeliveryAddress);

        $userBillingAddress = new UserAddress();
        if($values['sameAsBilling']){
            $userBillingAddress->name = $values['nameDelivery'];
            $userBillingAddress->city = $values['cityDelivery'];
            $userBillingAddress->street = $values['streetDelivery'];
            $userBillingAddress->userId = $user->userId;
            $userBillingAddress->zip = (string)$values['zipDelivery'];
            $userBillingAddress->phone = (string)$values['phoneDelivery'];
            $userBillingAddress->type = UserAddress::TYPE_BILLING;
            $this->usersFacade->saveUserAddress($userBillingAddress);
        }else{
            $userBillingAddress->name = $values['nameBilling'];
            $userBillingAddress->city = $values['cityBilling'];
            $userBillingAddress->street = $values['streetBilling'];
            $userBillingAddress->userId = $user->userId;
            $userBillingAddress->zip = (string)$values['zipBilling'];
            $userBillingAddress->phone = (string)$values['phoneBilling'];
            $userBillingAddress->type = UserAddress::TYPE_BILLING;
            $this->usersFacade->saveUserAddress($userBillingAddress);
        }

        $this->onFinished();
      };
    $this->addSubmit('storno','zrušit')
      ->setValidationScope([])
      ->onClick[]=function(SubmitButton $button){
        $this->onCancel();
      };
  }

}