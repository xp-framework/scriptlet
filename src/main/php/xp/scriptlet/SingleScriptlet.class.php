<?php namespace xp\scriptlet;

/**
 * Layout with single scriptlet
 *
 * @see   xp://xp.scriptlet.WebApplication
 */
class SingleScriptlet implements WebLayout {
  private $scriptlet, $config;

  /**
   * Creates a new instance
   *
   * @param  string $scriptlet
   * @param  xp.scriptlet.Config $config
   */
  public function __construct($scriptlet, Config $config= null) {
    $this->scriptlet= $scriptlet;
    $this->config= $config;
  }

  /**
   * Gets all mapped applications
   *
   * @param   string profile
   * @return  [:xp.scriptlet.WebApplication]
   * @throws  lang.IllegalStateException if the web is misconfigured
   */
  public function mappedApplications($profile= null) {
    return ['/' => (new WebApplication('default'))->withScriptlet($this->scriptlet)->withConfig($this->config)];
  }

  /**
   * Gets all static resources
   *
   * @param   string profile
   * @return  [:string]
   */
  public function staticResources($profile= null) {
    return ['^/static' => '{DOCUMENT_ROOT}'];
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.$this->scriptlet.($this->config ? ' << '.$this->config->toString() : '').')';
  }
}