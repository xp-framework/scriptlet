<?php namespace xp\scriptlet;

/**
 * Web server
 */
class Server {

  /**
   * Entry point method. Receives the following arguments from xpws:
   *
   * 0. The web root - defaults to $CWD
   * 1. The application source - either a directory or ":" + f.q.c.Name
   * 2. The server profile - default to "dev"
   * 3. The server address - default to "localhost:8080"
   * 4. The mode - default to "serve" (can include further arguments to the server constructor separated by commas)
   *
   * @param   string[] args
   * @return  int
   */
  public static function main(array $args) {
    WebRunner::main([
      '-r', isset($args[0]) ? realpath($args[0]) : getcwd(),
      '-p', isset($args[2]) ? $args[2] : 'dev',
      '-a', isset($args[3]) ? $args[3] : 'localhost:8080',
      '-m', isset($args[4]) ? $args[4] : 'serve',
      isset($args[1]) ? $args[1] : 'etc'
    ]);
    return 0;
  }
}