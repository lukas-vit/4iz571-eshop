<?php

namespace App\Model\Facades;

use App\Model\Entities\Delivery;
use App\Model\Entities\OrderDetail;
use App\Model\Entities\OrderItem;
use App\Model\Entities\Payment;
use App\Model\Repositories\DeliveryRepository;
use App\Model\Repositories\OrderDetailRepository;
use App\Model\Repositories\OrderItemRepository;
use App\Model\Repositories\PaymentRepository;
use Nette\Utils\Strings;

/**
 * Class OrdersFacade
 * @package App\Model\Facades
 */
class OrdersFacade{

  private OrderDetailRepository $orderDetailRepository;
  private OrderItemRepository $orderItemRepository;
  private PaymentRepository $paymentRepository;
  private DeliveryRepository $deliveryRepository;

  /**
   * Metoda pro získání jednoho order detailu
   * @param int $id
   * @return OrderDetail
   * @throws \Exception
   */
  public function getOrderDetail(int $id):OrderDetail {
    return $this->orderDetailRepository->find($id);
  }

  /**
   * Metoda pro vyhledání order detailů
   * @param array|null $params = null
   * @param int $offset = null
   * @param int $limit = null
   * @return OrderDetail[]
   */
  public function findOrderDetails(array $params=null,int $offset=null,int $limit=null):array {
    return $this->orderDetailRepository->findAllBy($params,$offset,$limit);
  }

  /**
   * Metoda pro uložení order detailu
   * @param OrderDetail &$orderDetail
   */
  public function saveOrderDetail(OrderDetail &$orderDetail):void {
    $this->orderDetailRepository->persist($orderDetail);
  }

    /**
   * Metoda pro smazání produktu
   * @param OrderDetail $orderDetail
   * @return bool
   */
  public function deleteOrderDetail(OrderDetail $orderDetail):bool {
    try{
      return (bool)$this->orderDetailRepository->delete($orderDetail);
    }catch (\Exception $e){
      return false;
    }
  }

  public function saveOrderItem(OrderItem &$orderItem):void {
    $this->orderItemRepository->persist($orderItem);
  }

  public function getOrderItemsByOrderDetail(OrderDetail $orderDetail):array {
    return $this->orderItemRepository->findAllBy(['order_detail_id'=>$orderDetail->orderDetailId]);

    /**
     * Metoda pro nalezení všech detailů objednávvek uživatele
     * @param int $id
     * @return array
     */
  public function findOrdersByUser(int $id):array{
      return $this->orderDetailRepository->findAllBy(['user_id'=>$id]);
  }

  public function __construct(OrderDetailRepository $orderDetailRepository,OrderItemRepository $orderItemRepository,PaymentRepository $paymentRepository,DeliveryRepository $deliveryRepository)
  {
    $this->orderDetailRepository = $orderDetailRepository;
    $this->orderItemRepository = $orderItemRepository;
    $this->paymentRepository = $paymentRepository;
    $this->deliveryRepository = $deliveryRepository;
  }
}