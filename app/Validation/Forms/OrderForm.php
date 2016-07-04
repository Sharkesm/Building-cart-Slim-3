<?php


namespace Cart\Validation\Forms;

use Respect\Validation\Validator as V;

/**
 *
 */
class OrderForm
{

    public static function rules(){

       return [
         'email' => V::email(),
         'name' => V::alpha(' '),
         'address1' => V::alnum(' -'),
         'address2' => V::optional(V::alnum(' -')),
         'city' => V::alpha(' '),
         'postal_code' => V::alnum(' '),
       ];

    }
}
