<?php

namespace App\FrontModule\Components\CartControl;

use App\FrontModule\Components\ConfirmationForm\ConfirmationForm;
use App\FrontModule\Components\ConfirmationForm\ConfirmationFormFactory;
use App\Model\Entities\Cart;
use App\Model\Entities\CartItem;
use App\Model\Entities\Product;
use App\Model\Facades\CartFacade;
use Nette\Application\UI\Control;
use Nette\Application\UI\Multiplier;
use Nette\Application\UI\Template;
use Nette\Http\Session;
use Nette\Http\SessionSection;
use Nette\Security\User;

/**
 * Class CartControl
 * @package App\FrontModule\Components\CartControl
 */
class CartControl extends Control
{
  private User $user;
  private SessionSection $cartSession;
  private CartFacade $cartFacade;
  private Cart $cart;
  private ConfirmationFormFactory $confirmationFormFactory;


   /** @var callable[] $onFinished */
   public $onFinished = [];
   /** @var callable[] $onCancel */
   public $onCancel = [];

  /**
   * Akce renderující šablonu s odkazem pro zobrazení košíku
   * @param array $params = []
   */
  public function render($params = []): void
  {
    $template = $this->prepareTemplate('default');
    $template->cart = $this->cart;
    $template->render();
  }

  public function renderList(): void
  {
    $template = $this->prepareTemplate('list');
    $template->cart = $this->cart;
    $template->render();
  }

  public function handleRemove($cartItemId){
    $this->cart->updateCartItems();
    try{
      $this->cartFacade->deleteCartItem($this->cartFacade->getCartItem($cartItemId));
    }catch (\Exception $e){
      //chybu odstranění ignorujeme (položka už tam pravděpodobně není)
    }
    $this->redirect('this');
  }

  public function handleAddCount($cartItemId){
    $this->cart->updateCartItems();
    try{
      $cartItem = $this->cartFacade->getCartItem($cartItemId);
      $cartItem->count++;

      //check skladu
      if ($cartItem->product->stock < $cartItem->count) {
        $cartItem->count = $cartItem->product->stock;
        $this->presenter->flashMessage('Není dostatek zboží na skladě. Bylo přidáno maximální možné množství.', 'warning');
        $this->presenter->redirect('this');
        return;
      }
      $this->cartFacade->saveCartItem($cartItem);
    }catch (\Exception $e){
      //chybu odstranění ignorujeme (položka už tam pravděpodobně není)
    }
    $this->redirect('this');
  }

  public function handleRemoveCount($cartItemId){
    $this->cart->updateCartItems();
    try{
      $cartItem = $this->cartFacade->getCartItem($cartItemId);
      $cartItem->count--;
      if ($cartItem->count<=0){
        $this->cartFacade->deleteCartItem($cartItem);
      }else{
        $this->cartFacade->saveCartItem($cartItem);
      }
    }catch (\Exception $e){
      //chybu odstranění ignorujeme (položka už tam pravděpodobně není)
    }
    $this->redirect('this');
  }

  /**
   * Metoda pro přidání produktu do košíku
   * @param Product $product
   * @param int $count
   */
  public function addToCart(Product $product, int $count): void
  {
    $cartItem = null;
    if (!empty($this->cart->items)) {
      foreach ($this->cart->items as $item) {
        if ($item->product->productId == $product->productId) {
          $cartItem = $item;
          break;
        }
      }
    }

    if (!$cartItem) {
      $cartItem = new CartItem();
      $cartItem->cart = $this->cart;
      $cartItem->product = $product;
    }

    $cartItem->count += $count;
    
    //check skladu
    if ($product->stock == 0){
      $this->presenter->flashMessage('Tento produkt není na skladě.', 'warning');
      $this->presenter->redirect('this');
      return;
    }
    if ($cartItem->count > $product->stock){
      $cartItem->count = $product->stock;
      $this->presenter->flashMessage('Není dostatek zboží na skladě. Bylo přidáno maximální možné množství.', 'warning');
      $this->presenter->redirect('this');
      return;
    }

    $this->cartFacade->saveCartItem($cartItem);
    $this->cartFacade->saveCart($this->cart);
    $this->cart->updateCartItems();
  }

  /**
   * UserLoginControl constructor.
   * @param User $user
   * @param Session $session
   * @param CartFacade $cartFacade
   * @param ConfirmationFormFactory $confirmationFormFactory
   */
  public function __construct(User $user, Session $session, CartFacade $cartFacade, ConfirmationFormFactory $confirmationFormFactory)
  {
    $this->user = $user;
    $this->cartFacade = $cartFacade;
    $this->cartSession = $session->getSection('cart');
    $this->cart = $this->prepareCart();
    $this->confirmationFormFactory = $confirmationFormFactory;
  }

  /**
   * Metoda pro smazání ID košíku ze session
   * TODO tuto metodu by bylo vhodné zavolat např. při odhlášení uživatele
   */
  public function unsetSessionCart(): void
  {
    $this->cartSession->remove('cartId');
  }

  /**
   * Metoda pro smazání již neplatných košíků z databáze
   * TODO tuto metodu je vhodné volat buď cronem, nebo při nějaké pravidelně se opakující události (ale ne při každém načtení stránky); v tomto ukázkovém kódu ji voláme při přípravě nového košíku, ale určitě by šlo najít i vhodnější místo
   */
  public function deleteOldCarts(): void
  {
    $this->cartFacade->deleteOldCarts();
  }

  /**
   * Metoda pro přípravu košíku uloženého v DB
   */
  public function prepareCart(): Cart
  {
    #region zkusíme najít košík podle ID ze session
    try {
      if ($cartId = $this->cartSession->get('cartId')) {
        $cart = $this->cartFacade->getCartById((int)$cartId);
        //zkontrolujeme, jestli tu není košík od předchozího uživatele, nebo se nepřihlásil uživatel s prázdným košíkem (případně ho zahodíme)
        if (($cart->userId || empty($cart->items)) && ($cart->userId != $this->user->id || !$this->user->isLoggedIn())) {
          $cart = null;
        }
      }
    } catch (\Exception $e) {
      /*košík se nepovedlo najít*/
    }
    #endregion zkusíme najít košík podle ID ze session
    #region vyřešíme vazbu košíku na uživatele, případně vytvoříme košík nový
    if ($this->user->isLoggedIn()) {
      if ($cart) {
        //přiřadíme do košíku načteného podle session vazbu na aktuálního uživatele
        if ($cart->userId != $this->user->id) {
          $this->cartFacade->deleteCartByUser($this->user->id);
        }
        $cart->userId = $this->user->id;
        $this->cartFacade->saveCart($cart);
      } else {
        //zkusíme najít košík podle ID uživatele - pokud ho nenajdeme, vytvoříme nový
        try {
          $cart = $this->cartFacade->getCartByUser($this->user->id);
        } catch (\Exception $e) {
          /*košík nebyl pro daného uživatele nalezen*/
          $cart = new Cart();
          $cart->userId = $this->user->id;
          $this->cartFacade->saveCart($cart);
          $this->deleteOldCarts();
        }
      }
    } elseif (!$cart) {
      //košík jsme zatím nijak nezvládli najít, vytvoříme nový prázdný
      $cart = new Cart();
      $this->cartFacade->saveCart($cart);
      $this->deleteOldCarts();
    }
    #endregion vyřešíme vazbu košíku na uživatele, případně vytvoříme košík nový

    //aktualizujeme ID košíku v session
    $this->cartSession->set('cartId', $cart->cartId);

    return $cart;
  }

  protected function createComponentConfirmationForm(): Multiplier {
    return new Multiplier(function($cartItemId) {
      $form = $this->confirmationFormFactory->create();
      $form->setDefaults(['cartItemId' => $cartItemId]);

      $form->onSubmit[] = function(ConfirmationForm $form) {
        if ($form['remove']->isSubmittedBy()) {
          $this->cartFacade->deleteCartItem($this->cartFacade->getCartItem($form->values->cartItemId));
          $this->cart = $this->prepareCart();
          $this->redrawControl('cart');
        } else {
          $this->redirect('this');
        }
      };

      return $form;
    });
  }

  /**
   * Metoda vytvářející šablonu komponenty
   * @param string $templateName=''
   * @return Template
   */
  private function prepareTemplate(string $templateName = ''): Template
  {
    $template = $this->template;
    if (!empty($templateName)) {
      $template->setFile(__DIR__ . '/templates/' . $templateName . '.latte');
    }
    return $template;
  }
}
