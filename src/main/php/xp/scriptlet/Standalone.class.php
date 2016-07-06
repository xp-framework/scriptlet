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
   * @param  xp.scriptlet.WebLayout $layout
   * @param  string $profile
   * @param  string $webroot
   * @param  string $docroot
   */
  public function serve(WebLayout $layout, $profile, $webroot, $docroot) {
    $this->server->init();
    $protocol= $this->server->setProtocol(new HttpProtocol(function($host, $method, $query, $status) {
      Console::writeLinef(
        '  [%s] %.3fkB %s %s -> %s',
        date('Y-m-d H:i:s'),
        memory_get_usage() / 1024,
        $method,
        $query,
        $status
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

    $resources= $layout->staticResources($profile);
    if (null === $resources) {
      $protocol->setUrlHandler('default', '#^/#', new FileHandler(
        $docroot,
        $notFound= function() { return false; }
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
      foreach ($application->config()->sources() as $source) {
        $pm->appendSource($source);
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