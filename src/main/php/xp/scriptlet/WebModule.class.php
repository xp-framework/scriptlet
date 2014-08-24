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
  public abstract function layout();

}