<?php


namespace App\FrontModule\Presenters;

use App\FrontModule\Components\BillingAddressForm\BillingAddressForm;
use App\FrontModule\Components\BillingAddressForm\BillingAddressFormFactory;
use App\FrontModule\Components\DeliveryAddressForm\DeliveryAddressForm;
use App\FrontModule\Components\DeliveryAddressForm\DeliveryAddressFormFactory;
use App\FrontModule\Components\NewPasswordForm\NewPasswordForm;
use App\FrontModule\Components\NewPasswordForm\NewPasswordFormFactory;
use App\FrontModule\Components\PersonalInfoForm\PersonalInfoForm;
use App\FrontModule\Components\PersonalInfoForm\PersonalInfoFormFactory;
use App\Model\Entities\UserAddress;
use App\Model\Facades\OrdersFacade;
use App\Model\Facades\UsersFacade;

/**
 * Class ProfilePresenter
 * @package App\AdminModule\Presenters
 */
class ProfilePresenter extends BasePresenter{
    /** @var UsersFacade $usersFacade */
    private $usersFacade;
    /** @var OrdersFacade $ordersFacade */
    private $ordersFacade;
    /** @var NewPasswordFormFactory $newPasswordFormFactory */
    private $newPasswordFormFactory;
    /** @var PersonalInfoFormFactory $personalInfoFormFactory */
    private $personalInfoFormFactory;
    /** @var BillingAddressFormFactory $billingAddressFromFactory*/
    private $billingAddressFormFactory;
    /** @var DeliveryAddressFormFactory $deliveryAddressFormFactory */
    private $deliveryAddressFormFactory;

    /**
     * Metoda pro vykreslení uživatelského profilu
     * @return void
     * @throws \Exception
     */
    public function renderDefault(){
        $this->template->currentUser = $this->usersFacade->getUser($this->user->id);
        $this->template->userAddresses = $this->usersFacade->findUserAdresses($this->user->id);
    }

    public function renderPersonal(){
        try {
            $user = $this->usersFacade->getUser($this->user->id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaný uživatel nebyl nalezen.', 'error');
            $this->redirect('default');
        }
        $form = $this->getComponent('personalInfoForm');
        $form->setDefaults($user);
    }

    public function renderOrders(){
        $this->template->orderDetails = $this->ordersFacade->findOrdersByUser($this->user->id);
    }

    public function renderPassword(){
        try {
            $user = $this->usersFacade->getUser($this->user->id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaný uživatel nebyl nalezen.', 'error');
            $this->redirect('default');
        }
        $form = $this->getComponent('newPasswordForm');
        $form->setDefaults($user);
    }

    public function renderDelivery(){
        try {
            $userAddresses = $this->usersFacade->findUserAdresses($this->user->id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaná dodací adresa nebyla nalezena.', 'error');
            $this->redirect('default');
        }

        foreach($userAddresses as $userAddress){
            if($userAddress instanceof UserAddress){
                if($userAddress->type == UserAddress::TYPE_DELIVERY){
                    $deliveryAddress = $userAddress;
                }
            }
        }

        $form = $this->getComponent('deliveryAddressForm');
        $form->setDefaults($deliveryAddress);
    }

    public function renderBilling(){
        try {
            $userAddresses = $this->usersFacade->findUserAdresses($this->user->id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaná fakturační adresa nebyla nalezena.', 'error');
            $this->redirect('default');
        }

        foreach($userAddresses as $userAddress){
            if($userAddress instanceof UserAddress){
                if($userAddress->type == UserAddress::TYPE_BILLING){
                    $billingAddress = $userAddress;
                }
            }
        }

        $form = $this->getComponent('billingAddressForm');
        $form->setDefaults($billingAddress);
    }

    protected function createComponentNewPasswordForm():NewPasswordForm{
        $form =$this->newPasswordFormFactory->create();
        $form->onFinished[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onFailed[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onCancel[]=function(){
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentPersonalInfoForm():PersonalInfoForm{
        $form = $this->personalInfoFormFactory->create();
        $form->onFinished[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onFailed[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onCancel[]=function(){
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentBillingAddressForm():BillingAddressForm{
        $form = $this->billingAddressFormFactory->create();
        $form->onFinished[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onFailed[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onCancel[]=function(){
            $this->redirect('default');
        };
        return $form;
    }

    protected function createComponentDeliveryAddressForm():DeliveryAddressForm{
        $form = $this->deliveryAddressFormFactory->create();
        $form->onFinished[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onFailed[]=function($message=''){
            if (!empty($message)){
                $this->flashMessage($message);
            }
            $this->redirect('default');
        };
        $form->onCancel[]=function(){
            $this->redirect('default');
        };
        return $form;
    }


    #region injections
    public function injectUsersFacade(UsersFacade $usersFacade){
        $this->usersFacade = $usersFacade;
    }
    public function injectOrdersFacade(OrdersFacade $ordersFacade){
        $this->ordersFacade = $ordersFacade;
    }
    public function injectNewPasswordFormFactory(NewPasswordFormFactory $newPasswordFormFactory){
        $this->newPasswordFormFactory = $newPasswordFormFactory;
    }
    public function injectPersonalInfoFormFactory(PersonalInfoFormFactory $personalInfoFormFactory){
        $this->personalInfoFormFactory = $personalInfoFormFactory;
    }
    public function injectBillingAddressFormFactory(BillingAddressFormFactory $billingAddressFormFactory){
        $this->billingAddressFormFactory = $billingAddressFormFactory;
    }
    public function injectDeliveryAddressFormFactory(DeliveryAddressFormFactory $deliveryAddressFormFactory){
        $this->deliveryAddressFormFactory = $deliveryAddressFormFactory;
    }
    #endregion injections
}