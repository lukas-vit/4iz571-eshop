<?php


namespace App\FrontModule\Presenters;

use App\FrontModule\Components\CartControl\CartControl;
use App\FrontModule\Components\CheckoutForm\CheckoutForm;
use App\FrontModule\Components\CheckoutForm\CheckoutFormFactory;
use App\Model\Entities\Cart;
use App\Model\Entities\OrderDetail;
use App\Model\Entities\User as EntitiesUser;
use Nette\Security\User;
use App\Model\Facades\CartFacade;
use App\Model\Facades\OrdersFacade;
use App\Model\Facades\UsersFacade;
use Nette;
use Nette\Application\BadRequestException;

/**
* Class CheckoutPresenter - presenter pro akce týkající se checkoutu
* @package App\FrontModule\Presenters
*/
class CheckoutPresenter extends BasePresenter{
    /** @var CheckoutFormFactory $checkoutFormFactory */
    private $checkoutFormFactory;
    /** @var UsersFacade $usersFacade */
    private $usersFacade;
    /** @var OrdersFacade $ordersFacade */
    private $ordersFacade;
    /** @var CartFacade $cartsFacade */
    private $cartFacade;
    /** @var CartControl $cartControl */
    private $cartControl;
    private Cart $cart;
    private User $user;

    public function __construct(User $user, CheckoutFormFactory $checkoutFormFactory, UsersFacade $usersFacade, OrdersFacade $ordersFacade, CartFacade $cartFacade, CartControl $cartControl) {
        parent::__construct();
        $this->checkoutFormFactory=$checkoutFormFactory;
        $this->usersFacade=$usersFacade;
        $this->ordersFacade=$ordersFacade;
        $this->cartFacade=$cartFacade;
        $this->cartControl=$cartControl;
        $this->cart = $this->cartControl->prepareCart();
        $this->user = $user;
    }

    public function renderDefault():void {
        $this->template->cart = $this->cart;
        if ($this->cart->getTotalPrice() == 0.0) {
            $this->redirect("Cart:default");
        }
    }

    /**
     * Formulář pro zadání emailu
     * @return CheckoutForm
     */
    protected function createComponentCheckoutForm():CheckoutForm {
        $form=$this->checkoutFormFactory->create();
        $form->onSubmit[]=function(CheckoutForm $form){
            $values=$form->getValues('array');

            //Check if user is logged in
            if ($this->user->isLoggedIn()) {
                $user = $this->usersFacade->getUser($this->user->getId());
            } else {
                try {
                    $user = $this->usersFacade->getUserByEmail($values['email']);
                } catch (\Exception $e) {
                    $user = null;
                }
                if ($user === null) {
                    $user = new EntitiesUser();
                    $user->email = $values['email'];
                    $this->usersFacade->saveUser($user);
                }
            }
            
           
            try {
                $order = new OrderDetail(); 
                //get cart id and total from presenter
                $order->total = $this->cart->getTotalPrice();
                $order->userId = $user->userId;
                $this->ordersFacade->saveOrderDetail($order);
            } catch (\Exception $e) {
                $this->flashMessage('Nepodařilo se uložit email','danger');
                $this->redirect('this');
            }
        };
        return $form;
    }

    public function injectCheckoutFormFactory(CheckoutFormFactory $checkoutFormFactory){
        $this->checkoutFormFactory=$checkoutFormFactory;
    }

    public function injectUsersFacade(UsersFacade $usersFacade){
        $this->usersFacade=$usersFacade;
    }

    public function injectOrdersFacade(OrdersFacade $ordersFacade){
        $this->ordersFacade=$ordersFacade;
    }

    public function injectCartFacade(CartFacade $cartFacade){
        $this->cartFacade=$cartFacade;
    }

    public function injectCartControl(CartControl $cartControl){
        $this->cartControl=$cartControl;
    }
}