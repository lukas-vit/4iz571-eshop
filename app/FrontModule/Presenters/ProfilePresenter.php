<?php


namespace App\FrontModule\Presenters;

use App\FrontModule\Components\NewPasswordForm\NewPasswordForm;
use App\FrontModule\Components\NewPasswordForm\NewPasswordFormFactory;
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

    /**
     * Metoda pro vykreslení uživatelského profilu
     * @return void
     * @throws \Exception
     */
    public function renderDefault(){
        $this->template->currentUser = $this->usersFacade->getUser($this->user->id);
        $this->template->userAddresses = $this->usersFacade->findUserAdresses($this->user->id);
        $this->template->userOrders = $this->ordersFacade->findOrdersByUser($this->user->id);
    }

    public function renderPersonal(){

    }

    public function renderOrders(){

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

    }

    public function renderBilling(){

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
    #endregion injections
}