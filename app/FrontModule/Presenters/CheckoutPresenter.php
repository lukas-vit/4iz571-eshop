<?php


namespace App\FrontModule\Presenters;

use App\FrontModule\Components\BillingAddressForm\BillingAddressForm;
use App\FrontModule\Components\BillingAddressForm\BillingAddressFormFactory;
use App\FrontModule\Components\CartControl\CartControl;
use App\FrontModule\Components\DeliveryAddressForm\DeliveryAddressForm;
use App\FrontModule\Components\DeliveryAddressForm\DeliveryAddressFormFactory;
use App\FrontModule\Components\DeliveryForm\DeliveryForm;
use App\FrontModule\Components\DeliveryForm\DeliveryFormFactory;
use App\FrontModule\Components\EmailForm\EmailForm;
use App\FrontModule\Components\EmailForm\EmailFormFactory;
use App\FrontModule\Components\PaymentForm\PaymentForm;
use App\FrontModule\Components\PaymentForm\PaymentFormFactory;
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
    /** @var EmailFormFactory $emailFormFactory */
    private $emailFormFactory;
    /** @var DeliveryFormFactory $deliveryFormFactory */
    private $deliveryFormFactory;
    /** @var DeliveryAddressFormFactory $deliveryAddressFormFactory */
    private $deliveryAddressFormFactory;
    /** @var BillingAddressFormFactory $billingAddressFormFactory */
    private $billingAddressFormFactory;
    /** @var PaymentFormFactory $paymentFormFactory */
    private $paymentFormFactory;
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

    public function __construct(User $user, EmailFormFactory $emailFormFactory, DeliveryFormFactory $deliveryFormFactory, DeliveryAddressFormFactory $deliveryAddressFormFactory, BillingAddressFormFactory $billingAddressFormFactory, PaymentFormFactory $paymentFormFactory, UsersFacade $usersFacade, OrdersFacade $ordersFacade, CartFacade $cartFacade, CartControl $cartControl) {
        parent::__construct();
        $this->emailFormFactory=$emailFormFactory;
        $this->deliveryFormFactory=$deliveryFormFactory;
        $this->deliveryAddressFormFactory=$deliveryAddressFormFactory;
        $this->billingAddressFormFactory=$billingAddressFormFactory;
        $this->paymentFormFactory=$paymentFormFactory;
        $this->usersFacade=$usersFacade;
        $this->ordersFacade=$ordersFacade;
        $this->cartFacade=$cartFacade;
        $this->cartControl=$cartControl;
        $this->cart = $this->cartControl->prepareCart();
        $this->user = $user;
    }

    /**
     * Akce pro zobrazení jednoho produktu
     * @param string $cartId
     * @throws BadRequestException
     */
    public function renderDefault():void {
        $this->template->cart = $this->cart;
    }

    /**
     * Formulář pro zadání emailu
     * @return EmailForm
     */
    protected function createComponentEmailForm():EmailForm {
        $form=$this->emailFormFactory->create();
        $form->onSubmit[]=function(EmailForm $form){
            $values=$form->getValues('array');

           /*  //Check if user is logged in
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
            } */
        };
        return $form;
    }

    /**
     * Formulář pro zadání způsobu dopravy
     * @return DeliveryForm
     */
    protected function createComponentDeliveryForm():DeliveryForm {
        $form=$this->deliveryFormFactory->create();
        $form->onFinished[]=function()use($form){
        $values=$form->getValues('array');
        /* try{
            $this->usersFacade->saveUser($values['name']);
            $this->usersFacade->saveUserAdress($values['']);
        }catch (\Exception $e){
            $this->flashMessage('Nepodařilo se uložit email','danger');
            $this->redirect('this');
        } */

        };
        return $form;
    }

    /**
     * Formulář pro zadání adresy dodání
     * @return DeliveryAddressForm
     */
    protected function createComponentDeliveryAddressForm():DeliveryAddressForm {
        $form=$this->deliveryAddressFormFactory->create();
        $form->onFinished[]=function()use($form){
        $values=$form->getValues('array');
        /* try{
            $this->usersFacade->saveUser($values['name']);
            $this->usersFacade->saveUserAdress($values['']);
        }catch (\Exception $e){
            $this->flashMessage('Nepodařilo se uložit email','danger');
            $this->redirect('this');
        } */

        };
        return $form;
    }

    /**
     * Formulář pro zadání adresy fakturace
     * @return BillingAddressForm
     */
    protected function createComponentBillingAddressForm():BillingAddressForm {
        $form=$this->billingAddressFormFactory->create();
        $form->onFinished[]=function()use($form){
        $values=$form->getValues('array');
        /* try{
            $this->usersFacade->saveUser($values['name']);
            $this->usersFacade->saveUserAdress($values['']);
        }catch (\Exception $e){
            $this->flashMessage('Nepodařilo se uložit email','danger');
            $this->redirect('this');
        } */

        };
        return $form;
    }

    /**
     * Formulář pro zadání způsobu platby
     * @return PaymentForm
     */
    protected function createComponentPaymentForm():PaymentForm {
        $form=$this->paymentFormFactory->create();
        $form->onFinished[]=function()use($form){
        $values=$form->getValues('array');
        /* try{
            $this->usersFacade->saveUser($values['name']);
            $this->usersFacade->saveUserAdress($values['']);
        }catch (\Exception $e){
            $this->flashMessage('Nepodařilo se uložit email','danger');
            $this->redirect('this');
        } */

        };
        return $form;
    }

    public function injectCheckoutFormFactory(EmailFormFactory $emailFormFactory){
        $this->emailFormFactory=$emailFormFactory;
    }

    public function injectDeliveryFormFactory(DeliveryFormFactory $deliveryFormFactory){
        $this->deliveryFormFactory=$deliveryFormFactory;
    }

    public function injectDeliveryAddressFormFactory(DeliveryAddressFormFactory $deliveryAddressFormFactory){
        $this->deliveryAddressFormFactory=$deliveryAddressFormFactory;
    }

    public function injectBillingAddressFormFactory(BillingAddressFormFactory $billingAddressFormFactory){
        $this->billingAddressFormFactory=$billingAddressFormFactory;
    }

    public function injectPaymentFormFactory(PaymentFormFactory $paymentFormFactory){
        $this->paymentFormFactory=$paymentFormFactory;
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