<?php namespace xp\scriptlet;

use util\cmd\Console;
use util\log\Logger;
use util\PropertyManager;
use util\ResourcePropertySource;
use util\FilesystemPropertySource;
use rdbms\ConnectionManager;

abstract class Standalone extends \lang\Object {
  private $server, $url;

  public function __construct($server, $url) {
    $this->server= $server;
    $this->url= $url;
  }

  /** @return string */
  public function toString() {
    return nameof($this).'(HTTP @ '.$this->server->socket->toString().')';
  }

  /**
   * Serve requests
   *
   * @param  string $source
   * @param  string $profile
   * @param  io.Path $webroot
   * @param  io.Path $docroot
   * @param  string[] $config
   */
  public function serve($source, $profile, $webroot, $docroot, $config) {
    $this->server->init();
    $protocol= $this->server->setProtocol(new HttpProtocol(function($request, $response, $message= null) {
      Console::writeLinef(
        "  \e[33m[%s %d %.3fkB]\e[0m %d %s %s %s",
        date('Y-m-d H:i:s'),
        getmypid(),
        memory_get_usage() / 1024,
        $response->statusCode,
        $request->getMethod(),
        $request->getURL()->getPath(),
        $message
      );
    }));

    $expand= [
      '{WEBROOT}'       => $webroot,
      '{PROFILE}'       => $profile,
      '{DOCUMENT_ROOT}' => $docroot
    ];
    $site= (new Source($source, new Config($config, $expand)))->site();

    Console::writeLine("\e[33m@", $this, "\e[0m");
    Console::writeLine("\e[1mServing site ", $site);
    Console::writeLine("\e[36m", str_repeat('═', 72), "\e[0m");
    Console::writeLine();

    foreach ($site->routes() as $pattern => $route) {
      $protocol->install($pattern, $route);
    }

    Console::writeLine("\e[33;1m>\e[0m Server started: \e[35;4m", $this->url, "\e[0m (", date('r'), ')');
    Console::writeLine('  PID ', getmypid(), '; press Ctrl+C to exit');
    Console::writeLine();

    $this->server->service();
    $this->server->shutdown();
  }
}