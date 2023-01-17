<?php

namespace App\FrontModule\Components\ReviewForm;

use App\Model\Entities\Review;
use App\Model\Facades\ProductsFacade;
use App\Model\Facades\ReviewsFacade;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\UI\Form;
use Nette\Forms\Controls\SubmitButton;
use Nette\Security\User;
use Nette\SmartObject;
use Nextras\FormsRendering\Renderers\Bs4FormRenderer;
use Nextras\FormsRendering\Renderers\FormLayout;

/**
 * Class ReviewForm
 * @package App\FrontModule\Components\ReviewForm
 * @method onFinished(string $message = '')
 * @method onCancel()
 */
class ReviewForm extends Form{

  use SmartObject;

  private User $user;

  /** @var callable[] $onFinished */
  public $onFinished = [];
  /** @var callable[] $onCancel */
  public $onCancel = [];
  /** @var ReviewsFacade $reviewsFacade */
  private $reviewsFacade;
  /** @var UsersFacade $usersFacade */
  private $usersFacade;
  /** @var ProductsFacade $productsFacade */
  private $productsFacade;

  /**
   * ReviewForm constructor.
   * @param Nette\ComponentModel\IContainer|null $parent
   * @param string|null $name
   */
  public function __construct(Nette\ComponentModel\IContainer $parent = null, string $name = null, ReviewsFacade $reviewsFacade, UsersFacade $usersFacade, ProductsFacade $productsFacade, User $user){
    parent::__construct($parent, $name);
    $this->setRenderer(new Bs4FormRenderer(FormLayout::VERTICAL));
    $this->user = $user;
    $this->reviewsFacade = $reviewsFacade;
    $this->usersFacade = $usersFacade;
    $this->productsFacade = $productsFacade;
    $this->createSubcomponents();
  }
  
  private function createSubcomponents(){
    $this->addHidden('productId');
    $this->addSelect('rating','Hodnocení')
      ->setPrompt('--vyberte hodnocení--')
      ->setItems([
        1 => '⭐',
        2 => '⭐⭐',
        3 => '⭐⭐⭐',
        4 => '⭐⭐⭐⭐',
        5 =>'⭐⭐⭐⭐⭐'
      ])
      ->setRequired('Zadejte prosím hodnocení');
    $this->addTextArea('description','Popis');
    $this->addSubmit('submit','Odeslat hodnocení');
  }
}