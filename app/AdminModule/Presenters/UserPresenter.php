<?php

namespace App\AdminModule\Presenters;
use App\AdminModule\Components\UserCreateForm\UserCreateForm;
use App\AdminModule\Components\UserCreateForm\UserCreateFormFactory;
use App\AdminModule\Components\UserEditForm\UserEditForm;
use App\AdminModule\Components\UserEditForm\UserEditFormFactory;
use App\FrontModule\Components\BillingAddressForm\BillingAddressForm;
use App\FrontModule\Components\BillingAddressForm\BillingAddressFormFactory;
use App\FrontModule\Components\DeliveryAddressForm\DeliveryAddressForm;
use App\FrontModule\Components\DeliveryAddressForm\DeliveryAddressFormFactory;
use App\FrontModule\Components\NewPasswordForm\NewPasswordForm;
use App\FrontModule\Components\NewPasswordForm\NewPasswordFormFactory;
use App\Model\Entities\UserAddress;
use App\Model\Facades\UsersFacade;

/**
 * Class UserPresenter
 * @package App\AdminModule\Presenters
 */
class UserPresenter extends BasePresenter {

    /** @var UsersFacade $usersFacade */
    private $usersFacade;
    /** @var UserEditFormFactory $userEditFormFactory */
    private $userEditFormFactory;
    /** @var UserCreateFormFactory $userCreateFormFactory*/
    private $userCreateFormFactory;
    /** @var NewPasswordFormFactory $newPasswordForm */
    private $newPasswordFormFactory;
    /** @var BillingAddressFormFactory $billingAddressFormFactory */
    private $billingAddressFormFactory;
    /** @var DeliveryAddressFormFactory $deliveryAddressFormFactory */
    private $deliveryAddressFormFactory;

    /**
     * Akce pro vykreslení seznamu uživatelů
     */
    public function renderDefault():void{
        $this->template->allUsers=$this->usersFacade->findAndOrderUsers(['order'=>'role_id'], 'desc');
    }

    /**
     * Akce pro úpravu uživatele
     * @param int $id
     * @return void
     * @throws \Nette\Application\AbortException
     */
    public function renderEdit(int $id):void{
        try {
            $user=$this->usersFacade->getUser($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaný uživatel nebyl nalezen.');
            $this->redirect('default');
        }
        $form=$this->getComponent('userEditForm');
        $form->setDefaults($user);
        $this->template->users=$user;
    }

    public function renderPassword(int $id){
        try {
            $user = $this->usersFacade->getUser($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaný uživatel nebyl nalezen.');
            $this->redirect('default');
        }
        $form=$this->getComponent('newPasswordForm');
        $form->setDefaults($user);
        $this->template->users=$user;
    }

    public function renderBilling(int $id){
        try {
            $user = $this->usersFacade->getUser($id);
            $userAddresses = $this->usersFacade->findUserAdresses($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaná fakturační adresa uživatele nebyla nalezena.', 'error');
            $this->redirect('default');
        }

        foreach($userAddresses as $userAddress){
            if($userAddress instanceof UserAddress){
                if($userAddress->type == UserAddress::TYPE_BILLING){
                    $billingAddress = $userAddress;
                }
            }
        }

        if(empty($billingAddress)){
            $billingAddress['userId'] = $user->userId;
        }

        $form=$this->getComponent('billingAddressForm');
        $form->setDefaults($billingAddress);
        $this->template->users=$user;
    }

    public function renderDelivery(int $id){
        try {
            $user = $this->usersFacade->getUser($id);
            $userAddresses = $this->usersFacade->findUserAdresses($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaná doručovací adresa uživatele nebyla nalezena.', 'error');
            $this->redirect('default');
        }

        foreach($userAddresses as $userAddress){
            if($userAddress instanceof UserAddress){
                if($userAddress->type == UserAddress::TYPE_DELIVERY){
                    $deliveryAddress = $userAddress;
                }
            }
        }

        if(empty($deliveryAddress)){
            $deliveryAddress['userId'] = $user->userId;
        }

        $form=$this->getComponent('deliveryAddressForm');
        $form->setDefaults($deliveryAddress);
        $this->template->users=$user;
    }

    public function createComponentUserEditForm():UserEditForm{
        $form = $this->userEditFormFactory->create();
        $form->onCancel[]=function (){
            $this->redirect('default');
        };
        $form->onFinished[]=function ($message=null){
            if(!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onFailed[]=function ($message=null){
            if(!empty($message)){
                $this->flashMessage($message,'error');
            }
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentUserCreateForm():UserCreateForm{
        $form = $this->userCreateFormFactory->create();
        $form->onCancel[]=function (){
            $this->redirect('default');
        };
        $form->onFinished[]=function ($message=null){
            if(!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentNewPasswordForm():NewPasswordForm{
        $form = $this->newPasswordFormFactory->create();
        $form->onCancel[]=function (){
            $this->redirect('default');
        };
        $form->onFinished[]=function ($message=null){
            if(!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentBillingAddressForm():BillingAddressForm{
        $form = $this->billingAddressFormFactory->create();
        $form->onCancel[]=function (){
            $this->redirect('default');
        };
        $form->onFinished[]=function ($message=null){
            if(!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentDeliveryAddressForm():DeliveryAddressForm{
        $form = $this->deliveryAddressFormFactory->create();
        $form->onCancel[]=function (){
            $this->redirect('default');
        };
        $form->onFinished[]=function ($message=null){
            if(!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        return $form;
    }

    #region injections
    public function injectUsersFacade(UsersFacade $usersFacade){
        $this->usersFacade=$usersFacade;
    }
    public function injectUserEditFormFactory(UserEditFormFactory $userEditFormFactory){
        $this->userEditFormFactory=$userEditFormFactory;
    }
    public function injectUserCreateFormFactory(UserCreateFormFactory $userCreateFormFactory){
        $this->userCreateFormFactory = $userCreateFormFactory;
    }
    public function injectNewPasswordFormFactory(NewPasswordFormFactory $newPasswordFormFactory){
        $this->newPasswordFormFactory = $newPasswordFormFactory;
    }
    public function injectBillingAddressFormFactory(BillingAddressFormFactory $billingAddressFormFactory){
        $this->billingAddressFormFactory = $billingAddressFormFactory;
    }
    public function injectDeliveryAddressFormFactory(DeliveryAddressFormFactory $deliveryAddressFormFactory){
        $this->deliveryAddressFormFactory = $deliveryAddressFormFactory;
    }
    #endregion injections
}