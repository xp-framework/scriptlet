<?php namespace xp\scriptlet;

/**
 * Web application configuration
 *
 * @see   xp://xp.scriptlet.WebApplication
 */
class CompositeLayout extends \lang\Object implements WebLayout {
  protected $layouts= [];

  /**
   * Creates a new composite layout
   *
   * @param  xp.scriptlet.WebLayout[] $layout
   */
  public function __construct($layouts) {
    $this->layouts= $layouts;
  }


  /**
   * Gets all mapped applications
   *
   * @param   string profile
   * @return  [:xp.scriptlet.WebApplication]
   * @throws  lang.IllegalStateException if the web is misconfigured
   */
  public function mappedApplications($profile= null) {
    $merged= [];
    foreach ($this->layouts as $layout) {
      $merged= array_merge($merged, $layout->mappedApplications($profile));
    }
    return $merged;
  }

  /**
   * Gets all static resources
   *
   * @param   string profile
   * @return  [:string]
   */
  public function staticResources($profile= null) {
    $merged= [];
    foreach ($this->layouts as $layout) {
      $merged= array_merge($merged, $layout->staticResources($profile));
    }
    return $merged;
  }
}
