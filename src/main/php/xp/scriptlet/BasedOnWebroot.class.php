<?php namespace xp\scriptlet;

use util\Properties;
use io\Path;

/**
 * Determines web layout based on web root
 *
 * - If a file etc/web.ini exists, uses layout from that
 * - Otherwise, serves files inside document root statically
 *
 * @see   xp://xp.scriptlet.WebApplication
 */
class BasedOnWebroot extends \lang\Object implements WebLayout {
  private $webroot, $config;
  private $layout= null;

  /**
   * Creates a new instance
   *
   * @param  io.Path|string $webroot
   * @param  xp.scriptlet.Config $config
   */
  public function __construct($webroot, Config $config= null) {
    $this->webroot= $webroot instanceof Path ? $webroot : new Path($webroot);
    $this->config= $config;
  }

  /**
   * Determine layout, cached.
   *
   * @return  xp.scriptlet.WebLayout
   */
  private function determineLayout() {
    if (null === $this->layout) {
      $ini= new Path($this->webroot, 'etc', WebConfiguration::INI);
      if ($ini->exists()) {
        $this->layout= new WebConfiguration(new Properties($ini->toString()), $this->config);
      } else {
        $this->layout= new ServeDocumentRootStatically();
      }
    }
    return $this->layout;
  }

  /**
   * Gets all mapped applications
   *
   * @param  string $profile
   * @return [:xp.scriptlet.WebApplication]
   * @throws lang.IllegalStateException if the web is misconfigured
   */
  public function mappedApplications($profile= null) {
    return $this->determineLayout()->mappedApplications($profile);
  }

  /**
   * Gets all static resources
   *
   * @param  string $profile
   * @return [:string]
   */
  public function staticResources($profile= null) {
    return $this->determineLayout()->staticResources($profile);
  }

  /** @return string */
  public function toString() { return nameof($this).'('.$this->webroot.')'; }
}