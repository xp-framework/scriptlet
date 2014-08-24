<?php namespace xp\scriptlet;

/**
 * Web application configuration
 */
interface WebLayout {

  /**
   * Gets all mapped applications
   *
   * @param   string profile
   * @return  [:xp.scriptlet.WebApplication]
   * @throws  lang.IllegalStateException if the web is misconfigured
   */
  public function mappedApplications($profile= null);

  /**
   * Gets all static resources
   *
   * @param   string profile
   * @return  [:string]
   */
  public function staticResources($profile= null);
}
