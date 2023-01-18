<?php

namespace App\FrontModule\Presenters;

use App\FrontModule\Components\ProductCartForm\ProductCartForm;
use App\FrontModule\Components\ProductCartForm\ProductCartFormFactory;
use App\FrontModule\Components\SearchForm\SearchForm;
use App\FrontModule\Components\SearchForm\SearchFormFactory;
use App\Model\Facades\CategoriesFacade;
use App\Model\Facades\ProductPhotoFacade;
use App\Model\Facades\ProductsFacade;
use App\Model\Facades\ReviewsFacade;
use Nette\Application\UI\Multiplier;
use Nette\Utils\Paginator;

class HomepagePresenter extends BasePresenter{
  private ProductsFacade $productsFacade;
  private CategoriesFacade $categoriesFacade;
  /** @var ProductCartFormFactory $productCartFormFactory */
  private $productCartFormFactory;
  /** @var SearchFormFactory $searchFormFactory */
  private $searchFormFactory;
  /** @var ProductPhotoFacade $productPhotoFacade */
  private $productPhotoFacade;
  /** @var ReviewsFacade $reviewsFacade */
  private $reviewsFacade;

  public function __construct(ProductsFacade $productsFacade, CategoriesFacade $categoriesFacade, ProductPhotoFacade $productPhotoFacade, ReviewsFacade $reviewsFacade)
  {
      parent::__construct();
      $this->productsFacade = $productsFacade;
      $this->categoriesFacade = $categoriesFacade;
      $this->productPhotoFacade = $productPhotoFacade;
      $this->reviewsFacade = $reviewsFacade;
  }

  /**
   * Akce pro zobrazení seznamu produktů
   */
  public function renderDefault(string $sort = null, string $order = null, string $search = null, int $page = 1)
  {
      if($search != null){
          $products = $this->productsFacade->findAllByPartOfTitle($search);
          $this->template->products = $products;
          $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
          $this->template->photos = $this->productPhotoFacade->findAllPhotos();
          $this->template->reviews = $this->reviewsFacade->findReviews();
          $this->template->search = $search;

          //pagination
          $paginator = new Paginator;
          $paginator->setItemCount(count($products));
          $paginator->setItemsPerPage(6);
          $paginator->setPage($this->getParameter('page', 1));
          $this->template->paginator = $paginator;

          $productsOnCurrentPage = array_slice($products, $paginator->getOffset(), $paginator->getLength());
          $this->template->productsOnCurrentPage = $productsOnCurrentPage;
    } elseif($sort != null && $order!=null){
        $products = $this->productsFacade->findAndOrderProducts(['order' => $sort], $order);
        $this->template->products = $products;
        $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
        $this->template->photos = $this->productPhotoFacade->findAllPhotos();
        $this->template->reviews = $this->reviewsFacade->findReviews();
        $this->template->dropDown = $sort.' '.$order;

        //pagination
        $paginator = new Paginator;
        $paginator->setItemCount(count($products));
        $paginator->setItemsPerPage(6);
        $paginator->setPage($this->getParameter('page', 1));
        $this->template->paginator = $paginator;

        $productsOnCurrentPage = array_slice($products, $paginator->getOffset(), $paginator->getLength());
        $this->template->productsOnCurrentPage = $productsOnCurrentPage;
    }else{
        $products = $this->productsFacade->findProducts();
        $this->template->products = $products;
        $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
        $this->template->photos = $this->productPhotoFacade->findAllPhotos();
        $this->template->reviews = $this->reviewsFacade->findReviews();

        //pagination
        $paginator = new Paginator;
        $paginator->setItemCount(count($products));
        $paginator->setItemsPerPage(6);
        $paginator->setPage($this->getParameter('page', 1));
        $this->template->paginator = $paginator;

        $productsOnCurrentPage = array_slice($products, $paginator->getOffset(), $paginator->getLength());
        $this->template->productsOnCurrentPage = $productsOnCurrentPage;
    }
  }

    /**
     * Akce pro zobrazení seznamu produktů dle kategorie
     */
  public function renderCategory(int $id, string $sort = null,string $search = null, string $order = null){

    if($search != null){
      $products = $this->productsFacade->findAllByPartOfTitle($search);
      $this->template->products = $products;
      $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
      $this->template->photos = $this->productPhotoFacade->findAllPhotos();
      $this->template->reviews = $this->reviewsFacade->findReviews();
      $this->template->search = $search;

      //pagination
      $paginator = new Paginator;
      $paginator->setItemCount(count($products));
      $paginator->setItemsPerPage(6);
      $paginator->setPage($this->getParameter('page', 1));
      $this->template->paginator = $paginator;

      $productsOnCurrentPage = array_slice($products, $paginator->getOffset(), $paginator->getLength());
      $this->template->productsOnCurrentPage = $productsOnCurrentPage;
    } elseif($sort != null && $order!=null) {
        $products = $this->productsFacade->findAndOrderProducts(['category_id' => $id, 'order' => $sort], $order);
        $this->template->products = $products;
        $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
        $this->template->photos = $this->productPhotoFacade->findAllPhotos();
        $this->template->currentCategory = $this->categoriesFacade->getCategory($id);
        $this->template->dropDown = $sort.' '.$order;
        //pagination
        $paginator = new Paginator;
        $paginator->setItemCount(count($products));
        $paginator->setItemsPerPage(6);
        $paginator->setPage($this->getParameter('page', 1));
        $this->template->paginator = $paginator;

        $productsOnCurrentPage = array_slice($products, $paginator->getOffset(), $paginator->getLength());
        $this->template->productsOnCurrentPage = $productsOnCurrentPage;
      }else{
          $products = $this->productsFacade->findProductsByCategory($id);
          $this->template->products = $products;
          $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
          $this->template->photos = $this->productPhotoFacade->findAllPhotos();
          $this->template->currentCategory = $this->categoriesFacade->getCategory($id);

          $paginator = new Paginator;
          $paginator->setItemCount(count($products));
          $paginator->setItemsPerPage(6);
          $paginator->setPage($this->getParameter('page', 1));
          $this->template->paginator = $paginator;
  
          $productsOnCurrentPage = array_slice($products, $paginator->getOffset(), $paginator->getLength());
          $this->template->productsOnCurrentPage = $productsOnCurrentPage;
      }
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
   * Formulář na hledání  produktů
   * @return SearchForm
   */
  protected function createComponentSearchForm():SearchForm {

    $form = $this->searchFormFactory->create();
    $form->onSubmit[]=function(SearchForm $form){
    $values=$form->getValues('array');
   
    //find products by title
    $products=$this->productsFacade->findProducts(['title' => $values['title']]);
    $this->template->products=$products;

    //redirect
    $this->redirect('Homepage:default');
    };
    return $form;
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

  public function injectSearchFormFactory(SearchFormFactory $searchFormFactory){
    $this->searchFormFactory=$searchFormFactory;
  }
  #endregion injections

}
