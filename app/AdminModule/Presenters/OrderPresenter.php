<?php

namespace App\AdminModule\Presenters;

use App\Model\Facades\OrdersFacade;

class OrderPresenter extends BasePresenter{
    /** @var OrdersFacade $ordersFacade */
    private $ordersFacade;


    public function renderDefault(){
        $this->template->orders = $this->ordersFacade->findOrderDetails();
    }

    #region injections
    public function injectOrdersFacade(OrdersFacade $ordersFacade){
        $this->ordersFacade = $ordersFacade;
    }
    #endregion injections
}