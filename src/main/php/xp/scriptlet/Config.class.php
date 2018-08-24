<?php namespace xp\scriptlet;

use lang\ElementNotFoundException;
use lang\FunctionType;
use lang\Primitive;
use util\CompositeProperties;
use util\FilesystemPropertySource;
use util\Objects;
use util\PropertySource;
use util\ResourcePropertySource;
new import('lang.ResourceProvider');

/**
 * The command line for any command allows specifiy explicit ("-c [source]")
 * config sources; implicitely searching either `./etc` or `.` for property
 * files. The `properties()` method then searches these locations.
 *
 * @test  xp://scriptlet.unittest.ConfigTest
 */
class Config implements \lang\Value {
  private static $expansion;
  private $expand;
  private $sources= [];

  static function __static() {
    self::$expansion= new FunctionType([Primitive::$STRING], Primitive::$STRING);
  }

  /**
   * Creates a new config instance from given sources
   *
   * @param  string[]|util.PropertySource[] $sources
   * @param  function(string): string $expand
   */
  public function __construct($sources= [], $expand= null) {
    $this->expand= self::$expansion->cast($expand);
    foreach ($sources as $source) {
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
    } else {
      $resolved= $this->expand ? $this->expand->__invoke($source) : $source;
      if (0 === strncmp('res://', $resolved, 6)) {
        $this->sources[]= new ResourcePropertySource(substr($resolved, 6));
      } else if (is_dir($resolved)) {
        $this->sources[]= new FilesystemPropertySource($resolved);
      } else {
        $this->sources[]= new ResourcePropertySource($resolved);
      }
    }
  }

  /** @return bool */
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

  /**
   * Compares a value to this config instance
   *
   * @param  var $value
   * @return int
   */
  public function compareTo($value) {
    return $value instanceof self ? Objects::compare($this->sources, $value->sources) : 1;
  }

  /** @return string */
  public function hashCode() {
    return 'C'.Objects::hashOf($this->sources);
  }

  /** @return string */
  public function toString() {
    $s= '';
    foreach ($this->sources as $source) {
      $s.= ', '.$source->toString();
    }
    return nameof($this).'['.substr($s, 2).']';
  }
}