<?php namespace xp\scriptlet;

use io\Path;
use lang\XPClass;
use lang\ClassLoader;
use lang\IllegalArgumentException;
use util\cmd\Console;
use util\PropertyManager;
use util\Properties;
use util\ResourcePropertySource;
use util\FilesystemPropertySource;
use util\log\Logger;
use util\log\context\EnvironmentAware;
use rdbms\ConnectionManager;
use peer\server\PreforkingServer;
use peer\server\ForkingServer;
use peer\server\EventServer;
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
 * The address the server listens to can be supplied via *-a {host}:{port}*.
 * The profile can be changed via *-p {profile}* (and can be anything!).
 */
class WebRunner {
  private static $modes= [
    'serve'   => Serve::class,
    'prefork' => PreforkingServer::class,
    'fork'    => ForkingServer::class,
    'event'   => EventServer::class,
    'develop' => Develop::class
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
    if (!($class= @self::$modes[$mode])) {
      throw new IllegalArgumentException(sprintf(
        'Unkown server mode "%s", supported: [%s]',
        $mode,
        implode(', ', array_keys(self::$modes))
      ));
    }

    sscanf($address, '%[^:]:%d', $host, $port);
    return (new XPClass($class))->getConstructor()->newInstance(array_merge(
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
    $layout= new BasedOnWebroot($webroot);

    $docroot= 'static';
    $address= 'localhost:8080';
    $profile= 'dev';
    $mode= 'serve';
    $arguments= [];

    $cl= ClassLoader::getDefault();
    for ($i= 0; $i < sizeof($args); $i++) {
       if ('-r' === $args[$i]) {
        $docroot= $args[++$i];
      } else if ('-a' === $args[$i]) {
        $address= $args[++$i];
      } else if ('-p' === $args[$i]) {
        $profile= $args[++$i];
      } else if ('-m' === $args[$i]) {
        $arguments= explode(',', $args[++$i]);
        $mode= array_shift($arguments);
      } else if ('-' === $args[$i]) {
        $layout= new ServeDocumentRootStatically();
      } else if (is_file($args[$i])) {
        $layout= new WebConfiguration(new Properties($args[$i]));
      } else if (is_dir($args[$i])) {
        $layout= new WebConfiguration(new Properties(new Path($args[$i], WebConfiguration::INI)));
      } else if ($cl->providesClass($args[$i])) {
        $class= $cl->loadClass($args[$i]);
        if ($class->isSubclassOf(HttpScriptlet::class)) {
          $layout= new SingleScriptlet($class->getName());
        } else if ($class->isSubclassOf(WebLayout::class)) {
          $layout= $class->newInstance();
        } else {
          throw new IllegalArgumentException('Expecting either a scriptlet or a weblayout, '.$class->getName().' given');
        }
      }
    }

    $server= self::server($mode, $address, $arguments);

    Console::writeLine("\e[33m@", $server, "\e[0m");
    Console::writeLine("\e[1mServing ", $layout);
    Console::writeLine("\e[36m", str_repeat('═', 72), "\e[0m");
    Console::writeLine();

    $server->serve($layout, $webroot, $profile, $webroot->resolve($docroot));
    return 0;
  }
}