<?php


namespace Cart\Handlers;

use Cart\Handlers\Contracts\HandlerInterface;

/**
 *
 */
class UpdateStock implements HandlerInterface
{

  public function handle($event){

    foreach($event->basket->all() as $product){

       // Set up a decrement query for each product based on the quantity
       $product->decrement('stock', $product->quantity);

    }

  }

}
