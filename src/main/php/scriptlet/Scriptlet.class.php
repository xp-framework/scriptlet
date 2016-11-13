<?php namespace scriptlet;

class Scriptlet implements Routing {

  /** Creates a new instance */
  public function __construct($arg) {
    $this->instance= $arg;
    $this->instance->init();
  }

  public function route($request, $response) {
    $this->instance->service($request, $response);
  }
}