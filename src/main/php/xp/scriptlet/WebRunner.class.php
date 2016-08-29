<?php namespace xp\scriptlet;

use io\Path;
use lang\XPClass;
use lang\ClassLoader;
use lang\IllegalArgumentException;
use util\cmd\Console;
use util\PropertyManager;
use util\log\Logger;
use util\log\context\EnvironmentAware;
use rdbms\ConnectionManager;
use scriptlet\HttpScriptlet;
new import('lang.ResourceProvider');

/**
 * Web server
 * ==========
 *
 * - Run a webserver [locally](http://localhost:8080/) using the "dev"
 *   profile. If a file named *etc/web.ini* exists, uses configuration
 *   from there, otherwise serves files from the document root *./static*.
 *   ```sh
 *   $ xp web
 *   ```
 * - Only serve static content from given directory
 *   ```sh
 *   $ xp web -r doc_root -
 *   ```
 * - Run a single scriptlet
 *   ```sh
 *   $ xp web com.example.scriptlet.Service
 *   ```
 * - Run a web layout
 *   ```sh
 *   $ xp web com.example.scriptlet.Layout
 *   ```
 * - Run a [configured web layout](xp help web/config)
 *   ```sh
 *   $ xp web etc/web.ini
 *   ```
 * - On Un*x systems, start multiprocess server with 50 children:
 *   ```sh
 *   $ xp web -m prefork,50
 *   ```
 * - Use [event-based I/O](http://pecl.php.net/package/event):
 *   ```sh
 *   $ xp web -m event
 *   ```
 * - Use [development webserver](http://php.net/features.commandline.webserver):
 *   ```sh
 *   $ xp web -m develop
 *   ```
 * The address the server listens to can be supplied via *-a {host}[:{port}]*.
 * The profile can be changed via *-p {profile}* (and can be anything!). One
 * or more configuration sources may be passed via *-c {file.ini|dir}*.
 */
class WebRunner {
  private static $modes= [
    'serve'   => 'xp.scriptlet.Serve',
    'prefork' => 'xp.scriptlet.Prefork',
    'fork'    => 'xp.scriptlet.Fork',
    'event'   => 'xp.scriptlet.Event',
    'develop' => 'xp.scriptlet.Develop'
  ];

  /**
   * Creates a server instance
   *
   * @param  string $mode
   * @param  string $address
   * @param  string[] $arguments
   * @return peer.server.Server
   * @throws lang.IllegalArgumentException
   */
  private static function server($mode, $address, $arguments) {
    if (!isset(self::$modes[$mode])) {
      throw new IllegalArgumentException(sprintf(
        'Unkown server mode "%s", supported: [%s]',
        $mode,
        implode(', ', array_keys(self::$modes))
      ));
    }

    $p= strpos($address, ':', '[' === $address{0} ? strpos($address, ']') : 0);
    if (false === $p) {
      $host= $address;
      $port= 8080;
    } else {
      $host= substr($address, 0, $p);
      $port= (int)substr($address, $p + 1);
    }

    return XPClass::forName(self::$modes[$mode])->getConstructor()->newInstance(array_merge(
      [$host, $port],
      $arguments
    ));
  }

  /**
   * Entry point method
   *
   * @param  string[] $args
   * @return int
   */
  public static function main(array $args) {
    $webroot= new Path(getcwd());
    $docroot= new Path($webroot, 'static');
    $address= 'localhost:8080';
    $profile= getenv('SERVER_PROFILE') ?: 'dev';
    $mode= 'serve';
    $arguments= [];
    $config= [];
    $source= '.';

    for ($i= 0; $i < sizeof($args); $i++) {
       if ('-r' === $args[$i]) {
        $docroot= $webroot->resolve($args[++$i]);
      } else if ('-a' === $args[$i]) {
        $address= $args[++$i];
      } else if ('-p' === $args[$i]) {
        $profile= $args[++$i];
      } else if ('-c' === $args[$i]) {
        $config[]= $args[++$i];
      } else if ('-m' === $args[$i]) {
        $arguments= explode(',', $args[++$i]);
        $mode= array_shift($arguments);
      } else if ('-s' === $args[$i]) {
        $source= $args[++$i];
      } else {
        $source= $args[$i];
        break;
      }
    }

    $server= self::server($mode, $address, $arguments);
    $server->serve($source, $profile, $webroot, $webroot->resolve($docroot), $config);
    return 0;
  }
}