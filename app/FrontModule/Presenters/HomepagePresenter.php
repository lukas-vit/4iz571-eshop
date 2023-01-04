<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\ProductCartForm\ProductCartForm;
use App\FrontModule\Components\ProductCartForm\ProductCartFormFactory;
use App\Model\Facades\CategoriesFacade;
use App\Model\Facades\ProductPhotoFacade;
use App\Model\Facades\ProductsFacade;
use Nette\Application\UI\Multiplier;

class HomepagePresenter extends BasePresenter{
  private ProductsFacade $productsFacade;
  private CategoriesFacade $categoriesFacade;
  /** @var ProductCartFormFactory $productCartFormFactory */
  private $productCartFormFactory;
  /** @var ProductPhotoFacade $productPhotoFacade */
    private $productPhotoFacade;

  public function __construct(ProductsFacade $productsFacade, CategoriesFacade $categoriesFacade, ProductPhotoFacade $productPhotoFacade)
  {
      $this->productsFacade = $productsFacade;
      $this->categoriesFacade = $categoriesFacade;
      $this->productPhotoFacade = $productPhotoFacade;
  }

  /**
   * Akce pro zobrazení seznamu produktů
   */
  public function renderDefault()
  {
      $this->template->products = $this->productsFacade->findProducts(['order' => 'title']);
      $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
      $this->template->photos = $this->productPhotoFacade->findAllPhotos();
  }

  /**
   * Akce pro úpravu jednoho produktu
   * @param int $id
   * @throws \Nette\Application\AbortException
   */
  public function renderEdit(int $id):void {
    try{
      $product=$this->productsFacade->getProduct($id);
    }catch (\Exception $e){
      $this->flashMessage('Požadovaný produkt nebyl nalezen.', 'error');
      $this->redirect('default');
    }
    $form=$this->getComponent('productEditForm');
    $form->setDefaults($product);
    $this->template->product=$product;
  }

  /**
   * Akce pro zobrazení seznamu produktů
   */
  public function renderSitemap()
  {
      $this->template->products = $this->productsFacade->findProducts(['order' => 'title']);
  }

  /**
   * Formulář na editaci produktů
   * @return ProductCartForm
   */
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

   #region injections
   public function injectProductsFacade(ProductsFacade $productsFacade){
    $this->productsFacade=$productsFacade;
  }
  public function injectProductCartFormFactory(ProductCartFormFactory $productCartFormFactory){
    $this->productCartFormFactory=$productCartFormFactory;
  }
  #endregion injections

}
