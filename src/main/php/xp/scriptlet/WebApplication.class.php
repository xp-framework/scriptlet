<?php namespace xp\scriptlet;

use scriptlet\Filter;
use lang\XPClass;

/**
 * Represents a web application
 *
 * @see      xp://xp.scriptlet.WebDebug
 * @see      xp://xp.scriptlet.Runner
 */
class WebApplication extends \lang\Object {
  protected $name;
  protected $config = null;
  protected $scriptlet = null;
  protected $arguments = [];
  protected $filters = [];
  protected $environment = [];
  protected $debug = 0;

  /**
   * Creates a new web application named by the given name
   *
   * @param   string $name
   */
  public function __construct($name) {
    $this->name= $name;
  }

  /**
   * Sets this application's name
   *
   * @param   string $name
   * @return  self this
   */
  public function withName($name) {
    $this->name= $name;
    return $this;
  }
  
  /**
   * Returns this application's name
   *
   * @return  string
   */
  public function name() {
    return $this->name;
  }

  /**
   * Sets this application's config
   *
   * @param   xp.scriptlet.Config|string|string[] $config
   * @return  self this
   */
  public function withConfig($config) {
    if ($config instanceof Config) {
      $this->config= $config;
    } else if (is_array($config)) {
      $this->config= new Config($config);
    } else {
      $this->config= new Config([$config]);
    }
    return $this;
  }
  
  /**
   * Returns this application's config
   *
   * @return  xp.scriptlet.Config
   */
  public function config() {
    return $this->config ?: new Config();
  }

  /**
   * Sets this application's debug flags
   *
   * @param   int $debug
   * @return  self this
   */
  public function withDebug($debug) {
    $this->debug= $debug;
    return $this;
  }
  
  /**
   * Returns this application's debug flags
   *
   * @return  int
   */
  public function debug() {
    return $this->debug;
  }

  /**
   * Sets this application's scriptlet class name
   *
   * @param   string|lang.XPClass $scriptlet
   * @return  self this
   */
  public function withScriptlet($scriptlet) {
    if (null === $scriptlet) {
      $this->scriptlet= null;
    } else if ($scriptlet instanceof XPClass) {
      $this->scriptlet= $scriptlet;
    } else {
      $this->scriptlet= XPClass::forName($scriptlet);
    }
    return $this;
  }
  
  /**
   * Returns this application's scriptlet class
   *
   * @return  lang.XPClass
   */
  public function scriptlet() {
    return $this->scriptlet;
  }

  /**
   * Sets this application's arguments
   *
   * @param   string[] $arguments
   * @return  self this
   */
  public function withArguments($arguments) {
    $this->arguments= $arguments;
    return $this;
  }
  
  /**
   * Returns this application's arguments
   *
   * @return  string[]
   */
  public function arguments() {
    return $this->arguments;
  }

  /**
   * Sets this application's filter class name
   *
   * @param   scriptlet.Filter|string $filter Either a filter instance or a filter class name
   * @return  xp.filter.WebApplication this
   */
  public function withFilter($filter) {
    if ($filter instanceof Filter) {
      $this->filters[]= $filter;
    } else {
      $this->filters[]= XPClass::forName($filter)->newInstance();
    }
    return $this;
  }

  /**
   * Returns this application's filters
   *
   * @return  scriptlet.Filter[]
   */
  public function filters() {
    return $this->filters;
  }

  /**
   * Sets this application's environment
   *
   * @param   [:string] $environment
   * @return  self this
   */
  public function withEnvironment($environment) {
    $this->environment= $environment;
    return $this;
  }
  
  /**
   * Returns this application's environment
   *
   * @return  [:string]
   */
  public function environment() {
    return $this->environment;
  }
  
  /**
   * Creates a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    return sprintf(
      "%s(%s)@{\n".
      "  [config       ] %s\n".
      "  [scriptlet    ] %s\n".
      "  [debug        ] %s\n".
      "  [arguments    ] [%s]\n".
      "  [environment  ] %s\n".
      "}",
      nameof($this),
      $this->name,
      $this->config ? strtr($this->config->toString(), ["\n" => "\n  "]) : '(none)',
      $this->scriptlet ? $this->scriptlet->getName() : '(none)',
      implode(' | ', WebDebug::namesOf($this->debug)),
      implode(', ', $this->arguments),
      \xp::stringOf($this->environment, '  ')
    );
  }
  
  /**
   * Returns whether another object is equal to this
   *
   * @param   lang.Generic $cmp
   * @return  bool
   */
  public function equals($cmp) {
    return (
      $cmp instanceof self && 
      $this->name === $cmp->name && 
      $this->debug === $cmp->debug && 
      $this->arguments === $cmp->arguments &&
      $this->environment === $cmp->environment &&
      $this->scriptlet === null ? null === $cmp->scriptlet : $this->scriptlet->equals($cmp->scriptlet) &&
      0 === $this->config->compareTo($cmp->config)
    );
  }
}
