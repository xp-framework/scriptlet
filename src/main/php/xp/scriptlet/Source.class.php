<?php namespace xp\scriptlet;

use io\Path;
use util\Properties;
use util\RegisteredPropertySource;
use util\FileSystemPropertySource;
use lang\XPClass;
use lang\IllegalArgumentException;
use lang\ClassLoadingException;

/**
 * Represent the source argument
 *
 * @test  xp://scriptlet.unittest.SourceTest
 */
class Source extends \lang\Object {
  private $site;

  /**
   * Creates a new instance
   *
   * @param  string $source
   * @param  xp.scriptlet.Config $config
   * @throws lang.IllegalArgumentException
   */
  public function __construct($source, Config $config= null) {
    if ('-' === $source) {
      $this->site= new ServeDocumentRootStatically();
    } else if (is_file($source)) {
      $this->site= new WebConfiguration(new Properties($source), $config);
    } else if (is_dir($source)) {
      $this->site= new BasedOnWebroot($source, $config);
    } else {
      $name= ltrim($source, ':');
      try {
        $class= XPClass::forName($name);
      } catch (ClassLoadingException $e) {
        throw new IllegalArgumentException('Cannot load '.$name, $e);
      }

      if ($class->isSubclassOf('scriptlet.Site')) {
        $this->site= $class->newInstance($config);
      } else if ($class->isSubclassOf('xp.scriptlet.WebLayout')) {
        if ($class->hasConstructor()) {
          $this->site= $class->getConstructor()->newInstance([$config]);
        } else {
          $this->site= $class->newInstance();
        }
      } else if ($class->isSubclassOf('scriptlet.HttpScriptlet')) {
        $this->site= new SingleScriptlet($class->getName(), $config);
      } else {
        throw new IllegalArgumentException('Expecting either a scriptlet or a website, '.$class->getName().' given');
      }
    }
  }

  /** @return scriptlet.Site */
  public function site() { return $this->site; }
}