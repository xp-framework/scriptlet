<?php namespace scriptlet\unittest;

use lang\IllegalArgumentException;
use xp\scriptlet\Source;
use lang\ClassLoader;
use lang\System;

class SourceTest extends \unittest\TestCase {
  private static $scriptlet, $layout, $dir, $file;

  #[@beforeClass]
  public static function defineScriptlet() {
    self::$scriptlet= ClassLoader::defineClass('scriptlet.unittest.SourceTest_Scriptlet', 'scriptlet.HttpScriptlet', []);
  }

  #[@beforeClass]
  public static function defineLayout() {
    self::$layout= ClassLoader::defineClass('scriptlet.unittest.SourceTest_Layout', 'lang.Object', ['xp.scriptlet.WebLayout'], '{
      public function mappedApplications($profile= null) { /* Intentionally empty */ }
      public function staticResources($profile= null) { /* Intentionally empty */ }
    }');
  }

  #[@beforeClass]
  public static function makeConfigDirectory() {
    self::$dir= realpath(System::tempDir()).DIRECTORY_SEPARATOR.md5(uniqid()).'.xp'.DIRECTORY_SEPARATOR;
    if (is_dir(self::$dir) && !rmdir(self::$dir)) {
      throw new PrerequisitesNotMetError('Fixture directory exists, but cannot remove', null, self::$dir);
    }
    self::$file= self::$dir.'web.ini';

    mkdir(self::$dir);
    file_put_contents(self::$file, '[app]');
  }
 
  #[@afterClass]
  public function removeConfigDirectory() {
    if (is_dir(self::$dir)) {
      unlink(self::$file);
      rmdir(self::$dir);
    }
  }

  #[@test]
  public function from_dash() {
    $this->assertInstanceOf('xp.scriptlet.ServeDocumentRootStatically', (new Source('-'))->layout());
  }

  #[@test]
  public function from_directory() {
    $this->assertInstanceOf('xp.scriptlet.WebConfiguration', (new Source(self::$dir))->layout());
  }

  #[@test]
  public function from_file() {
    $this->assertInstanceOf('xp.scriptlet.WebConfiguration', (new Source(self::$file))->layout());
  }

  #[@test]
  public function from_fully_qualified_scriptlet_name() {
    $this->assertInstanceOf('xp.scriptlet.SingleScriptlet', (new Source(self::$scriptlet->getName()))->layout());
  }

  #[@test]
  public function from_fully_qualified_layout_name() {
    $this->assertInstanceOf(self::$layout, (new Source(self::$layout->getName()))->layout());
  }

  #[@test]
  public function from_fully_qualified_scriptlet_name_bc_with_colon_prefix() {
    $this->assertInstanceOf('xp.scriptlet.SingleScriptlet', (new Source(':'.self::$scriptlet->getName()))->layout());
  }

  #[@test]
  public function from_fully_qualified_layout_name_bc_with_colon_prefix() {
    $this->assertInstanceOf(self::$layout, (new Source(':'.self::$layout->getName()))->layout());
  }

  #[@test, @expect(IllegalArgumentException::class), @values(['lang.Object', ':lang.Object'])]
  public function cannot_create_when_passed_class_which_is_neither_scriptlet_nor_layout($name) {
    (new Source($name))->layout();
  }

  #[@test, @expect(IllegalArgumentException::class), @values(['does.not.exist', ':does.not.exist'])]
  public function cannot_create_when_passed_class_does_not_exist($name) {
    (new Source($name))->layout();
  }

  #[@test, @expect(IllegalArgumentException::class), @values(['', ':'])]
  public function cannot_create_when_passed_class_is_empty($name) {
    (new Source($name))->layout();
  }
}