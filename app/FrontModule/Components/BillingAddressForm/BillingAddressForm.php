<?php

namespace App\FrontModule\Components\BillingAddressForm;

use App\Model\Entities\UserAddress;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class BillingAddressForm
 * @package App\FrontModule\Components\BillingAddressForm
 *
 * @method onFinished($message='')
 * @method onFailed($message='')
 * @method onCancel()
 */
class BillingAddressForm extends Form {

    use SmartObject;

    /** @var callable[] $onFinished */
    public $onFinished = [];
    /** @var callable[] $onFailed */
    public $onFailed = [];
    /** @var callable[] $onCancel */
    public $onCancel = [];

    /** @var UsersFacade $usersFacade */
    private $usersFacade;

    /**
     * BillingAddressForm constructor
     * @param Nette\ComponentModel\IContainer|null $parent
     * @param string|null $name
     * @param UsersFacade $usersFacade
     */
    public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, UsersFacade $usersFacade){
        parent::__construct($parent, $name);
        $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
        $this->usersFacade=$usersFacade;
        $this->createSubComponents();
    }

    private function createSubComponents(){
        $this->addHidden('userId');
        $this->addHidden('type');

        $this->addText('name','Jméno a příjmení:')
            ->setRequired('Zadejte své jméno');
        $this->addText('city','Město')
            ->setRequired('Zadejte město');
        $this->addText('street','Ulice a číslo')
            ->setRequired('Zadejte ulici a číslo popisné');
        $this->addInteger('zip', 'PSČ')
            ->setRequired('Zadejte PSČ');
        $this->addInteger('phone', 'Telefonní číslo')
            ->setRequired('Zadejte telefonní číslo');
        $this->addSubmit('ok', 'uložit údaje')
            ->onClick[]=function (SubmitButton $button){
            $values = $this->getValues('array');

            try{
                $userAddresses = $this->usersFacade->findUserAdresses($values['userId']);
                foreach($userAddresses as $userAddress){
                    if($userAddress instanceof UserAddress){
                        if($userAddress->type == UserAddress::TYPE_BILLING){
                            $billingAddress = $userAddress;
                        }
                    }
                }
            }catch (\Exception $e){
                $this->onFailed('Zvolená fakturační adresa nebyla nalezena.');
                return;
            }

            if(empty($billingAddress)){
                $billingAddress = new UserAddress();
            }

            $billingAddress->assign($values, ['name','city','street']);
            $billingAddress->userId=(int)$values['userId'];
            $billingAddress->zip = (string)$values['zip'];
            $billingAddress->phone = (string)$values['phone'];
            $billingAddress->type = UserAddress::TYPE_BILLING;
            $this->usersFacade->saveUserAddress($billingAddress);

            $this->onFinished('Fakturační údaje byly změněny');
        };
        $this->addSubmit('storno','zrušit')
            ->setValidationScope([])
            ->onClick[]=function(SubmitButton $button){
            $this->onCancel();
        };
    }

    /**
     * Metoda pro nastavení výchozích hodnot
     * @param $values
     * @param bool $erase
     * @return $this|BillingAddressForm
     */
    public function setDefaults($values, bool $erase = false){
        if($values instanceof UserAddress){
            $values=[
                'userId'=>$values->userId,
                'name'=>$values->name,
                'street'=>$values->street,
                'city'=>$values->city,
                'zip'=>$values->zip,
                'phone'=>$values->phone,
                'type'=>UserAddress::TYPE_BILLING
            ];
        }
        parent::setDefaults($values, $erase);
        return $this;
    }
}