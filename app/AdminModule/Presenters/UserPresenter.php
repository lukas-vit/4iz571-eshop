<?php

namespace App\AdminModule\Presenters;
use App\AdminModule\Components\UserEditForm\UserEditFormFactory;
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

    /**
     * Akce pro vykreslení seznamu uživatelů
     */
    public function renderDefault():void{
        $this->template->users=$this->usersFacade->findUsers(['order'=>'role_id']);
    }
}