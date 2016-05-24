<?php namespace scriptlet\unittest;

use lang\IllegalStateException;
use unittest\TestCase;
use scriptlet\HttpScriptletResponse;

/**
 * TestCase
 *
 * @see      xp://scriptlet.HttpScriptletResponse
 */
class HttpScriptletResponseTest extends TestCase {
  protected $r= null;

  /**
   * Set up this testcase
   */
  public function setUp() {
    $this->r= new HttpScriptletResponse();
  }

  #[@test]
  public function noHeaders() {
    $this->assertEquals([], $this->r->headers);
  }

  #[@test]
  public function addHeader() {
    $this->r->setHeader('header', 'value');
    $this->assertEquals('value', $this->r->getHeader('header'));
  }

  #[@test]
  public function addHeaderTwice() {
    $this->r->setHeader('header', 'value');
    $this->r->setHeader('header', 'shadow');
    $this->assertEquals('value', $this->r->getHeader('header'));
    $this->assertEquals(2, sizeof($this->r->headers));
  }

  #[@test]
  public function lookupCaseInsensitive() {
    $this->r->setHeader('header', 'value');
    $this->assertEquals('value', $this->r->getHeader('HEADER'));
  }

  #[@test]
  public function nonexistingHeaderReturnsDefault() {
    $this->assertEquals('default', $this->r->getHeader('does_not_exist', 'default'));
  }

  #[@test]
  public function writeToOutputStream() {
    $this->r->getOutputStream()->write('Hello');
    $this->assertEquals('Hello', $this->r->getContent());
  }

  #[@test]
  public function sendContent() {
    $this->r->setContent('Test');
    ob_start();
    $this->r->sendContent();
    $content= ob_get_contents();
    ob_end_clean();
    $this->assertEquals('Test', $content);
  }

  #[@test]
  public function doNotSendNullContent() {
    $this->r->setContent(null);
    ob_start();
    $this->r->sendContent();
    $content= ob_get_contents();
    ob_end_clean();
    $this->assertEquals('', $content);
  }

  #[@test]
  public function flush() {
    $this->r->flush();
  }

  #[@test, @expect(IllegalStateException::class)]
  public function flushCalledTwice() {
    $this->r->flush();
    $this->r->flush();
  }

  #[@test]
  public function isCommitted() {
    $this->assertFalse($this->r->isCommitted());
  }

  #[@test]
  public function isCommittedAfterFlush() {
    $this->r->flush();
    $this->assertTrue($this->r->isCommitted());
  }

  #[@test]
  public function writeToBuffer() {
    $this->r->write('Hello');
    $this->assertEquals('Hello', $this->r->getContent());
  }

  #[@test]
  public function writeDirectly() {
    $this->r->flush();

    ob_start();
    $this->r->write('Hello');
    $content= ob_get_contents();
    ob_end_clean();

    $this->assertEquals('Hello', $content);
  }

  #[@test]
  public function writeBufferedAndDirectWrites() {
    $this->r->write('Hello');   // This will be buffered

    ob_start();
    $this->r->flush();          // This will flush the buffer
    $this->r->write('World');
    $content= ob_get_contents();
    ob_end_clean();

    $this->assertEquals('HelloWorld', $content);
  }

  #[@test]
  public function output_stream_closed_twice() {
    $out= $this->r->getOutputStream();
    $out->close();
    $out->close();
  }
}
