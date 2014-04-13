<?php namespace xp\scriptlet;

use io\IOException;
use util\cmd\Console;
use peer\Socket;

/**
 * HTTP protocol implementation
 */
class HttpProtocol extends \lang\Object implements \peer\server\ServerProtocol {
  protected $handlers = [];
  public $server = null;

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
   * Handle client data
   *
   * @param   peer.Socket socket
   * @return  mixed
   */
  public function handleData($socket) {
    
    // Read header
    $header= '';
    try {
      while (false === ($p= strpos($header, "\r\n\r\n")) && !$socket->eof()) {
        $header.= $socket->readBinary(1024);
      }
    } catch (IOException $e) {
      Console::$err->writeLine($e);
      return $socket->close();
    }
    
    // Parse first line
    if (4 != sscanf($header, '%s %[^ ] HTTP/%d.%d', $method, $query, $major, $minor)) {
      Console::$err->writeLine('Malformed request "', addcslashes($header, "\0..\17"), '" from ', $socket->host);
      return $socket->close();
    }
    $offset= strpos($header, "\r\n")+ 2;
    
    // Parse rest
    $headers= [];
    if ($t= strtok(substr($header, $offset, $p- $offset), "\r\n")) do {
      sscanf($t, "%[^:]: %[^\n]", $name, $value);
      $headers[$name]= $value;
    } while ($t= strtok("\r\n"));
    
    // Read input data (XXX: Delay until requested?)
    $body= '';
    try {
      if (isset($headers['Content-length'])) {
        $body= substr($header, $p+ 4);
        while (strlen($body) < $headers['Content-length']) {
          $body.= $socket->readBinary(1024);
        }
      }
    } catch (IOException $e) {
      Console::$err->writeLine($e);
      return $socket->close();
    }
    
    // Log request
    sscanf($headers['Host'], '%[^:]:%d', $host, $port);
    Console::$out->writef(
      '[%.3f %s %s @ %s] %s %s (%d bytes): ',
      memory_get_usage() / 1024,
      date('Y-m-d H:i:s'), 
      @$headers['User-Agent'],
      $host,
      $method, 
      $query, 
      strlen($body)
    );

    $host= strtolower($host);
    $handlers= isset($this->handlers[$host]) ? $this->handlers[$host] : $this->handlers['default'];
    foreach ($handlers as $pattern => $handler) {
      if (preg_match($pattern, $query)) {
        try {
          if (false === $handler->handleRequest($method, $query, $headers, $body, $socket)) continue;
          Console::$out->writeLine('OK');
        } catch (IOException $e) {
          Console::$out->writeLine('Error ', $e->compoundMessage());
        }
        \xp::gc();
        $socket->close();
        return;
      }
    }

    // Unhandled
    Console::$err->writeLine('Unhandled (', $this->handlers, ')');
    $handlers[':error']->handleRequest($method, $query, $headers, $body, $socket);
    $socket->close();
  }

  /**
   * Handle I/O error
   *
   * @param   peer.Socket socket
   * @param   lang.XPException e
   */
  public function handleError($socket, $e) {
    Console::$err->writeLine('* ', $socket->host, '~', $e);
    $socket->close();
  }
  
  /**
   * Returns a string representation of this object
   *
   * @return  string
   */
  public function toString() {
    $s= $this->getClassName()."@{\n";
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
