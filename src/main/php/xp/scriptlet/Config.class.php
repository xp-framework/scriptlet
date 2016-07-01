<?php namespace xp\scriptlet;

use util\PropertySource;
use util\FilesystemPropertySource;
use util\ResourcePropertySource;
use util\CompositeProperties;
use util\Objects;
use lang\ElementNotFoundException;

/**
 * The command line for any command allows specifiy explicit ("-c [source]")
 * config sources; implicitely searching either `./etc` or `.` for property
 * files. The `properties()` method then searches these locations.
 */
class Config {
  private $sources= [];

  /**
   * Creates a new config instance from given sources
   *
   * @param  (string|util.PropertySource)... $source
   */
  public function __construct() {
    foreach (func_get_args() as $source) {
      $this->append($source);
    }
  }

  /**
   * Appends property source
   *
   * @param  string|util.PropertySource $source
   * @return void
   */
  public function append($source) {
    if ($source instanceof PropertySource) {
      $this->sources[]= $source;
    } else if (is_dir($source)) {
      $this->sources[]= new FilesystemPropertySource($source);
    } else if (0 === strncmp('res://', $source, 6)) {
      $this->sources[]= new ResourcePropertySource(substr($source, 6));
    } else {
      $this->sources[]= new ResourcePropertySource($source);
    }
  }

  /** @retun bool */
  public function isEmpty() { return empty($this->sources); }

  /** @return util.PropertySource[] */
  public function sources() { return $this->sources; }

  /**
   * Gets properties
   *
   * @param  string $name
   * @return util.PropertyAccess
   * @throws lang.ElementNotFoundException
   */
  public function properties($name) {
    $found= [];
    foreach ($this->sources as $source) {
      if ($source->provides($name)) {
        $found[]= $source->fetch($name);
      }
    }

    switch (sizeof($found)) {
      case 0: throw new ElementNotFoundException(sprintf(
        'Cannot find properties "%s" in any of %s',
        $name,
        Objects::stringOf($this->sources)
      ));
      case 1: return $found[0];
      default: return new CompositeProperties($found);
    }
  }
}