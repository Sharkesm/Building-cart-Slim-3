<?php
// To control all the controllers


 namespace Cart\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Braintree_ClientToken;

class BraintreeController
{
     public function token(Response $response){

        return $response->withJSON([
          'token' => Braintree_ClientToken::generate(),
        ]);

       }
}
