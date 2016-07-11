<?php namespace xp\scriptlet;

use peer\server\EventServer;

/**
 * Serves requests from an libevent-based server
 *
 * @ext   event - see http://pecl.php.net/package/event
 * @see   xp://peer.server.EventServer
 */
class Event extends Standalone {

  /**
   * Creates a new instance
   *
   * @param  string $host
   * @param  int $port
   */
  public function __construct($host, $port) {
    parent::__construct(new EventServer($host, $port), 'http://'.$host.':'.$port.'/');
  }
}