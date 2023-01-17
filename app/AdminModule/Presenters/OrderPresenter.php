<?php

namespace App\AdminModule\Presenters;

use App\Model\Entities\OrderDetail;
use App\Model\Facades\OrdersFacade;

class OrderPresenter extends BasePresenter{
    /** @var OrdersFacade $ordersFacade */
    private $ordersFacade;


    public function renderDefault(string $sort = null, string $order = null, string $filter = null){
        if($sort != null && $order!=null){
            if($filter != null){
                $this->template->orders = $this->ordersFacade->findAndOrderOrderDetails(['status' => $filter,'order' => $sort], $order);
                $this->template->dropDown = $sort.' '.$order;
                $this->template->filter = $filter;
            }else{
                $this->template->orders = $this->ordersFacade->findAndOrderOrderDetails(['order' => $sort], $order);
                $this->template->dropDown = $sort.' '.$order;
            }
        }elseif($filter != null){
            $this->template->orders = $this->ordersFacade->findOrderDetails(['status' => $filter]);
            $this->template->filter = $filter;
        }else{
            $this->template->orders = $this->ordersFacade->findOrderDetails();
        }
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

    public function handleDone(int $id){
        try {
            $order = $this->ordersFacade->getOrderDetail($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaná objednávka nebyla nalezena', 'error');
            $this->redirect('default');
        }

        if($order->paymentStatus == OrderDetail::TYPE_PAYMENT_PAID){
            if ($order->status == OrderDetail::TYPE_ORDER_PENDING){
                $order->status = OrderDetail::TYPE_ORDER_DONE;
                $this->ordersFacade->saveOrderDetail($order);
                $this->flashMessage('Stav objedkávky byl změněn na Done');
                $this->redirect('this');
            }
        } else{
            $this->flashMessage('Objednávka musí být zaplacena', 'error');
            $this->redirect('this');
        }
    }

    public function handlePayment(int $id){
        try {
            $order = $this->ordersFacade->getOrderDetail($id);
        }catch (\Exception $e){
            $this->flashMessage('Požadovaná objednávka nebyla nalezena', 'error');
            $this->redirect('default');
        }

        if($order->paymentStatus == OrderDetail::TYPE_PAYMENT_PENDING){
            $order->paymentStatus = OrderDetail::TYPE_PAYMENT_PAID;
            $this->ordersFacade->saveOrderDetail($order);
            $this->flashMessage('Objednávka byla zaplacena');
            $this->redirect('this');
        }
    }

    #region injections
    public function injectOrdersFacade(OrdersFacade $ordersFacade){
        $this->ordersFacade = $ordersFacade;
    }
    #endregion injections
}