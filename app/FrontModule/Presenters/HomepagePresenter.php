<?php

namespace App\FrontModule\Presenters;

use App\Model\Facades\CategoriesFacade;
use App\Model\Facades\ProductsFacade;

class HomepagePresenter extends BasePresenter{
  private ProductsFacade $productsFacade;
  private CategoriesFacade $categoriesFacade;

  public function __construct(ProductsFacade $productsFacade, CategoriesFacade $categoriesFacade)
  {
      $this->productsFacade = $productsFacade;
      $this->categoriesFacade = $categoriesFacade;
  }

  /**
   * Akce pro zobrazení seznamu produktů
   */
  public function renderDefault()
  {
      $this->template->products = $this->productsFacade->findProducts(['order' => 'title']);
      $this->template->categories = $this->categoriesFacade->findCategories(['order' => 'title']);
  }
}
