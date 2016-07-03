<?php namespace xp\scriptlet;

/**
 * Web server
 */
class Server {

  /**
   * Entry point method. Receives the following arguments from xpws:
   *
   * 0. The web root - defaults to $CWD
   * 1. The application source - either a directory or a layout or scriptlet class name
   * 2. The server profile - default to "dev"
   * 3. The server address - default to "localhost:8080"
   * 4. The mode - default to "serve" (can include further arguments to the server constructor separated by commas)
   *
   * @param   string[] args
   * @return  int
   */
  public static function main(array $args) {
    $pass= [
      '-r', isset($args[0]) ? realpath($args[0]) : getcwd(),
      '-p', isset($args[2]) ? $args[2] : 'dev',
      '-a', isset($args[3]) ? $args[3] : 'localhost:8080',
      '-m', isset($args[4]) ? $args[4] : 'serve'
    ];

    if (isset($args[1])) {
      $sources= explode(PATH_SEPARATOR, ltrim($args[1], ':'));
      $source= array_shift($sources);
      foreach ($sources as $dir) {
        $pass[]= '-c';
        $pass[]= $dir;
      }
      $pass[]= $source;
    }

    WebRunner::main($pass);
    return 0;
  }
}