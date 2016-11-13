<?php namespace scriptlet;

abstract class Site extends \lang\Object {
  protected $config;

  /** Creates a new instance */
  public function __construct($config) {
    $this->config= $config;
  }

  /** @return [:scriptlet.Route] */
  public abstract function routes();

  /** @return string */
  public function toString() {
    return nameof($this).($this->config ? '@(config= '.$this->config->toString().')' : '');
  }
}