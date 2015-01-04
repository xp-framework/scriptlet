<?php namespace xp\scriptlet;

use util\Properties;
use util\cmd\Console;

/**
 * Inspect scriptlet coniguration
 */
class Inspect extends \lang\Object {

  /**
   * Entry point method. Gets passed the following arguments from "xpws -i":
   *
   * 1. The web root - a directory
   * 2. The application source - either a directory or ":" + f.q.c.Name
   * 3. The server profile - any name, really, defaulting to "dev"
   * 4. The server address - default to "localhost:8080"
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    $webroot= isset($args[0]) ? realpath($args[0]) : getcwd();
    $source= isset($args[1]) ? $args[1] : 'etc';
    $profile= isset($args[2]) ? $args[2] : 'dev';
    $address= isset($args[3]) ? $args[3] : 'localhost:8080';
    Console::writeLine('xpws-', $profile, ' @ ', $address, ', ', $webroot, ' {');

    $layout= (new Source($source))->layout();
    foreach ($layout->mappedApplications($profile) as $url => $app) {
      Console::writeLine('  Route<', $url, '*> => ', \xp::stringOf($app, '  '));
    }
    Console::writeLine('}');
  }
}
