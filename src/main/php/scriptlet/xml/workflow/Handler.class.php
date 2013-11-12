<?php namespace scriptlet\xml\workflow;



/**
 * BC class for handlers which rely on old form value behavior
 * 
 * @see    https://github.com/xp-framework/xp-framework/issues/55
 * @deprecated
 */
class Handler extends AbstractHandler {
  public $requestOverride= true;
}
