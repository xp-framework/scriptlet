<?php namespace xp\scriptlet;

use peer\server\ForkingServer;

/**
 * Serves requests from a forking server
 *
 * @ext   pcntl
 * @see   xp://peer.server.ForkingServer
 */
class Fork extends Standalone {

  /**
   * Creates a new instance
   *
   * @param  string $host
   * @param  int $port
   */
  public function __construct($host, $port) {
    parent::__construct(new ForkingServer($host, $port), 'http://'.$host.':'.$port.'/');
  }
}