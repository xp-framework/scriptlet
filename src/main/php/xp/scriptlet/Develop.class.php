<?php namespace xp\scriptlet;

use lang\Runtime;
use lang\RuntimeOptions;
use util\cmd\Console;
use lang\ClassLoader;
use lang\FileSystemClassLoader;
use lang\archive\ArchiveClassLoader;

/**
 * Serves requests using PHP's builtin webserver
 *
 * @see   http://php.net/manual/en/features.commandline.webserver.php
 */
class Develop {
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
   * @param  string $source
   * @param  string $profile
   * @param  io.Path $webroot
   * @param  io.Path $docroot
   * @param  string[] $config
   */
  public function serve($source, $profile, $webroot, $docroot, $config) {
    $runtime= Runtime::getInstance();
    $startup= $runtime->startupOptions();
    $backing= typeof($startup)->getField('backing')->setAccessible(true)->get($startup);

    // PHP doesn't start with a nonexistant document root
    if (!$docroot->exists()) {
      $docroot= getcwd();
    }

    // Start `php -S`, the development webserver
    $arguments= ['-S', $this->host.':'.$this->port, '-t', $docroot];
    $options= newinstance(RuntimeOptions::class, [$backing], [
      'asArguments' => function() use($arguments) {
        return array_merge($arguments, parent::asArguments());
      }
    ]);
    $options->withSetting('user_dir', $source.PATH_SEPARATOR.implode(PATH_SEPARATOR, $config));

    // Pass classpath (TODO: This is fixed in XP 7.6.0, remove once
    // this becomes minimum dependency)
    $cp= [];
    foreach (ClassLoader::getLoaders() as $delegate) {
      if ($delegate instanceof FileSystemClassLoader || $delegate instanceof ArchiveClassLoader) {
        $cp[]= $delegate->path;
      }
    }
    set_include_path('');
    $options->withClassPath($cp);

    // Export environment
    putenv('DOCUMENT_ROOT='.$docroot);
    putenv('WEB_ROOT='.$webroot);
    putenv('SERVER_PROFILE='.$profile);

    Console::writeLine("\e[33m@", $this, "\e[0m");
    Console::writeLine("\e[1mServing ", (new Source($source, new Config($config)))->layout());
    Console::writeLine("\e[36m", str_repeat('═', 72), "\e[0m");
    Console::writeLine();

    with ($runtime->newInstance($options, 'web', '', []), function($proc) {
      $proc->in->close();
      Console::writeLine("\e[33;1m>\e[0m Server started: \e[35;4m", $this->url, "\e[0m (", date('r'), ')');
      Console::writeLine('  PID ', $proc->getProcessId(), '; press Ctrl+C to exit');
      Console::writeLine();

      while (is_string($line= $proc->err->readLine())) {
        Console::writeLine("  \e[36m", $line, "\e[0m");
      }
    });
  }
}