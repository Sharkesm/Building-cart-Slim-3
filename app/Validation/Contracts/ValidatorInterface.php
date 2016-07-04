<?php

namespace Cart\Validation\Contracts;

use Psr\Http\Message\RequestInterface as Request;


interface ValidatorInterface
{

  public function validate(Request $request, array $rules);
  public function fails();

}
