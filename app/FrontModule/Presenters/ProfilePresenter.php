<?php


namespace App\FrontModule\Presenters;

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


    #region injections
    public function injectUsersFacade(UsersFacade $usersFacade){
        $this->usersFacade = $usersFacade;
    }
    public function injectOrdersFacade(OrdersFacade $ordersFacade){
        $this->ordersFacade = $ordersFacade;
    }
    #endregion injections
}