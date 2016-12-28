<?php namespace xp\scriptlet;

use scriptlet\HttpScriptletURL;
use scriptlet\HttpScriptletRequest;
use io\streams\MemoryInputStream;
use lang\FormatException;

class HttpRequest extends HttpScriptletRequest {

  public function __construct($socket) {
    $header= '';
    while (false === ($p= strpos($header, "\r\n\r\n")) && !$socket->eof()) {
      $header.= $socket->readBinary(1024);
    }

    if (4 !== sscanf($header, '%s %[^ ] HTTP/%d.%d', $this->method, $uri, $major, $minor)) {
      throw new FormatException('Malformed request "'.addcslashes($header, "\0..\17").'"');
    }

    // Parse headers
    $offset= strpos($header, "\r\n") + 2;
    $headers= [];
    if ($t= strtok(substr($header, $offset, $p- $offset), "\r\n")) do {
      sscanf($t, "%[^:]: %[^\r\n]", $name, $value);

      $l= strtolower($name);
      if (isset($this->headerlookup[$l])) {
        $name= $this->headerlookup[$l];
      } else {
        $this->headerlookup[$l]= $name;
      }
      $this->headers[$name]= $value;
    } while ($t= strtok("\r\n"));

    // Parse URI
    $host= isset($this->headerlookup['host']) ? $this->headers[$this->headerlookup['host']] : 'localhost';
    $this->url= new HttpScriptletURL('http://'.$host.$uri);

    // Read data
    if (isset($this->headerlookup['content-length'])) {
      $this->data= substr($header, $p+ 4);
      $length= (int)$this->headers[$this->headerlookup['content-length']];
      while (strlen($this->data) < $length && !$socket->eof()) {
        $this->data.= $socket->readBinary(1024);
      }
    } else {
      $this->data= '';
    }
    $this->readData= function() { return new MemoryInputStream($this->data); };

    // Merge POST and GET parameters
    if (isset($this->headerlookup['content-type']) && 0 === strncmp($this->headers[$this->headerlookup['content-type']], 'application/x-www-form-urlencoded', 33)) {
      parse_str($this->data, $params);
      $this->setParams(array_merge($this->url->getParams(), $params));
    } else {
      $this->setParams($this->url->getParams());
    }

    // Set up standard environment
    $this->env['SERVER_PROTOCOL']= 'HTTP/'.$major.'.'.$minor;
    $this->env['REQUEST_URI']= $uri;
    $this->env['QUERY_STRING']= substr($uri, strpos($uri, '?')+ 1);
    $this->env['HTTP_HOST']= $host;
    if (isset($this->headerlookup['authorization'])) {
      $header= $this->headers[$this->headerlookup['authorization']];
      if (0 === strncmp('Basic', $header, 5)) {
        $credentials= explode(':', base64_decode(substr($header, 6)));
        $this->env['PHP_AUTH_USER']= $credentials[0];
        $this->env['PHP_AUTH_PW']= $credentials[1];
      }
    }
  }

  /** @return string */
  public function toString() {
    return nameof($this).'@('.$this->method.' '.$this->url->toString().')';
  }
}