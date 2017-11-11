<?php namespace xp\scriptlet;

use peer\Socket;

/**
 * Base class for all URL handlers
 */
abstract class AbstractUrlHandler {

  /**
   * Send a HTTP header message
   *
   * @param   peer.Socket socket
   * @param   int sc the status code
   * @param   string message status message
   * @param   string[] headers
   */
  protected function sendHeader(Socket $socket, $sc, $message, array $headers) {
    $socket->write('HTTP/1.1 '.$sc.' '.$message."\r\n");
    $socket->write('Date: '.gmdate('D, d M Y H:i:s T')."\r\n");
    $socket->write("Server: XP/PHP\r\n");
    $socket->write("Connection: close\r\n");
    foreach ($headers as $header) {
      $socket->write($header."\r\n");
    }
    $socket->write("\r\n");
  }

  /**
   * Send a HTTP error message
   *
   * @param   peer.Socket socket
   * @param   int sc the status code
   * @param   string message status message
   * @param   string reason the reason
   */
  protected function sendErrorMessage(Socket $socket, $sc, $message, $reason) {
    $package= typeof($this)->getPackage();
    $errorPage= ($package->providesResource('error'.$sc.'.html')
      ? $package->getResource('error'.$sc.'.html')
      : $package->getResource('error500.html')
    );
    $body= str_replace('<xp:value-of select="reason"/>', $reason, $errorPage);
    $this->sendHeader($socket, $sc, $message, [
      'Content-Type: text/html',
      'Content-Length: '.strlen($body),
    ]);
    $socket->write($body);
  }

  /**
   * Handle a single request
   *
   * @param   string method request method
   * @param   string query query string
   * @param   [:string] headers request headers
   * @param   string data post data
   * @param   peer.Socket socket
   */
  public abstract function handleRequest($method, $query, array $headers, $data, Socket $socket);
}
