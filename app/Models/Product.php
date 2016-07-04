<?php


namespace Cart\Models;

use Illuminate\Database\Eloquent\Model;
use Cart\Models\Order;

/**
 *
 */
class Product extends Model
{
    public $quantity = null;

    public function hasLowStock() {

          if ($this->outOfStock()){
               return false;
          }

          return (bool) ($this->stock <= 5);
    }


    public function outOfStock() {

        return $this->stock == 0;

    }


    public function inStock() {

       return $this->stock > 5;

    }


    public function hasStock($quantity) {

       return $this->stock >= $quantity;

    }


    public function order() {

      return $this->belongsToMany(Order::class,'order_products')->withPivot('quantity');

    }

}
