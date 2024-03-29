<?php

namespace App\Model\Facades;

use App\Model\Entities\Product;
use App\Model\Repositories\ProductRepository;
use Nette\Http\FileUpload;
use Nette\Utils\Strings;

/**
 * Class ProductsFacade
 * @package App\Model\Facades
 */
class ProductsFacade{
  private ProductRepository $productRepository;

  /**
   * Metoda pro získání jednoho produktu
   * @param int $id
   * @return Product
   * @throws \Exception
   */
  public function getProduct(int $id):Product {
    return $this->productRepository->find($id);
  }

  /**
   * Metoda pro získání produktu podle URL
   * @param string $url
   * @return Product
   * @throws \Exception
   */
  public function getProductByUrl(string $url):Product {
    return $this->productRepository->findBy(['url'=>$url]);
  }

  /**
   * Metoda pro vyhledání produktů
   * @param array|null $params = null
   * @param int $offset = null
   * @param int $limit = null
   * @return Product[]
   */
  public function findProducts(array $params=null,int $offset=null,int $limit=null):array {
    return $this->productRepository->findAllBy($params,$offset,$limit);
  }

  public function findAllByPartOfTitle(string $partOfTitle):array {
    return $this->productRepository->findAllByPartOfTitle($partOfTitle);
  }

    /**
     * Metoda pro vyhledávání a řazení produktů
     * @param array|null $params
     * @param string $order
     * @param int|null $offset
     * @param int|null $limit
     * @return array
     */
  public function findAndOrderProducts(array $params=null,string $order = 'desc',int $offset=null,int $limit=null):array{
      return$this->productRepository->findAllByAndOrder($params,$order,$offset,$limit);
  }

  /**
   * Metoda pro zjištění počtu produktů
   * @param array|null $params
   * @return int
   */
  public function findProductsCount(array $params=null):int {
    return $this->productRepository->findCountBy($params);
  }

  /**
   * Metoda pro uložení produktu
   * @param Product &$product
   */
  public function saveProduct(Product &$product):void {
    #region URL produktu
    if (empty($product->url)){
      //pokud je URL prázdná, vygenerujeme ji podle názvu produktu
      $baseUrl=Strings::webalize($product->title.'-'.$product->ram.'-'.$product->color);
    }else{
      $baseUrl=$product->url;
    }

    #region vyhledání produktů se shodnou URL (v případě shody připojujeme na konec URL číslo)
    $urlNumber=1;
    $url=$baseUrl;
    $productId = isset($product->productId)?$product->productId:null;
    try{
      while ($existingProduct = $this->getProductByUrl($url)){
        if ($existingProduct->productId==$productId){
          //ID produktu se shoduje => je v pořádku, že je URL stejná
          $product->url=$url;
          break;
        }
        $urlNumber++;
        $url=$baseUrl.$urlNumber;
      }
    }catch (\Exception $e){
      //produkt nebyl nalezen => URL je použitelná
    }
    $product->url=$url;
    #endregion vyhledání produktů se shodnou URL (v případě shody připojujeme na konec URL číslo)
    #endregion URL produktu

    $this->productRepository->persist($product);
  }

    /**
   * Metoda pro smazání produktu
   * @param Product $product
   * @return bool
   */
  public function deleteProduct(Product $product):bool {
    try{
      return (bool)$this->productRepository->delete($product);
    }catch (\Exception $e){
      return false;
    }
  }

  public function findProductsByCategory(int $id):array{
      return $this->productRepository->findAllBy(['category_id'=> $id]);
  }

  public function __construct(ProductRepository $productRepository){
    $this->productRepository=$productRepository;
  }
}