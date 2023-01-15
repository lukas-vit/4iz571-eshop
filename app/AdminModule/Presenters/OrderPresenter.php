<?php

namespace App\AdminModule\Presenters;

use App\Model\Facades\OrdersFacade;

class OrderPresenter extends BasePresenter{
    /** @var OrdersFacade $ordersFacade */
    private $ordersFacade;


    public function renderDefault(){
        $this->template->orders = $this->ordersFacade->findOrderDetails();
    }

    public function renderShow(int $id){
        try {
            $order = $this->ordersFacade->getOrderDetail($id);
        } catch (\Exception $e){
            $this->flashMessage('Požadovaná objednávka nebyla nalezena', 'error');
            $this->redirect('default');
        }
        $this->template->order = $order;
    }

    #region injections
    public function injectOrdersFacade(OrdersFacade $ordersFacade){
        $this->ordersFacade = $ordersFacade;
    }
    #endregion injections
}