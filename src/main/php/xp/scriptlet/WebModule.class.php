<?php namespace xp\scriptlet;

abstract class WebModule extends \lang\reflect\Module {
  public static $loaded= [];

  /**
   * Initialize this module
   */
  public function initialize() {
    self::$loaded[]= $this;
  }

  /** @return xp.scriptlet.WebLayout */
  protected abstract function webLayout();

  /**
   * Bind
   *
   * @param  inject.Injector $inject
   */
  public function bind($inject) {
    $inject->bind('xp.scriptlet.WebLayout', $this->webLayout());
  }
}