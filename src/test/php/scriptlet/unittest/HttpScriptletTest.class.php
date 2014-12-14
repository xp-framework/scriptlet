<?php namespace scriptlet\unittest;

use scriptlet\RequestAuthenticator;
use scriptlet\HttpScriptlet;
use scriptlet\HttpScriptletRequest;
use scriptlet\HttpScriptletResponse;
use scriptlet\ScriptletException;
use peer\URL;
use peer\http\HttpConstants;
use lang\IllegalStateException;
use lang\IllegalArgumentException;
use lang\IllegalAccessException;

/**
 * TestCase
 *
 * @see   https://github.com/xp-framework/xp-framework/issues/162
 * @see   xp://scriptlet.HttpScriptlet
 */
class HttpScriptletTest extends ScriptletTestCase {
  protected static $helloScriptlet= null;

  /**
   * Defines scriptlet which echoes "Hello" and the request method for all methods.
   *
   * @return void
   */
  #[@beforeClass]
  public static function defineHelloScriptlet() {
    self::$helloScriptlet= newinstance('scriptlet.HttpScriptlet', [], '{
      public function doGet($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doPost($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doHead($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doTrace($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doConnect($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doOptions($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doDelete($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doPut($request, $response) {
        $response->write("Hello ".$request->method);
      }
      public function doPatch($request, $response) {
        $response->write("Hello ".$request->method);
      }
    }');
  }

  /**
   * Helper method
   *
   * @param   string method
   * @param   string expect
   */
  protected function assertHandlerForMethodTriggered($method) {
    $req= $this->newRequest($method, new URL('http://localhost/'));
    $res= new HttpScriptletResponse();

    self::$helloScriptlet->service($req, $res);
    $this->assertEquals(HttpConstants::STATUS_OK, $res->statusCode);
    $this->assertEquals('Hello '.$method, $res->getContent());
  }

  #[@test, @expect(class= 'scriptlet.ScriptletException', withMessage= 'Unknown HTTP method: "LOOK"')]
  public function illegalHttpVerb() {
    $req= $this->newRequest('LOOK', new URL('http://localhost/'));
    $res= new HttpScriptletResponse();
    
    $s= new HttpScriptlet();
    $s->service($req, $res);
  }

  #[@test]
  public function doGet() {
    $this->assertHandlerForMethodTriggered('GET');
  }

  #[@test]
  public function doHead() {
    $this->assertHandlerForMethodTriggered('HEAD');
  }

  #[@test]
  public function doPost() {
    $this->assertHandlerForMethodTriggered('POST');
  }

  #[@test]
  public function doDelete() {
    $this->assertHandlerForMethodTriggered('DELETE');
  }

  #[@test]
  public function doOptions() {
    $this->assertHandlerForMethodTriggered('OPTIONS');
  }

  #[@test]
  public function doTrace() {
    $this->assertHandlerForMethodTriggered('TRACE');
  }

  #[@test]
  public function doConnect() {
    $this->assertHandlerForMethodTriggered('CONNECT');
  }

  #[@test]
  public function doPut() {
    $this->assertHandlerForMethodTriggered('PUT');
  }

  #[@test]
  public function doPatch() {
    $this->assertHandlerForMethodTriggered('PATCH');
  }

  #[@test, @expect(class= 'scriptlet.ScriptletException', withMessage= 'HTTP method "DELETE" not supported')]
  public function requestedMethodNotImplemented() {
    $req= $this->newRequest('DELETE', new URL('http://localhost/'));
    $res= new HttpScriptletResponse();
    
    $s= newinstance('scriptlet.HttpScriptlet', [], []);
    $s->service($req, $res);
  }

  #[@test, @expect(class= 'scriptlet.ScriptletException', withMessage= 'Request processing failed [doGet]: Test')]
  public function exceptionInDoWrapped() {
    $req= $this->newRequest('GET', new URL('http://localhost/'));
    $res= new HttpScriptletResponse();
    
    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'doGet' => function($request, $response) { throw new IllegalArgumentException('Test'); }
    ]);
    $s->service($req, $res);
  }

  #[@test, @expect(class= 'scriptlet.ScriptletException', withMessage= 'Test')]
  public function scriptletExceptionInDo() {
    $req= $this->newRequest('GET', new URL('http://localhost/'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'doGet' => function($request, $response) { throw new ScriptletException('Test'); }
    ]);
    $s->service($req, $res);
  }

  #[@test]
  public function sendRedirect() {
    $req= $this->newRequest('GET', new URL('http://localhost/'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'doGet' => function($request, $response) { $response->sendRedirect('http://localhost/home'); }
    ]);
    $s->service($req, $res);
    $this->assertEquals(HttpConstants::STATUS_FOUND, $res->statusCode);
    $this->assertEquals('Location: http://localhost/home', $res->headers[0]);
  }

  #[@test]
  public function forwardedHost() {
    $req= $this->newRequest('GET', new URL('http://localhost/'));
    $req->addHeader('X-Forwarded-Host', 'proxy.example.com');
    $res= new HttpScriptletResponse();

    $s= new HttpScriptlet();
    $s->service($req, $res);

    $this->assertEquals('proxy.example.com', $req->getURL()->getHost());
  }

  #[@test]
  public function forwardedHosts() {
    $req= $this->newRequest('GET', new URL('http://localhost/'));
    $req->addHeader('X-Forwarded-Host', 'balance.example.com, proxy.example.com');
    $res= new HttpScriptletResponse();

    $s= new HttpScriptlet();
    $s->service($req, $res);

    $this->assertEquals('balance.example.com', $req->getURL()->getHost());
  }
}
