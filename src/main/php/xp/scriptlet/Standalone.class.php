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
    $protocol= $this->server->setProtocol(new HttpProtocol(function($host, $method, $query, $status, $error) {
      Console::writeLinef(
        "  \e[33m[%s %d %.3fkB]\e[0m %d %s %s",
        date('Y-m-d H:i:s'),
        getmypid(),
        memory_get_usage() / 1024,
        $status,
        $method,
        $query,
        $error
      );
    }));

    $pm= PropertyManager::getInstance();
    $expand= function($in) use($webroot, $profile, $docroot) {
      return is_string($in) ? strtr($in, [
        '{WEBROOT}'       => $webroot,
        '{PROFILE}'       => $profile,
        '{DOCUMENT_ROOT}' => $docroot
      ]) : $in;
    };
    $layout= (new Source($source, new Config($config, $expand)))->layout();

    Console::writeLine("\e[33m@", $this, "\e[0m");
    Console::writeLine("\e[1mServing ", $layout);
    Console::writeLine("\e[36m", str_repeat('═', 72), "\e[0m");
    Console::writeLine();

    $resources= $layout->staticResources($profile);
    if (null === $resources) {
      $protocol->setUrlHandler('default', '#^/#', new FileHandler(
        $docroot,
        $notFound= function() { return null; }  // Continue to next handler
      ));
    } else {
      foreach ($resources as $pattern => $location) {
        $protocol->setUrlHandler('default', '#'.strtr($pattern, ['#' => '\\#']).'#', new FileHandler($expand($location)));
      }
    }

    foreach ($layout->mappedApplications($profile) as $url => $application) {
      $protocol->setUrlHandler('default', '/' === $url ? '##' : '#^('.preg_quote($url, '#').')($|/.+)#', new ScriptletHandler(
        $application->scriptlet(),
        array_map($expand, $application->arguments()),
        array_map($expand, array_merge($application->environment(), ['DOCUMENT_ROOT' => $docroot])),
        $application->filters()
      ));
      foreach ($application->config()->sources() as $s) {
        $pm->appendSource($s);
      }
    }

    $l= Logger::getInstance();
    $pm->hasProperties('log') && $l->configure($pm->getProperties('log'));
    $cm= ConnectionManager::getInstance();
    $pm->hasProperties('database') && $cm->configure($pm->getProperties('database'));

    Console::writeLine("\e[33;1m>\e[0m Server started: \e[35;4m", $this->url, "\e[0m (", date('r'), ')');
    Console::writeLine('  PID ', getmypid(), '; press Ctrl+C to exit');
    Console::writeLine();

    $this->server->service();
    $this->server->shutdown();
  }
}