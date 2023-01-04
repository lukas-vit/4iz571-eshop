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

    public function savePhoto(ProductPhoto &$productPhoto):void{
        $this->productPhotoRepository->persist($productPhoto);
    }

    public function savePhotoParameters(FileUpload $fileUpload, ProductPhoto $productPhoto, Product $product):void{
        if ($fileUpload->isOk() && $fileUpload->isImage()){
            $fileExtension=strtolower($fileUpload->getImageFileExtension());
            $productPhoto->photoExtension=$fileExtension;
            $productPhoto->product = $product;
            $productPhoto->productId = $product->productId;
            $this->savePhoto($productPhoto);
        }
    }

    public function deletePhoto(){

    }

    public function findAllPhotos():array{
        return $this->productPhotoRepository->findAll();
    }
}