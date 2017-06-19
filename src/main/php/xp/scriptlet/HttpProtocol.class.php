<?php namespace xp\scriptlet;

use io\IOException;
use util\cmd\Console;
use peer\Socket;

/**
 * HTTP protocol implementation
 */
class HttpProtocol implements \peer\server\ServerProtocol {
  protected $handlers = [];
  private $logging;
  public $server = null;

  /**
   * Creates a new instance with a given logging
   *
   * @param  var $logging
   */
  public function __construct($logging) {
    $this->logging= $logging;
  }

  /**
   * Initialize Protocol
   *
   * @return  bool
   */
  public function initialize() {
    $this->handlers['default'][':error']= newinstance('xp.scriptlet.AbstractUrlHandler', [], [
      'handleRequest' => function($method, $query, array $headers, $data, Socket $socket) {
        $this->sendErrorMessage($socket, 400, 'Bad Request', 'Cannot handle request');
      }
    ]);
  }

  /**
   * Handle client connect
   *
   * @param   peer.Socket socket
   */
  public function handleConnect($socket) {
    // Intentionally empty
  }

  /**
   * Handle client disconnect
   *
   * @param   peer.Socket socket
   */
  public function handleDisconnect($socket) {
    $socket->close();
  }

  /**
   * Supply a URL handler for a given regex
   *
   * @param   string pattern regex
   * @param   xp.scriptlet.AbstractUrlHandler handler
   */
  public function setUrlHandler($host, $pattern, AbstractUrlHandler $handler) {
    if (!isset($this->handlers[$host])) {
      $this->handlers[$host]= [];
    }
    $this->handlers[$host][$pattern]= $handler;
  }

  /**
   * Handle request by searching for all handlers, and invoking the correct handler
   *
   * @param  string $host
   * @param  string $method
   * @param  string $query
   * @param  [:string] $headers
   * @param  string $body
   * @param  peer.Socket $socket
   */
  public function handleRequest($host, $method, $query, $headers, $body, $socket) {
    $handlers= isset($this->handlers[$host]) ? $this->handlers[$host] : $this->handlers['default'];
    foreach ($handlers as $pattern => $handler) {
      if (preg_match($pattern, $query)) {
        try {
          if (null === ($status= $handler->handleRequest($method, $query, $headers, $body, $socket))) continue;
          return [$status, ''];
        } catch (IOException $e) {
          return [520, $e->compoundMessage()];
        }
      }
    }

    $handlers[':error']->handleRequest($method, $query, $headers, $body, $socket);
    return [520, 'Unhandled'];
  }

  /**
   * Handle client data
   *
   * @param   peer.Socket socket
   * @return  mixed
   */
  public function handleData($socket) {
    $header= '';
    try {
      while (false === ($p= strpos($header, "\r\n\r\n")) && !$socket->eof()) {
        $header.= $socket->readBinary(1024);
      }
    } catch (IOException $e) {
      // Console::$err->writeLine($e);
      return $socket->close();
    }

    if (4 !== sscanf($header, '%s %[^ ] HTTP/%d.%d', $method, $query, $major, $minor)) {
      // Console::$err->writeLine('Malformed request "', addcslashes($header, "\0..\17"), '" from ', $socket->host);
      return $socket->close();
    }
    $offset= strpos($header, "\r\n") + 2;
    $headers= [];
    if ($t= strtok(substr($header, $offset, $p- $offset), "\r\n")) do {
      sscanf($t, "%[^:]: %[^\r\n]", $name, $value);
      $headers[$name]= $value;
    } while ($t= strtok("\r\n"));

    $body= '';
    try {
      if (isset($headers['Content-Length'])) {
        $body= substr($header, $p+ 4);
        $length= (int)$headers['Content-Length'];
        while (strlen($body) < $length) {
          $body.= $socket->readBinary(1024);
        }
      }
    } catch (IOException $e) {
      Console::$err->writeLine($e);
      return $socket->close();
    }

    gc_enable();
    sscanf($headers['Host'], '%[^:]:%d', $host, $port);
    $status= $this->handleRequest(strtolower($host), $method, $query, $headers, $body, $socket);
    $this->logging->__invoke($host, $method, $query, $status[0], $status[1]);
    gc_collect_cycles();
    gc_disable();
    \xp::gc();
    $socket->close();
  }

  /**
   * Handle I/O error
   *
   * @param   peer.Socket socket
   * @param   lang.XPException e
   */
  public function handleError($socket, $e) {
    // Console::$err->writeLine('* ', $socket->host, '~', $e);
    $socket->close();
  }

  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    $s= nameof($this)."@{\n";
    foreach ($this->handlers as $host => $handlers) {
      $s.= '  [host '.$host."] {\n";
      foreach ($handlers as $pattern => $handler) {
        $s.= '    handler<'.$pattern.'> => '.$handler->toString()."\n";
      }
      $s.= "  }\n";
    }
    return $s.'}';
  }
}
