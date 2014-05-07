<?php namespace scriptlet\unittest;

use unittest\TestCase;
use xp\scriptlet\WebConfiguration;

/**
 * TestCase
 *
 * @see   xp://xp.scriptlet.WebConfiguration
 */
class WebConfigurationTest extends TestCase {

  #[@test]
  public function configure_with_all_possible_settings() {
    with ($p= \util\Properties::fromString('')); {
      $p->writeSection('app');
      $p->writeString('app', 'map.service', '/service');

      $p->writeSection('app::service');
      $p->writeString('app::service', 'class', 'ServiceScriptlet');
      $p->writeString('app::service', 'prop-base', '{WEBROOT}/etc/{PROFILE}');
      $p->writeString('app::service', 'init-envs', 'ROLE:admin|CLUSTER:a');
      $p->writeString('app::service', 'init-params', 'a|b');

      $p->writeSection('app::service@dev');
      $p->writeString('app::service@dev', 'debug', 'STACKTRACE|ERRORS');

      $this->assertEquals(
        ['/service' => (new \xp\scriptlet\WebApplication('service'))
          ->withConfig('{WEBROOT}/etc/{PROFILE}')
          ->withScriptlet('ServiceScriptlet')
          ->withEnvironment(array('ROLE' => 'admin', 'CLUSTER' => 'a'))
          ->withDebug(\xp\scriptlet\WebDebug::STACKTRACE | \xp\scriptlet\WebDebug::ERRORS)
          ->withArguments(array('a', 'b'))
        ],
        (new WebConfiguration($p))->mappedApplications('dev')
      );
    }
  }

  #[@test, @expect(class= 'lang.IllegalArgumentException', withMessage= 'No flag named WebDebug::UNKNOWN')]
  public function configure_with_unknown_debug_flags_raises_exception() {
    with ($p= \util\Properties::fromString('')); {
      $p->writeSection('app');
      $p->writeString('app', 'map.service', '/service');
      $p->writeSection('app::service');
      $p->writeString('app::service', 'debug', 'UNKNOWN');

      (new WebConfiguration($p))->mappedApplications();
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: "app" section missing or broken')]
  public function configure_with_empty_mappings_raises_exception() {
    with ($p= \util\Properties::fromString('')); {
      $p->writeSection('app');

      (new WebConfiguration($p))->mappedApplications();
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: "app" section missing or broken')]
  public function configure_with_invalid_mappings_raises_exception() {
    with ($p= \util\Properties::fromString('')); {
      $p->writeSection('app');
      $p->writeString('app', 'not.a.mapping', 1);

      (new WebConfiguration($p))->mappedApplications();
    }
  }

  #[@test]
  public function old_style_mappings_via_pipe_syntax_supported() {
    with ($p= \util\Properties::fromString('')); {
      $p->writeSection('app');
      $p->writeString('app', 'mappings', '/service:service|/:global');

      $p->writeSection('app::service');
      $p->writeSection('app::global');

      $this->assertEquals(
        array(
          '/service' => (new \xp\scriptlet\WebApplication('service'))->withConfig('{WEBROOT}/etc'), 
          '/'        => (new \xp\scriptlet\WebApplication('global'))->withConfig('{WEBROOT}/etc')
        ),
        (new WebConfiguration($p))->mappedApplications()
      );
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: Section app::service mapped by /service missing')]
  public function old_style_mappings_without_corresponding_section_raises_exception() {
    with ($p= \util\Properties::fromString('')); {
      $p->writeSection('app');
      $p->writeString('app', 'mappings', '/service:service');

      (new WebConfiguration($p))->mappedApplications();
    }
  }

  #[@test]
  public function mappings() {
    with ($p= \util\Properties::fromString('')); {
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
        (new WebConfiguration($p))->mappedApplications()
      );
    }
  }

  #[@test, @expect(class= 'lang.IllegalStateException', withMessage= 'Web misconfigured: Section app::service mapped by /service missing')]
  public function mappings_without_corresponding_section_raises_exception() {
    with ($p= \util\Properties::fromString('')); {
      $p->writeSection('app');
      $p->writeString('app', 'map.service', '/service');

      (new WebConfiguration($p))->mappedApplications();
    }
  }
}
