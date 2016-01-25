<?php namespace xp\scriptlet;

/**
 * Layout serving only static content
 *
 * @see   xp://xp.scriptlet.WebApplication
 */
class ServeDocumentRootStatically extends \lang\Object implements WebLayout {

  /**
   * Gets all mapped applications
   *
   * @param   string profile
   * @return  [:xp.scriptlet.WebApplication]
   * @throws  lang.IllegalStateException if the web is misconfigured
   */
  public function mappedApplications($profile= null) {
    return [];
  }

  /**
   * Gets all static resources
   *
   * @param   string profile
   * @return  [:string]
   */
  public function staticResources($profile= null) {
    return ['.*' => '{DOCUMENT_ROOT}'];
  }

  /** @return string */
  public function toString() {
    return nameof($this).'()';
  }
}