<?php

namespace Cart\Controllers;


use Slim\Router;
use Slim\Views\Twig;
use Cart\Basket\Basket;
use Cart\Models\Product;
use Cart\Models\Customer;
use Cart\Models\Address;
use Cart\Models\Order;
use Braintree_Transaction;
use Cart\Validation\Forms\OrderForm;
use Cart\Validation\Contracts\ValidatorInterface;
use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\RequestInterface as Request;



class OrderController
{

    protected $router;
    protected $basket;
    protected $validator;


    public function __construct(Basket $basket,Router $router,  ValidatorInterface $validator)
    {
      $this->basket = $basket;
      $this->router = $router;
      $this->validator = $validator;
    }



     public function index(Request $request, Response $response, Twig $view,Product $product){

          $this->basket->refresh();

        if ($this->basket->subTotal() !== $this->basket->getStockCost() || !$this->basket->subTotal())
        {
            return $response->withRedirect($this->router->pathFor('cart.index'));
        }


        return $view->render($response,'order/index.twig');
     }



     public function show($hash, Request $request, Response $response, Twig $view, Order $order){


          $order = $order->with(['address','products'])->where('hash',$hash)->first();

          if (!$order){

            return $response->withRedirect($this->router->pathFor('home'));
          }


          return $view->render($response,'order/show.twig',[
            'order' => $order
          ]);

     }



     public function create(Request $request, Response $response)
     {

        $this->basket->refresh();

        if ($this->basket->subTotal() !== $this->basket->getStockCost() || !$this->basket->subTotal())
        {
            return $response->withRedirect($this->router->pathFor('cart.index'));
        }


        if (!$request->getParam('payment_method_nonce')){

          return $response->withRedirect($this->router->pathFor('order.index'));

        }


        // Validating form fields
        $validation = $this->validator->validate($request,OrderForm::rules());


        // Check existence of errors and return
        if ($validation->fails()){

            return $response->withRedirect($this->router->pathFor('order.index'));
        }

        // Regenerate unique hash identity
        $hash = bin2hex(random_bytes(32));


        $customer = Customer::firstOrCreate([
            'email' => $request->getParam('email'),
            'name' => $request->getParam('name'),
        ]);


        $address = Address::firstOrCreate([
            'address1' => $request->getParam('address1'),
            'address2' => $request->getParam('address2'),
            'city' => $request->getParam('city'),
            'postal_code' => $request->getParam('postal_code'),
        ]);


        $order = $customer->orders()->create([
          'hash' => $hash,
          'paid' => false,
          'total' => $this->basket->subTotalAll(),
          'address_id' => $address->id,
        ]);


        $allItems = $this->basket->all();

        $order->products()->saveMany($allItems,$this->getQuantities($allItems));


        // Processing payment
        $result = Braintree_Transaction::sale([
                  'amount' => $this->basket->subTotalAll(),
                  'paymentMethodNonce' => $request->getParam('payment_method_nonce'),
                  'options' => [
                  'submitForSettlement' => True
                  ]
                ]);



        // Build an event handler and pass some dependences as parameters
        $event = new \Cart\Events\OrderWasCreated($order, $this->basket);

        // Conditioning if payment process fails trigger event handler to record failed attempt
        if (!$result->success){

            $event->attach(new \Cart\Handlers\RecordFailPayment);
            $event->dispatch();

            return $response->withRedirect($this->router->pathFor('order.index'));

        }



        $event->attach([
            new \Cart\Handlers\MarkOrderPaid,
            new \Cart\Handlers\RecordPassPayment($result->transaction->id),
            new \Cart\Handlers\UpdateStock,
            new \Cart\Handlers\EmptyBasket
        ]);

        // Send and process events
        $event->dispatch();


        return $response->withRedirect($this->router->pathFor('order.show',[
          'hash' => $hash,
        ]));



     }



     protected function getQuantities($items){

       $quantities = [];

       foreach($items as $item){
         $quantities[] = ['quantity' => $item->quantity];
       }

       return $quantities;

     }





}
