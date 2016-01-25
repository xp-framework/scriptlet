<?php namespace xp\scriptlet;

use util\Properties;
use lang\IllegalStateException;

/**
 * Web application configuration
 *
 * @see   xp://xp.scriptlet.WebApplication
 * @test  xp://scriptlet.unittest.WebConfigurationTest
 */
class WebConfiguration extends \lang\Object implements WebLayout {
  const INI = 'web.ini';

  protected $prop= null;
  
  /**
   * Creates a new web configuration instance
   *
   * @param   util.Properties prop
   */
  public function __construct(Properties $prop) {
    $this->prop= $prop;
  }

  /**
   * Read string. First tries special section "section"@"profile", then defaults 
   * to "section"
   *
   * @param   string profile
   * @param   string section
   * @param   string key
   * @param   var default default NULL
   * @return  string
   */
  protected function readString($profile, $section, $key, $default= null) {
    if (null === ($s= $this->prop->readString($section.'@'.$profile, $key, null))) {
      return $this->prop->readString($section, $key, $default);
    }
    return $s;
  }
  
  /**
   * Read array. First tries special section "section"@"profile", then defaults 
   * to "section"
   *
   * @param   string profile
   * @param   string section
   * @param   string key
   * @param   var default default NULL
   * @return  string[]
   */
  protected function readArray($profile, $section, $key, $default= null) {
    if (null === ($a= $this->prop->readArray($section.'@'.$profile, $key, null))) {
      return $this->prop->readArray($section, $key, $default);
    }
    return $a;
  }
  
  /**
   * Read map. First tries special section "section"@"profile", then defaults 
   * to "section"
   *
   * @param   string profile
   * @param   string section
   * @param   string key
   * @param   var default default NULL
   * @return  [:var]
   */
  protected function readMap($profile, $section, $key, $default= null) {
    if (null === ($h= $this->prop->readMap($section.'@'.$profile, $key, null))) {
      return $this->prop->readMap($section, $key, $default);
    }
    return $h;
  }
  
  /**
   * Creates a web application object from a given configuration section
   *
   * @param   string profile
   * @param   string application app name
   * @param   string url
   * @return  xp.scriptlet.WebApplication
   * @throws  lang.IllegalStateException if the web is misconfigured
   */
  protected function configuredApp($profile, $application, $url) {
    $section= 'app::'.$application;
    if (!$this->prop->hasSection($section)) {
      throw new IllegalStateException('Web misconfigured: Section '.$section.' mapped by '.$url.' missing');
    }

    $app= new WebApplication($application);
    $app->withScriptlet($this->readString($profile, $section, 'class', ''));
    
    $app->withConfig($this->readArray($profile, $section, 'prop-base', '{WEBROOT}/etc'));

    // Determine debug level
    $flags= WebDebug::NONE;
    foreach ($this->readArray($profile, $section, 'debug', []) as $lvl) {
      $flags |= WebDebug::flagNamed($lvl);
    }
    $app->withDebug($flags);
    
    // Initialization arguments
    $app->withArguments($this->readArray($profile, $section, 'init-params', []));
 
    // Environment
    $app->withEnvironment($this->readMap($profile, $section, 'init-envs', []));

    // Filter
    foreach ($this->readArray($profile, $section, 'filters', []) as $filter) {
      $app->withFiter($filter);
    }
   
    return $app;
  }

  /**
   * Gets all mapped applications
   *
   * @param   string profile
   * @return  [:xp.scriptlet.WebApplication]
   * @throws  lang.IllegalStateException if the web is misconfigured
   */
  public function mappedApplications($profile= null) {
    $mappings= $this->prop->readMap('app', 'mappings', null);
    $apps= [];

    // Verify configuration
    if (null === $mappings) {
      foreach ($this->prop->readSection('app') as $key => $url) {
        if (0 !== strncmp('map.', $key, 4)) continue;
        $apps[$url]= $this->configuredApp($profile, substr($key, 4), $url);
      }
    } else {
      foreach ($mappings as $url => $mapping) {
        $apps[$url]= $this->configuredApp($profile, $mapping, $url);
      }
    }

    if (0 === sizeof($apps)) {
      throw new IllegalStateException('Web misconfigured: "app" section missing or broken');
    }

    return $apps;
  }

  /**
   * Gets all static resources
   *
   * @param   string profile
   * @return  [:string]
   */
  public function staticResources($profile= null) {
    return $this->prop->readMap('static', 'resources', null);
  }

  /** @return string */
  public function toString() {
    return nameof($this).'('.$this->prop->toString().')';
  }
}
