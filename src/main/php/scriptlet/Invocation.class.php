<?php namespace scriptlet;

/**
 * HTTP service handler invocation
 */  
class Invocation extends \lang\Object {
  private $handler;
  private $chain;

  /**
   * Creates a new invocation
   *
   * @param  function(scriptlet.Request, scriptlet.Request): bool $handler
   * @param  scriptlet.Filter[] $filters
   */
  public function __construct($handler, $filters= []) {
    $this->handler= $handler;
    $this->chain= $filters;
  }

  /**
   * Proceeds with invocation, eventually returning the scriptlet handler's result
   *
   * @param  scriptlet.Request $request
   * @param  scriptlet.Response $response
   * @return bool
   */
  public function proceed($request, $response) {
    if (empty($this->chain)) {
      $callable= $this->handler;
      return $callable($request, $response);
    }

    $filter= array_shift($this->chain);
    return $filter->filter($request, $response, $this);
  }
}
