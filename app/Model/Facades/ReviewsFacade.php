<?php

namespace App\Model\Facades;

use App\Model\Entities\Review;
use App\Model\Repositories\ReviewRepository;

/**
 * Class ReviewsFacade
 * @package App\Model\Facades
 */
class ReviewsFacade{
  private ReviewRepository $reviewRepository;

  /**
   * Metoda pro získání jednoho review
   * @param int $id
   * @return Review
   * @throws \Exception
   */
  public function getReview(int $id):Review {
    return $this->reviewRepository->find($id);
  }

  /**
   * Metoda pro vyhledání Reviewů
   * @param array|null $params = null
   * @param int $offset = null
   * @param int $limit = null
   * @return Review[]
   */
  public function findReviews(array $params=null,int $offset=null,int $limit=null):array {
    return $this->reviewRepository->findAllBy($params,$offset,$limit);
  }

  /**
   * Metoda pro zjištění počtu Reviewů
   * @param array|null $params
   * @return int
   */
  public function findReviewsCount(array $params=null):int {
    return $this->reviewRepository->findCountBy($params);
  }

  /**
   * Metoda pro uložení Reviewu
   * @param Review &$Review
   */
  public function saveReview(Review &$review):void {
    $this->reviewRepository->persist($review);
  }

    /**
   * Metoda pro smazání Reviewu
   * @param Review $Review
   * @return bool
   */
  public function deleteReview(Review $review):bool {
    try{
      return (bool)$this->reviewRepository->delete($review);
    }catch (\Exception $e){
      return false;
    }
  }

  public function __construct(ReviewRepository $reviewRepository){
    $this->reviewRepository=$reviewRepository;
  }
}