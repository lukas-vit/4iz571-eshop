<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\ProductCartForm\ProductCartForm;
use App\FrontModule\Components\ProductCartForm\ProductCartFormFactory;
use App\Model\Facades\ProductPhotoFacade;
use App\FrontModule\Components\ReviewForm\ReviewForm;
use App\FrontModule\Components\ReviewForm\ReviewFormFactory;
use App\Model\Facades\ProductsFacade;
use App\Model\Facades\ReviewsFacade;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Multiplier;

/**
 * Class ProductPresenter
 * @package App\FrontModule\Presenters
 * @property string $category
 */
class ProductPresenter extends BasePresenter{
  private ProductsFacade $productsFacade;
  private ReviewsFacade $reviewsFacade;
  private ProductCartFormFactory $productCartFormFactory;
  private ProductPhotoFacade $productPhotoFacade;
  private ReviewFormFactory $reviewFormFactory;

  /** @persistent */
  public string $category;

  /**
   * Akce pro zobrazení jednoho produktu
   * @param string $url
   * @throws BadRequestException
   */
  public function renderShow(string $url):void {
    try{
      $product = $this->productsFacade->getProductByUrl($url);
      $modelProductsColors = $this->getModelProductsColors($product->title, $product->ram);
      $modelProductsRams = $this->getModelProductsRams($product->title, $product->color);
      $productRating = 0.0;
      $numberOfRatings = 0;
    
      try {
        $reviews = $this->reviewsFacade->findReviews(['order'=>'rating', 'product_id'=>$product->productId]);
        if (count($reviews)!= 0) {
          $averageRating = 0.0;
          foreach ($reviews as $review) {
            $averageRating += (float)$review->rating;
          }
          $averageRating /= count($reviews);
          $numberOfRatings = count($reviews);
          $productRating =  round($averageRating, 1);
        } else {
          $productRating = 4.5;
          $numberOfRatings = 0;
        }
      } catch (\Exception $e) {
        $productRating = 4.5;
        $numberOfRatings = 0;
      }
    }catch (\Exception $e){
      throw new BadRequestException('Produkt nebyl nalezen.');
    }

    $this->template->product = $product;
    $this->template->modelProductsColors = $modelProductsColors;
    $this->template->modelProductsRams = $modelProductsRams;
    $this->template->rating = $productRating;
    $this->template->numberOfRatings = $numberOfRatings;
    $this->template->reviews = $reviews;
    $this->template->photos = $this->productPhotoFacade->findAllPhotos();
  }

  protected function createComponentProductCartForm():Multiplier {
    return new Multiplier(function($productId){
      $form = $this->productCartFormFactory->create();
      $form->setDefaults(['productId'=>$productId]);
      $form->onSubmit[]=function(ProductCartForm $form){
        try{
          $product = $this->productsFacade->getProduct($form->values->productId);
          //kontrola zakoupitelnosti
        }catch (\Exception $e){
          $this->flashMessage('Produkt nejde přidat do košíku','error');
          $this->redirect('this');
        }
        //přidání do košíku
        /** @var CartControl $cart */
        $cart = $this->getComponent('cart');
        $cart->addToCart($product, (int)$form->values->count);
        $this->redirect('this');
      };

      return $form;
    });
  }
  
  /**
   * Formulář na přidání review
   * @return ReviewForm
   */
  protected function createComponentReviewForm():ReviewForm {
    $form = $this->reviewFormFactory->create();
    
    $form->onSubmit[]=function(ReviewForm $form){
      try{
        $product = $this->productsFacade->getProduct($form->values->productId);
        //TODO kontrola, zdali uživatel může napsat review - zdali má produkt koupený
      }catch (\Exception $e){
        $this->flashMessage('Uživatel nemůže napsat recenzi k produktu, který nezakoupil','error');
        $this->redirect('this');
      }
    };

    $form->onFinished[]=function(ReviewForm $form){
      $this->flashMessage('Recenze byla úspěšně přidána','success');
      $this->redirect('this');
    };
    //TODO

    return $form;
  }

  /**
   * Akce pro vykreslení přehledu produktů
   */
  public function renderList():void {
    //TODO tady by mělo přibýt filtrování podle kategorie, stránkování atp.
    $this->template->products = $this->productsFacade->findProducts(['order'=>'title']);
  }

  /**
   * Akce pro získání produktů se stejným title a ramkou pro výčet barev
   */
  public function getModelProductsColors($title, $ram):array {
    $products =  $this->productsFacade->findProducts(['title'=>$title, 'ram'=>$ram, 'order'=>'color']);

    return $products;
  }

  /**
   * Akce pro získání produktů se stejným title a barvou pro výčet ramek
   */
  public function getModelProductsRams($title, $color):array {
    $products =  $this->productsFacade->findProducts(['title'=>$title, 'color'=>$color, 'order'=>'ram']);

    return $products;
  }

  #region injections
  public function injectProductsFacade(ProductsFacade $productsFacade):void {
    $this->productsFacade=$productsFacade;
  }

  public function injectReviewsFacade(ReviewsFacade $reviewsFacade):void {
    $this->reviewsFacade=$reviewsFacade;
  }

  public function injectProductCartFormFactory(ProductCartFormFactory $productCartFormFactory):void {
    $this->productCartFormFactory=$productCartFormFactory;
  }
  public function injectProductPhotoFacade(ProductPhotoFacade $productPhotoFacade){
      $this->productPhotoFacade=$productPhotoFacade;
  }
  public function injectReviewFormFactory(ReviewFormFactory $reviewFormFactory):void {
    $this->reviewFormFactory=$reviewFormFactory;
  }
  #endregion injections
}