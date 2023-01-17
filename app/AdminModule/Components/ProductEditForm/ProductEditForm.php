<?php

namespace App\AdminModule\Components\ProductEditForm;

use App\Model\Entities\Product;
use App\Model\Entities\ProductPhoto;
use App\Model\Facades\CategoriesFacade;
use App\Model\Facades\ProductPhotoFacade;
use App\Model\Facades\ProductsFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class ProductEditForm
 * @package App\AdminModule\Components\ProductEditForm
 *
 * @method onFinished(string $message = '')
 * @method onFailed(string $message = '')
 * @method onCancel()
 */
class ProductEditForm extends Form{

  use SmartObject;

  /** @var callable[] $onFinished */
  public array $onFinished = [];
  /** @var callable[] $onFailed */
  public array $onFailed = [];
  /** @var callable[] $onCancel */
  public array $onCancel = [];

  private CategoriesFacade $categoriesFacade;
  private ProductsFacade $productsFacade;
  private ProductPhotoFacade $productPhotoFacade;

  /**
   * TagEditForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   * @param CategoriesFacade $categoriesFacade
   * @param ProductsFacade $productsFacade
   * @noinspection PhpOptionalBeforeRequiredParametersInspection
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, CategoriesFacade $categoriesFacade,
                              ProductsFacade $productsFacade, ProductPhotoFacade $productPhotoFacade){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->categoriesFacade=$categoriesFacade;
    $this->productsFacade=$productsFacade;
    $this->productPhotoFacade=$productPhotoFacade;
    $this->createSubcomponents();
  }

  private function createSubcomponents():void {
    $productId=$this->addHidden('productId');
    $this->addText('title','Název produktu')
      ->setRequired('Musíte zadat název produktu')
      ->setMaxLength(100);

    $this->addText('url','URL produktu')
      ->setMaxLength(100)
      ->addFilter(function(string $url){
        return Nette\Utils\Strings::webalize($url);
      })
      ->addRule(function(Nette\Forms\Controls\TextInput $input)use($productId){
        try{
          $existingProduct = $this->productsFacade->getProductByUrl($input->value);
          return $existingProduct->productId==$productId->value;
        }catch (\Exception $e){
          return true;
        }
      },'Zvolená URL je již obsazena jiným produktem');

    #region kategorie
    $categories=$this->categoriesFacade->findCategories();
    $categoriesArr=[];
    foreach ($categories as $category){
      $categoriesArr[$category->categoryId]=$category->title;
    }
    $this->addSelect('categoryId','Kategorie',$categoriesArr)
      ->setPrompt('--vyberte kategorii--')
      ->setRequired(false);
    #endregion kategorie

    $this->addTextArea('description', 'Popis produktu')
      ->setRequired('Zadejte popis produktu.');

    $this->addText('price', 'Cena')
      ->setHtmlType('number')
      ->addRule(Form::NUMERIC,'Musíte zadat číslo.')
      ->setRequired('Musíte zadat cenu produktu');//tady by mohly být další kontroly pro min, max atp.

      $this->addInteger('ram', 'Velikost úlolžiště')
          ->setRequired('Zadejte velikost úlolžiště produktu')
            ->addRule(Form::MIN, 'Číslo musí být větší než 0', '1');

      $this->addText('color', 'Barva')
          ->setRequired('Zadejte barvu produktu');

      $this->addInteger('stock', 'Počet kusů na skladě')
          ->setRequired('Zadejte počet kusů produktu na skladě.')
          ->addRule(Form::MIN, 'Počet kusů nemůže být záporný', '0');

      $this->addInteger('discount', 'Sleva na produkt')
          ->addRule(Form::RANGE, 'Sleva musí být v rozmezí ', [0,100]);

    $this->addCheckbox('available', 'Nabízeno ke koupi')
      ->setDefaultValue(true);

    #region obrazky
      $photosUpload = $this->addMultiUpload('photos', 'Fotografie produktu');
      $photosUpload->addConditionOn($productId, Form::EQUAL, '')
          ->setRequired('Pro uložení nového produktu je nutné nahrát alespoň jednu jeho fotografii');
      $photosUpload->addRule(Form::MAX_LENGTH, 'Maximální počet fotografií, které lze nahrát je 10', 10);
      $photosUpload->addRule(Form::MAX_FILE_SIZE, 'Nahraný soubor je příliš velký', 1000000);
      $photosUpload->addRule(Form::IMAGE, 'Je nutné nahrát obrázky ve formátu JPEG či PNG.');
    #endregion obrazky

    $this->addSubmit('ok','uložit')
      ->onClick[]=function(SubmitButton $button){
        $values=$this->getValues('array');
        if (!empty($values['productId'])){
          try{
            $product=$this->productsFacade->getProduct($values['productId']);
          }catch (\Exception $e){
            $this->onFailed('Požadovaný produkt nebyl nalezen.');
            return;
          }
        }else{
          $product=new Product();
        }
        $product->assign($values,['title','url','description','available', 'ram', 'color', 'stock']);
        if($values['categoryId'] != null){
            $product->category=$this->categoriesFacade->getCategory($values['categoryId']);
        }else{
            $product->category=null;
        }
        $product->discount=floatval($values['discount']/100);
        $product->price=floatval($values['price']);
        $this->productsFacade->saveProduct($product);
        $this->setValues(['productId'=>$product->productId]);

        $productPhotosArr=[];
        foreach ($values['photos'] as $photo){
            if(($photo instanceof Nette\Http\FileUpload) && ($photo->isOk())){
                try{
                    $productPhoto = new ProductPhoto();
                    $this->productPhotoFacade->savePhotoParameters($photo, $productPhoto, $product);
                    $productPhotosArr[]=$productPhoto;
                    $photo->move(__DIR__.'/../../../../www/img/products/'.$productPhoto->productPhotoId.'.'.$productPhoto->photoExtension);
                }catch (\Exception $e){
                    $this->onFailed('Produkt byl uložen, ale nepodařilo se uložit jeho fotky.');
                }
            }
        }

        $thumbnailExists=false;
        $existingPhotos = $this->productPhotoFacade->getProductPhotosByProductId((int)$values['productId']);
        foreach ($existingPhotos as $existingPhoto){
            if($existingPhoto instanceof ProductPhoto){
                if($existingPhoto->isThumbnail){
                    $thumbnailExists=true;
                }
            }
        }

        if(!empty($existingPhotos)){
            if(!$thumbnailExists){
                $newThumbnail=$productPhotosArr[0];
                $newThumbnail->isThumbnail=true;
                $this->productPhotoFacade->savePhoto($newThumbnail);
            }
        }

        $this->onFinished('Produkt byl uložen.');
      };
    $this->addSubmit('storno','zrušit')
      ->setValidationScope([$productId])
      ->onClick[]=function(SubmitButton $button){
        $this->onCancel();
      };
  }

  /**
   * Metoda pro nastavení výchozích hodnot formuláře
   * @param Product|array|object $values
   * @param bool $erase = false
   * @return $this
   */
  public function setDefaults($values, bool $erase = false):self {
    if ($values instanceof Product){
      $values = [
        'productId'=>$values->productId,
        'categoryId'=>$values->category?$values->category->categoryId:null,
        'title'=>$values->title,
        'url'=>$values->url,
        'description'=>$values->description,
        'price'=>$values->price,
        'ram'=>$values->ram,
        'color'=>$values->color,
        'discount'=>($values->discount*100),
        'stock'=>$values->stock
      ];
    }
    parent::setDefaults($values, $erase);
    return $this;
  }

}