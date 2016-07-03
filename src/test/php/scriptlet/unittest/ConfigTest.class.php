<?php namespace scriptlet\unittest;
 
use xp\scriptlet\Config;
use util\FilesystemPropertySource;
use util\ResourcePropertySource;
use util\PropertyAccess;
use lang\ElementNotFoundException;

class ConfigTest extends \unittest\TestCase {
  
  #[@test]
  public function can_create() {
    new Config();
  }

  #[@test]
  public function initially_empty() {
    $this->assertTrue((new Config())->isEmpty());
  }

  #[@test]
  public function not_empty_if_created_with_source() {
    $this->assertFalse((new Config(['.']))->isEmpty());
  }

  #[@test]
  public function not_empty_if_created_with_sources() {
    $this->assertFalse((new Config(['.', 'user']))->isEmpty());
  }

  #[@test]
  public function no_longer_empty_after_appending_source() {
    $config= new Config();
    $config->append('.');
    $this->assertFalse($config->isEmpty());
  }

  #[@test]
  public function append_dir() {
    $config= new Config();
    $config->append('.');
    $this->assertEquals([new FilesystemPropertySource('.')], $config->sources());
  }

  #[@test]
  public function append_resource() {
    $config= new Config();
    $config->append('live');
    $this->assertEquals([new ResourcePropertySource('live')], $config->sources());
  }

  #[@test]
  public function append_resource_with_explicit_res_prefix() {
    $config= new Config();
    $config->append('res://live');
    $this->assertEquals([new ResourcePropertySource('live')], $config->sources());
  }

  #[@test]
  public function append_source() {
    $config= new Config();
    $source= new FilesystemPropertySource('.');
    $config->append($source);
    $this->assertEquals([$source], $config->sources());
  }

  #[@test, @expect(ElementNotFoundException::class)]
  public function properties_raises_exception_when_nothing_found() {
    (new Config())->properties('test');
  }

  #[@test]
  public function properties() {
    $config= new Config();
    $config->append('user');
    $this->assertEquals('value', $config->properties('debug')->readString('section', 'key'));
  }

  #[@test]
  public function expand() {
    $config= new Config([], function($in) { return 'res://'.strtolower(substr($in, 1, -1)); });
    $config->append('{USER}');
    $this->assertEquals([new ResourcePropertySource('user')], $config->sources());
  }
}