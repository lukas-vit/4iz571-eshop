<?php


namespace App\FrontModule\Presenters;

use App\FrontModule\Components\CartControl\CartControl;
use App\FrontModule\Components\CheckoutForm\CheckoutForm;
use App\FrontModule\Components\CheckoutForm\CheckoutFormFactory;
use App\Model\Entities\Cart;
use App\Model\Entities\OrderDetail;
use App\Model\Entities\OrderItem;
use App\Model\Entities\UserAddress;
use App\Model\Entities\User as EntitiesUser;
use Nette\Security\User;
use App\Model\Facades\CartFacade;
use App\Model\Facades\DeliveriesFacade;
use App\Model\Facades\OrdersFacade;
use App\Model\Facades\PaymentsFacade;
use App\Model\Facades\ProductsFacade;
use App\Model\Facades\UsersFacade;
use DateTime;
use Latte\Engine;
use Nette;
use Nette\Application\BadRequestException;
use tFPDF;

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
    /** @var DeliveriesFacade $deliveriesFacade */
    private $deliveriesFacade;
    /** @var PaymentsFacade $paymentsFacade */
    private $paymentsFacade;
    /** @var ProductsFacade $productsFacade */
    private $productsFacade;
    /** @var CartFacade $cartsFacade */
    private $cartFacade;
    /** @var CartControl $cartControl */
    private $cartControl;
    private Cart $cart;
    private User $user;

    public function __construct(User $user, CheckoutFormFactory $checkoutFormFactory, UsersFacade $usersFacade, OrdersFacade $ordersFacade, CartFacade $cartFacade, CartControl $cartControl, DeliveriesFacade $deliveriesFacade, PaymentsFacade $paymentsFacade, ProductsFacade $productsFacade) {
        parent::__construct();
        $this->checkoutFormFactory=$checkoutFormFactory;
        $this->usersFacade=$usersFacade;
        $this->ordersFacade=$ordersFacade;
        $this->cartFacade=$cartFacade;
        $this->deliveriesFacade=$deliveriesFacade;
        $this->paymentsFacade=$paymentsFacade;
        $this->productsFacade=$productsFacade;
        $this->cartControl=$cartControl;
        $this->cart = $this->cartControl->prepareCart();
        $this->user = $user;
    }

    public function renderDefault():void {
        $this->template->cart = $this->cart;
        if ($this->cart->getTotalPrice() == 0.0) {
            $this->redirect("Cart:default");
        }
        $this->template->deliveries = $this->deliveriesFacade->findAllDeliveries();
        $this->template->payments = $this->paymentsFacade->findAllPayments();
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
            
            //create order
            $order = new OrderDetail(); 
            $order->total = $this->cart->getTotalPrice();
            $order->userId = $user->userId;
            $this->ordersFacade->saveOrderDetail($order);

            //create order items
            foreach ($this->cart->getItems() as $item) {
                $orderItem = new OrderItem();
                $orderItem->orderDetailId = $order->orderDetailId;
                $orderItem->productId = $item->product->productId;
                $orderItem->quantity = (int)$item->count;
                $orderItem->price = $item->product->price;
                $this->ordersFacade->saveOrderItem($orderItem);
                //remove 1 stock from DB
                $product = $item->product;
                $product->stock = $product->stock - 1;
                $this->productsFacade->saveProduct($product);
            }

            //delivery method
            $orderDelivery = $this->deliveriesFacade->getDelivery((int)$values['delivery']);
            $order->deliveryId = $orderDelivery->deliveryId;
            //payment method
            $orderPayment = $this->paymentsFacade->getPayment((int)$values['payment']);
            $order->paymentId = $orderPayment->paymentId;
            $order->paymentStatus = "pending";

            //if user does not have an address - create addresses
            $userAddresses = $this->usersFacade->getAddressesByUser($user);
            if (count($userAddresses) == 0) {
                $deliveryAddress = new UserAddress();
                $deliveryAddress->userId = $user->userId;
                $deliveryAddress->name = $values['delivery_name'];
                $deliveryAddress->street = $values['delivery_street'];
                $deliveryAddress->city = $values['delivery_city'];
                $deliveryAddress->zip = $values['delivery_zip'];
                $deliveryAddress->phone = $values['delivery_phone'];
                $deliveryAddress->type = "delivery";
                $this->usersFacade->saveUserAddress($deliveryAddress);

                if ($values['sameAsBilling'] == 0) {
                    $billingAddress = new UserAddress();
                    $billingAddress->userId = $user->userId;
                    $billingAddress->name = $values['billing_name'];
                    $billingAddress->street = $values['billing_street'];
                    $billingAddress->city = $values['billing_city'];
                    $billingAddress->zip = $values['billing_zip'];
                    $billingAddress->phone = $values['billing_phone'];
                    $billingAddress->type = "billing";
                    $this->usersFacade->saveUserAddress($billingAddress);
                } else {
                    $billingAddress = new UserAddress();
                    $billingAddress->userId = $user->userId;
                    $billingAddress->name = $values['delivery_name'];
                    $billingAddress->street = $values['delivery_street'];
                    $billingAddress->city = $values['delivery_city'];
                    $billingAddress->zip = $values['delivery_zip'];
                    $billingAddress->phone = $values['delivery_phone'];
                    $billingAddress->type = "billing";
                    $this->usersFacade->saveUserAddress($billingAddress);
                }
            } else {
                //if user has an address - update addresses
                foreach ($userAddresses as $address) {
                    if ($address->type == "delivery") {
                        $address->name = $values['delivery_name'];
                        $address->street = $values['delivery_street'];
                        $address->city = $values['delivery_city'];
                        $address->zip = $values['delivery_zip'];
                        $address->phone = $values['delivery_phone'];
                        $deliveryAddress = $address;
                        $this->usersFacade->saveUserAddress($deliveryAddress);
                    }

                    if ($address->type == "billing") {
                        $address->name = $values['billing_name'];
                        $address->street = $values['billing_street'];
                        $address->city = $values['billing_city'];
                        $address->zip = $values['billing_zip'];
                        $address->phone = $values['billing_phone'];
                        $billingAddress = $address;
                        $this->usersFacade->saveUserAddress($billingAddress);
                    }
                }
            }
            
            //calculate total
            $orderTotal = $this->cart->getTotalPrice();

            //doprava zdarma nad 20000
            if ($orderTotal < 20000) {
                $orderTotal = $orderTotal + $orderDelivery->price;
            }

            $orderTotal = $orderTotal + $orderPayment->price;

            $order->total = $orderTotal;
            $orderTotalWithoutDph = round(($orderTotal / 1.21), 0);
            $orderDph = round(($orderTotal - $orderTotalWithoutDph), 0);
            $orderCreated = new DateTime();

            if ($orderPayment->type == "cash")
                $order->status = "paid";
            else {
                $order->status = "pending";
            }

            $this->ordersFacade->saveOrderDetail($order);
            
            //delete cart from user
            $this->cartFacade->deleteCartByUser($user);
            //delete cart from session
            $this->cartControl->unsetSessionCart();

            $latte = new Engine;
            $orderItems = $this->ordersFacade->getOrderItemsByOrderDetail($order);
            $params = [
                'order' => $order,
                'deliveryAddress' => $deliveryAddress,
                'payment' => $orderPayment,
                'delivery' => $orderDelivery,
                'orderItems' => $orderItems,
            ];

            $urlToEmailTemplate = __DIR__ . "/templates/email.latte";

            //TODO faktura
            $pdf = new tFPDF();


            $pdf->AddPage();
            
            //set UTF8
            $pdf->AddFont('DejaVu','','DejaVuSansCondensed.ttf',true);
            $pdf->AddFont('DejaVu','B','DejaVuSansCondensed-Bold.ttf',true);
            $pdf->SetFont('DejaVu','',14);

            $pdf->Cell(130 ,5,'Společnost iShop',0,0);
            $pdf->Cell(59 ,5,'FAKTURA',0,1);//end of line

            $pdf->SetFont('DejaVu','',12);

            $pdf->Cell(130 ,5,'Pražská 908/3',0,0);
            $pdf->Cell(59 ,5,'',0,1);//end of line

            $pdf->Cell(130 ,5,'165 01 Praha',0,0);
            $pdf->Cell(25 ,5,'Date',0,0);
            $pdf->Cell(34 ,5,$orderCreated->format('d.m.Y'),0,1);//end of line

            $pdf->Cell(130 ,5,'12345689',0,0);
            $pdf->Cell(25 ,5,'Faktura #',0,0);
            $pdf->Cell(34 ,5,$order->orderDetailId,0,1);//end of line

            $pdf->Cell(189 ,10,'',0,1);//end of line

            //billing address
            $pdf->Cell(100 ,5,'Adresa příjemce',0,1);//end of line

            $pdf->Cell(130 ,5,$deliveryAddress->name,0,0);
            $pdf->Cell(59 ,5,'',0,1);

            $pdf->Cell(130 ,5,$deliveryAddress->street,0,0);
            $pdf->Cell(59 ,5,'',0,1);

            $pdf->Cell(130 ,5,$deliveryAddress->zip .' '. $deliveryAddress->city,0,0);
            $pdf->Cell(59 ,5,'',0,1);

            $pdf->Cell(130 ,5,$deliveryAddress->phone,0,0);
            $pdf->Cell(59 ,5,'',0,1);

            $pdf->Cell(189 ,10,'',0,1);//end of line

            //invoice contents
            $pdf->SetFont('DejaVu','B',12);

            $pdf->Cell(130 ,5,'Název',1,0);
            $pdf->Cell(25 ,5,'Počet',1,0);
            $pdf->Cell(34 ,5,'Cena',1,1);//end of line

            $pdf->SetFont('DejaVu','',12);

            //Numbers are right-aligned so we give 'R' after new line parameter

            foreach($orderItems as $item) {
                $pdf->Cell(130 ,5,$item->product->title,1,0);
                $pdf->Cell(25 ,5,$item->quantity,1,0);
                $pdf->Cell(34 ,5,number_format($item->price, 2, ',', ' ') . ' Kč',1,1,'R');//end of line
            }

            //summary
            $pdf->Cell(130 ,5,'',0,0);
            $pdf->Cell(25 ,5,'Doprava',0,0);
            $pdf->Cell(34 ,5,number_format($orderDelivery->price, 2, ',', ' ') . ' Kč',1,1,'R');//end of line

            $pdf->Cell(130 ,5,'',0,0);
            $pdf->Cell(25 ,5,'Platba',0,0);
            $pdf->Cell(34 ,5,number_format($orderPayment->price, 2, ',', ' ') . ' Kč',1,1,'R');//end of line

            $pdf->Cell(130 ,5,'',0,0);
            $pdf->Cell(25 ,5,'Mezisoučet',0,0);
            $pdf->Cell(34 ,5, number_format($orderTotalWithoutDph, 2, ',', ' ') . ' Kč',1,1,'R');//end of line

            $pdf->Cell(130 ,5,'',0,0);
            $pdf->Cell(25 ,5,'DPH',0,0);
            $pdf->Cell(34 ,5,number_format($orderDph, 2, ',', ' ') . ' Kč',1,1,'R');//end of line

            $pdf->Cell(130 ,5,'',0,0);
            $pdf->Cell(25 ,5,'Celkem',0,0);
            $pdf->Cell(34 ,5,number_format($orderTotal, 2, ',', ' ') . ' Kč',1,1,'R');//end of line

            $invoiceDir = __DIR__ . "/../../../www/invoices/";
            $pdf->Output("F", $invoiceDir . $order->orderDetailId . ".pdf");

            $mail = new Nette\Mail\Message;
            $mail->setFrom('ishop@vse.cz', 'iShop')
                ->setSubject('Potvrzení objednávky')
                ->addTo($user->email)
                ->setHtmlBody(
                    $latte->renderToString($urlToEmailTemplate, $params)
                )
                ->addAttachment($invoiceDir . $order->orderDetailId . ".pdf");
            
            $mailer = new Nette\Mail\SendmailMailer;
            $mailer->send($mail);


            $this->redirect("OrderPlaced:default");
            $this->flashMessage("Objednávka byla úspěšně odeslána.", "success");

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

    public function injectDeliveriesFacade(DeliveriesFacade $deliveriesFacade){
        $this->deliveriesFacade=$deliveriesFacade;
    }

    public function injectPaymentsFacade(PaymentsFacade $paymentsFacade){
        $this->paymentsFacade=$paymentsFacade;
    }

    public function injectProductsFacade(ProductsFacade $productsFacade){
        $this->productsFacade=$productsFacade;
    }

    public function injectCartControl(CartControl $cartControl){
        $this->cartControl=$cartControl;
    }
}