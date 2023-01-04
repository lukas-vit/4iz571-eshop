<?php

namespace App\Model\Facades;

use App\Model\Entities\Product;
use App\Model\Entities\ProductPhoto;
use App\Model\Repositories\ProductPhotoRepository;
use App\Model\Repositories\ProductRepository;
use Nette\Http\FileUpload;

/**
 * Class ProductPhotoFacade
 * @package App\Model\Facades
 */
class ProductPhotoFacade{
    private ProductPhotoRepository $productPhotoRepository;
    private ProductRepository $productRepository;

    public function __construct(ProductPhotoRepository $productPhotoRepository, ProductRepository $productRepository){
        $this->productPhotoRepository = $productPhotoRepository;
        $this->productRepository = $productRepository;
    }

    /**
     * Metoda pro uložení fotografie
     * @param ProductPhoto $productPhoto
     * @return void
     */
    public function savePhoto(ProductPhoto &$productPhoto):bool{
        return (bool)$this->productPhotoRepository->persist($productPhoto);
    }

    /**
     * Metoda na vyplnění hodnot fotografie
     * @param FileUpload $fileUpload
     * @param ProductPhoto $productPhoto
     * @param Product $product
     * @return void
     */
    public function savePhotoParameters(FileUpload $fileUpload, ProductPhoto $productPhoto, Product $product):void{
        if ($fileUpload->isOk() && $fileUpload->isImage()){
            $fileExtension=strtolower($fileUpload->getImageFileExtension());
            $productPhoto->photoExtension=$fileExtension;
            $productPhoto->product = $product;
            $productPhoto->productId = $product->productId;
            $this->savePhoto($productPhoto);
        }
    }

    /**
     * Metoda pro odstranění forografie
     * @param ProductPhoto $productPhoto
     * @return bool
     */
    public function deletePhoto(ProductPhoto $productPhoto):bool{
        try {
            return (bool)$this->productPhotoRepository->delete($productPhoto);
        } catch (\Exception $e){
            return false;
        }
    }

    /**
     * Metoda pro nalezení jedné fotografie
     * @param int $id
     * @return ProductPhoto
     * @throws \Exception
     */
    public function getProductPhoto(int $id):ProductPhoto{
        return $this->productPhotoRepository->find($id);
    }

    /**
     * Metoda pro vyhledání všech fotek
     * @return array
     */
    public function findAllPhotos():array{
        return $this->productPhotoRepository->findAll();
    }
}