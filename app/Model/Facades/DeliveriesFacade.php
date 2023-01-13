<?php

namespace App\Model\Facades;

use App\Model\Entities\Delivery;
use App\Model\Repositories\DeliveryRepository;

/**
 * Class DeliveriesFacade
 * @package App\Model\Facades
 */
class DeliveriesFacade{
  private DeliveryRepository $deliveryRepository;

  /**
   * Metoda pro získání jednoho delivery
   * @param int $id
   * @return Delivery
   * @throws \Exception
   */
  public function getDelivery(int $id):Delivery {
    return $this->deliveryRepository->find($id);
  }

  /**
   * Metoda pro vyhledání delivery
   * @param array|null $params = null
   * @param int $offset = null
   * @param int $limit = null
   * @return Delivery[]
   */
  public function findDeliveries(array $params=null,int $offset=null,int $limit=null):array {
    return $this->deliveryRepository->findAllBy($params,$offset,$limit);
  }

  public function findAllDeliveries():array {
    return $this->deliveryRepository->findAll();
  }

  /**
   * Metoda pro zjištění počtu Delivery
   * @param array|null $params
   * @return int
   */
  public function findDeliveriesCount(array $params=null):int {
    return $this->deliveryRepository->findCountBy($params);
  }

  /**
   * Metoda pro uložení Delivery
   * @param Delivery &$delivery
   */
  public function saveDelivery(Delivery &$delivery):void {
    $this->deliveryRepository->persist($delivery);
  }

    /**
   * Metoda pro smazání Delivery
   * @param Delivery $delivery
   * @return bool
   */
  public function deleteDelivery(Delivery $delivery):bool {
    try{
      return (bool)$this->deliveryRepository->delete($delivery);
    }catch (\Exception $e){
      return false;
    }
  }

  public function __construct(DeliveryRepository $deliveryRepository){
    $this->deliveryRepository=$deliveryRepository;
  }
}