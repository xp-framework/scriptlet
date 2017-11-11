<?php namespace scriptlet;

/**
 * Scriptlet output stream
 *
 * @see  xp://scriptlet.Reponse#out
 */
class ScriptletOutputStream implements \io\streams\OutputStream {
  protected $response;

  /**
   * Creates a new scriptlet output stream
   *
   * @param  scriptlet.Response $r
   */
  public function __construct($r) {
    $this->response= $r;
  }

  /**
   * Writes to this output stream.
   *
   * @param  string $arg
   * @return int
   */
  public function write($arg) {
    $this->response->write($arg);
    return strlen($arg);
  }

  /**
   * Flushes output stream
   *
   * @return void
   */
  public function flush() {
    $this->response->flush();
  }

  /**
   * Closes output stream
   *
   * @return void
   */
  public function close() {
    $this->response->isCommitted() || $this->response->flush();
  }
}