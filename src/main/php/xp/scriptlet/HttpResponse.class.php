<?php namespace xp\scriptlet;

use scriptlet\HttpScriptletURL;
use scriptlet\HttpScriptletResponse;

class HttpResponse extends HttpScriptletResponse {

  public function __construct($socket) {

    // Rewire I/O
    $this->sendHeaders= function($version, $statusCode, $headers) use($socket) {
      $socket->write("HTTP/1.1 ".$statusCode."\r\n");
      $socket->write("Date: ".gmdate('D, d M Y H:i:s T')."\r\n");
      $socket->write("Connection: close\r\n");
      foreach ($headers as $header) {
        $socket->write($header."\r\n");
      }
      $socket->write("\r\n");
    };
    $this->sendContent= function($content) use($socket) {
      $socket->write($content);
    };
  }
}