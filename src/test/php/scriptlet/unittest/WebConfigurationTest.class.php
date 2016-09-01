<?php namespace scriptlet\unittest;

use util\RegisteredPropertySource;
use xp\scriptlet\WebConfiguration;
use xp\scriptlet\Config;
use scriptlet\HttpScriptlet;
use lang\ClassLoader;
use util\Properties;
use io\streams\MemoryInputStream;

/**
 * TestCase
 *
 * @see   xp://xp.scriptlet.WebConfiguration
 */
class WebConfigurationTest extends \unittest\TestCase {
  private static $scriptlet;

  #[@beforeClass]
  public static function defineScriptlet() {
    self::$scriptlet= ClassLoader::defineClass(self::class.'_Scriptlet', HttpScriptlet::class, [], []);
  }

  /**
   * Creates a web configuration instance
   *
   * @param  util.Properties $properties
   * @return xp.scriptlet.WebConfiguration
   */
  private function newConfiguration($properties) {
    return new WebConfiguration($properties, new Config([]));
  }

  /** @return util.Properties */
  private function newProperties() {
    $p= new Properties(null);
    $p->load(new MemoryInputStream(''));
    return $p;
  }

  #[@test]
  public function configure_with_all_possible_settings() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');
      $p->writeString('app', 'map.service', '/service');

      $p->writeSection('app::service');
      $p->writeString('app::service', 'class', self::$scriptlet);
      $p->writeString('app::service', 'prop-base', '{WEBROOT}/etc/{PROFILE}');
      $p->writeString('app::service', 'init-envs', 'ROLE:admin|CLUSTER:a');
      $p->writeString('app::service', 'init-params', 'a|b');
      $p->writeHash('app::service', 'log-level', [404 => 'warn', 403 => 'error']);

      $p->writeSection('app::service@dev');
      $p->writeString('app::service@dev', 'debug', 'STACKTRACE|ERRORS');

      $this->assertEquals(
        ['/service' => (new \xp\scriptlet\WebApplication('service'))
          ->withConfig('{WEBROOT}/etc/{PROFILE}')
          ->withScriptlet(self::$scriptlet)
          ->withEnvironment(['ROLE' => 'admin', 'CLUSTER' => 'a'])
          ->withDebug(\xp\scriptlet\WebDebug::STACKTRACE | \xp\scriptlet\WebDebug::ERRORS)
          ->withArguments(['a', 'b'])
          ->withLogLevel(404, 'warn')
          ->withLogLevel(403, 'error')
        ],
        $this->newConfiguration($p)->mappedApplications('dev')
      );
    }
  }

  #[@test, @expect(class= 'lang.IllegalArgumentException', withMessage= 'No flag named WebDebug::UNKNOWN')]
  public function configure_with_unknown_debug_flags_raises_exception() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');
      $p->writeString('app', 'map.service', '/service');
      $p->writeSection('app::service');
      $p->writeString('app::service', 'debug', 'UNKNOWN');

      $this->newConfiguration($p)->mappedApplications();
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: "app" section missing or broken')]
  public function configure_with_empty_mappings_raises_exception() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');

      $this->newConfiguration($p)->mappedApplications();
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: "app" section missing or broken')]
  public function configure_with_invalid_mappings_raises_exception() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');
      $p->writeString('app', 'not.a.mapping', 1);

      $this->newConfiguration($p)->mappedApplications();
    }
  }

  #[@test]
  public function old_style_mappings_via_pipe_syntax_supported() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');
      $p->writeString('app', 'mappings', '/service:service|/:global');

      $p->writeSection('app::service');
      $p->writeSection('app::global');

      $this->assertEquals(
        [
          '/service' => (new \xp\scriptlet\WebApplication('service'))->withConfig('{WEBROOT}/etc'), 
          '/'        => (new \xp\scriptlet\WebApplication('global'))->withConfig('{WEBROOT}/etc')
        ],
        $this->newConfiguration($p)->mappedApplications()
      );
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: Section app::service mapped by /service missing')]
  public function old_style_mappings_without_corresponding_section_raises_exception() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');
      $p->writeString('app', 'mappings', '/service:service');

      $this->newConfiguration($p)->mappedApplications();
    }
  }

  #[@test]
  public function mappings() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');
      $p->writeString('app', 'map.service', '/service');
      $p->writeString('app', 'map.global', '/');

      $p->writeSection('app::service');
      $p->writeSection('app::global');

      $this->assertEquals(
        [
          '/service' => (new \xp\scriptlet\WebApplication('service'))->withConfig('{WEBROOT}/etc'), 
          '/'        => (new \xp\scriptlet\WebApplication('global'))->withConfig('{WEBROOT}/etc')
        ],
        $this->newConfiguration($p)->mappedApplications()
      );
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: Section app::service mapped by /service missing')]
  public function mappings_without_corresponding_section_raises_exception() {
    with ($p= $this->newProperties()); {
      $p->writeSection('app');
      $p->writeString('app', 'map.service', '/service');

      $this->newConfiguration($p)->mappedApplications();
    }
  }
}
