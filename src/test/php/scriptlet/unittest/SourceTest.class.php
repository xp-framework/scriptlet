<?php namespace scriptlet\unittest;

use xp\scriptlet\Source;
use lang\ClassLoader;

class SourceTest extends \unittest\TestCase {
  private static $scriptlet, $layout;

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

  #[@test]
  public function from_dash() {
    $this->assertInstanceOf('xp.scriptlet.WebLayout', (new Source('-'))->layout());
  }

  #[@test]
  public function from_directory() {
    $this->assertInstanceOf('xp.scriptlet.WebConfiguration', (new Source('etc'))->layout());
  }

  #[@test]
  public function from_fully_qualified_scriptlet_name() {
    $this->assertInstanceOf('xp.scriptlet.WebLayout', (new Source(':'.self::$scriptlet->getName()))->layout());
  }

  #[@test]
  public function from_fully_qualified_layout_name() {
    $this->assertInstanceOf(self::$layout, (new Source(':'.self::$layout->getName()))->layout());
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_create_when_passed_class_which_is_neither_scriptlet_nor_layout() {
    (new Source(':lang.Object'))->layout();
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_create_when_passed_class_does_not_exist() {
    (new Source(':does.not.exist'))->layout();
  }

  #[@test, @expect('lang.IllegalArgumentException')]
  public function cannot_create_when_passed_class_is_empty() {
    (new Source(':'))->layout();
  }
}