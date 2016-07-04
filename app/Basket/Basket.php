<?php

namespace Cart\Basket;

use Cart\Models\Product;
use Cart\Support\Storage\Contracts\StorageInterface;
use Cart\Basket\Exceptions\QuantityExceededException;
/**
 *
 */
class Basket
{

    protected $storage;
    protected $product;
    protected $stockCost;


    public function __construct(StorageInterface $storage, Product $product)
    {
        $this->storage = $storage;

        $this->product = $product;
    }


/**
- Conditioning to check whether product exist into storage container and update current quantity
- Update the storage container with new product and quantity specified
**/

    public function add(Product $product,$quantity)
    {
          if ($this->has($product))
          {
              $quantity = $this->get($product)['quantity'] + $quantity;
          }

          $this->update($product,$quantity);
    }




/**
- Conditioning to check whether a product of choice has stock available Else throw exception error
- Conditioning if quantity of choice if equals 0 remove product from storage container
- Add new product into storage container and add extra product info inlcluding product id and quantity
**/
    public function update(Product $product, $quantity)
    {
          if (!$this->product->find($product->id)->hasStock($quantity))
          {

            throw new QuantityExceededException;

          }


          if ((int)$quantity === 0)
          {
             $this->remove($product);
             return;
          }


          $this->storage->set($product->id,[
            'product_id' => (int) $product->id,
            'quantity' => (int) $quantity
          ]);
    }




/**
- Removes a certain product from storage container
**/

    public function remove(Product $product)
    {
        $this->storage->unset($product->id);
    }



/**
- Check if certain product exist into Storage container
**/

    public function has(Product $product)
    {
        return $this->storage->exists($product->id);
    }




/**
- Fetch a certain product into storage container
**/

    public function get(Product $product)
    {
      return $this->storage->get($product->id);
    }



/**
- Clear the storage container
**/

    public function clear()
    {
        return $this->storage->clear();
    }



/**
- Returns all stored items into the basket by hitting through the product model
**/

    public function all()
    {

        $ids = [];
        $items = [];

        foreach($this->storage->all() as $product)
        {
          $ids[] = $product['product_id'];
        }

        $products = $this->product->find($ids);

        foreach($products as $product)
        {
          $product->quantity = $this->storage->get($product->id)['quantity'];
        }

        $items = $products;
        return $items;
    }



/**
- Count the number of items stored
**/

    public function itemCount()
    {

      return $this->storage->count();

    }



/**
- Conditioning if item still has some stock left else continue looping and add populate grand cost
- Returning item sub cost
**/


   public function subTotal()
   {
       $total = 0;

       foreach($this->all() as $item)
       {
            if ($item->outOfStock())
            {
               continue;
            }

            $total = $total + ($item->price * $item->quantity);
       }

      $this->stockCost =  (int) $total;

      return (int)$total;

   }




   public function subTotalAll($shippingCost = 5){

        return (int) $this->subTotal() + $shippingCost;
   }



/**
 - Getter property that returns stock total cost
**/

   public function getStockCost()
   {
       return $this->stockCost;
   }




   public function refresh()
   {

     foreach($this->all() as $item)
     {
        if ($item->hasStock($item->quantity)){

          $this->update($item, $item->quantity);

        } else if ($item->hasStock(1) && $item->quantity === 0){

           $this->update($item,1);
        }

     }
   }



}
