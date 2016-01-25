<?php namespace xp\scriptlet;

/**
 * Layout with single scriptlet
 *
 * @see   xp://xp.scriptlet.WebApplication
 */
class SingleScriptlet extends \lang\Object implements WebLayout {
  private $scriptlet;

  /**
   * Creates a new instance
   *
   * @param  string $scripltet
   */
  public function __construct($scriptlet) {
    $this->scriptlet= $scriptlet;
  }

  /**
   * Gets all mapped applications
   *
   * @param   string profile
   * @return  [:xp.scriptlet.WebApplication]
   * @throws  lang.IllegalStateException if the web is misconfigured
   */
  public function mappedApplications($profile= null) {
    return ['/' => (new WebApplication('default'))->withScriptlet($this->scriptlet)];
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
    return nameof($this).'('.$this->scriptlet.')';
  }
}