<?php namespace xp\scriptlet;

use lang\Runtime;
use lang\RuntimeOptions;
use util\cmd\Console;

/**
 * Serves requests using PHP's builtin webserver
 *
 * @see   http://php.net/manual/en/features.commandline.webserver.php
 */
class Develop extends \lang\Object {
  private $host, $port, $url;
  
  /**
   * Creates a new instance
   *
   * @param  string $host
   * @param  int $port
   */
  public function __construct($host, $port) {
    $this->host= $host;
    $this->port= $port;
    $this->url= 'http://'.$host.':'.$port.'/';
  }

  /** @return string */
  public function toString() {
    return nameof($this).'(`'.Runtime::getInstance()->getExecutable()->getFileName().' -S`)';
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
    $runtime= Runtime::getInstance();
    $startup= $runtime->startupOptions();
    $backing= typeof($startup)->getField('backing')->setAccessible(true)->get($startup);

    // Start `php -S`, the development webserver
    $arguments= ['-S', $this->host.':'.$this->port, '-t', $docroot];
    $options= newinstance(RuntimeOptions::class, [$backing], [
      'asArguments' => function() use($arguments) {
        return array_merge($arguments, parent::asArguments());
      }
    ]);

    with ($runtime->newInstance($options, 'web', '', []), function($proc) {
      $proc->in->close();
      Console::writeLine("\e[33;1m>\e[0m Server started: \e[35;4m", $this->url, "\e[0m (", date('r'), ')');
      Console::writeLine('  PID ', $proc->getProcessId(), '; press Ctrl+C to exit');
      Console::writeLine();

      while (null !== ($line= $proc->err->readLine())) {
        Console::writeLine("  \e[36m", $line, "\e[0m");
      }
    });
  }
}