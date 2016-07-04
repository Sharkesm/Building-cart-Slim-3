<?php


namespace Cart\Support\Storage\Contracts;

/*
  Contract interface to support cart operations
*/

interface StorageInterface
{
  
    public function get($index);
    public function set($index,$value);
    public function all();
    public function exists($index);
    public function unset($index);
    public function clear();

}
