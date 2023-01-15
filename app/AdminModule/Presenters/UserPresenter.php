<?php

namespace App\AdminModule\Presenters;
use App\AdminModule\Components\UserCreateForm\UserCreateForm;
use App\AdminModule\Components\UserCreateForm\UserCreateFormFactory;
use App\AdminModule\Components\UserEditForm\UserEditForm;
use App\AdminModule\Components\UserEditForm\UserEditFormFactory;
use App\FrontModule\Components\NewPasswordForm\NewPasswordForm;
use App\FrontModule\Components\NewPasswordForm\NewPasswordFormFactory;
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

    /**
     * Akce pro vykreslení seznamu uživatelů
     */
    public function renderDefault():void{
        $this->template->allUsers=$this->usersFacade->findUsers(['order'=>'role_id']);
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

    public function createComponentUserCreateForm():UserCreateForm{
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

    public function createComponentNewPasswordForm():NewPasswordForm{
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
    #endregion injections
}