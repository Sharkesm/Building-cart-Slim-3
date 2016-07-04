<?php


namespace Cart\Handlers;

use Cart\Handlers\Contracts\HandlerInterface;

/**
 *
 */
class RecordFailPayment implements HandlerInterface
{

  public function handle($event){

    $event->order->payment()->create([
          'failed' => true
    ]);

    

  }

}
