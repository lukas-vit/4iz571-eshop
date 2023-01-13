<?php

namespace App\Model\Facades;

use App\Model\Entities\Payment;
use App\Model\Repositories\PaymentRepository;

/**
 * Class PaymentsFacade
 * @package App\Model\Facades
 */
class PaymentsFacade{
  private PaymentRepository $paymentRepository;

  /**
   * Metoda pro získání jednoho payment
   * @param int $id
   * @return Payment
   * @throws \Exception
   */
  public function getPayment(int $id):Payment {
    return $this->paymentRepository->find($id);
  }

  /**
   * Metoda pro vyhledání Payment
   * @param array|null $params = null
   * @param int $offset = null
   * @param int $limit = null
   * @return Payment[]
   */
  public function findPayment(array $params=null,int $offset=null,int $limit=null):array {
    return $this->paymentRepository->findAllBy($params,$offset,$limit);
  }

  /**
   * Metoda pro zjištění počtu Payment
   * @param array|null $params
   * @return int
   */
  public function findPaymentsCount(array $params=null):int {
    return $this->paymentRepository->findCountBy($params);
  }

  public function findAllPayments():array {
    return $this->paymentRepository->findAll();
  }

  /**
   * Metoda pro uložení Payment
   * @param Payment &$payment
   */
  public function savePayment(Payment &$payment):void {
    $this->paymentRepository->persist($payment);
  }

    /**
   * Metoda pro smazání Payment
   * @param Payment $payment
   * @return bool
   */
  public function deletePayment(Payment $payment):bool {
    try{
      return (bool)$this->paymentRepository->delete($payment);
    }catch (\Exception $e){
      return false;
    }
  }

  public function __construct(PaymentRepository $paymentRepository){
    $this->paymentRepository=$paymentRepository;
  }
}