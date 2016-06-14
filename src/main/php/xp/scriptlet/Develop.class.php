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
  private $host, $port, $url, $output;
  
  /**
   * Creates a new instance
   *
   * @param  string $host
   * @param  int $port
   * @param  bool $verbose Whether to use verbose output. Optional, defaults to false.
   */
  public function __construct($host, $port, $verbose= false) {
    $this->host= $host;
    $this->port= $port;
    $this->url= 'http://'.$host.':'.$port.'/';

    if ($verbose) {
      $this->output= function($line) {
        Console::writeLine("  \e[36m", $line, "\e[0m");
      };
    } else {
      $this->output= function($line) {
        static $requests= 0;

        Console::write("\r  \e[36m", $requests++, "\e[0m requests served @ ", date('r'));
      };
    }
  }

  /** @return string */
  public function toString() {
    return nameof($this).'(`'.Runtime::getInstance()->getExecutable()->getFileName().' -S`)';
  }

  /**
   * Serve requests
   */
  public function serve(WebLayout $layout, $webroot, $profile, $docroot) {
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
      Console::writeLine();

      while (null !== ($line= $proc->err->readLine())) {
        $this->output->__invoke($line);
      }
    });
  }
}