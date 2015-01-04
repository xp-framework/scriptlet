<?php namespace xp\scriptlet;

use io\Path;
use util\Properties;
use lang\XPClass;
use lang\IllegalArgumentException;
use lang\CLassLoadingException;

/**
 * Represent the source argument
 *
 * @test  xp://scriptlet.unittest.SourceTest
 */
class Source extends \lang\Object {

  /**
   * Creates a new instance
   *
   * @param  string $source
   * @throws lang.IllegalArgumentException
   */
  public function __construct($source) {
    if ('-' === $source) {
      $this->layout= newinstance('xp.scriptlet.WebLayout', [], [
        'mappedApplications' => function($profile= null) {
          return [];
        },
        'staticResources' => function($profile= null) {
          return ['^/' => '{DOCUMENT_ROOT}'];
        }
      ]);
    } else if (':' === $source{0}) {
      $name= substr($source, 1);
      try {
        $class= XPClass::forName($name);
      } catch (CLassLoadingException $e) {
        throw new IllegalArgumentException('Cannot load '.$name, $e);
      }

      if ($class->isSubclassOf('scriptlet.HttpScriptlet')) {
        $this->layout= new SingleScriptlet($class->getName());
      } else if ($class->isSubclassOf('xp.scriptlet.WebLayout')) {
        $this->layout= $class->newInstance();
      } else {
        throw new IllegalArgumentException('Expecting either a scriptlet, a weblayout or a config file');
      }
    } else {
      $this->layout= new WebConfiguration(new Properties((new Path($source, 'web.ini'))->toString()));
    }
  }

  /** @return xp.scriptlet.WebLayout */
  public function layout() { return $this->layout; }
}