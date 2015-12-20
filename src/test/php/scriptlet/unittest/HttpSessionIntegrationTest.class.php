<?php namespace scriptlet\unittest;

use scriptlet\HttpSessionInvalidException;
use scriptlet\HttpScriptlet;
use scriptlet\HttpScriptletRequest;
use scriptlet\HttpScriptletResponse;
use scriptlet\ScriptletException;
use peer\URL;
use peer\http\HttpConstants;
use lang\IllegalArgumentException;

/**
 * TestCase
 *
 * @see   xp://scriptlet.HttpSession
 */
class HttpSessionIntegrationTest extends ScriptletTestCase {

  #[@test]
  public function createSession() {
    $req= $this->newRequest('GET', new URL('http://localhost/'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'needsSession' => function($request) { return true; }
    ]);
    $s->service($req, $res);
    $this->assertEquals(HttpConstants::STATUS_FOUND, $res->statusCode);
    
    // Check URL from Location: header contains the session ID
    with ($redirect= new URL(substr($res->headers[0], strlen('Location: ')))); {
      $this->assertEquals('http', $redirect->getScheme());
      $this->assertEquals('localhost', $redirect->getHost());
      $this->assertEquals('/', $redirect->getPath());
      $this->assertEquals(session_id(), $redirect->getParam('psessionid', ''), $redirect->getURL());
    }
  }

  #[@test]
  public function invalidSessionCreatesNewSession() {
    $req= $this->newRequest('GET', new URL('http://localhost/?psessionid=INVALID'));
    $res= new HttpScriptletResponse();
    
    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'needsSession' => function($request) { return true; }
    ]);
    $s->service($req, $res);
    $this->assertEquals(HttpConstants::STATUS_FOUND, $res->statusCode);
    
    // Check URL from Location: header contains the session ID
    with ($redirect= new URL(substr($res->headers[0], strlen('Location: ')))); {
      $this->assertEquals('http', $redirect->getScheme());
      $this->assertEquals('localhost', $redirect->getHost());
      $this->assertEquals('/', $redirect->getPath());
      $this->assertEquals(session_id(), $redirect->getParam('psessionid'));
    }
  }

  #[@test, @expect(HttpSessionInvalidException::class)]
  public function invalidSession() {
    $req= $this->newRequest('GET', new URL('http://localhost/?psessionid=INVALID'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'needsSession' => function($request) { return true; },
      'handleInvalidSession' => function($request, $response) { return false; } 
    ]);
    $s->service($req, $res);
  }

  #[@test, @expect(HttpSessionInvalidException::class)]
  public function sessionInitializationError() {
    $req= $this->newRequest('GET', new URL('http://localhost/?psessionid=MALFORMED'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'needsSession' => function($request) { return true; },
      'handleSessionInitialization' => function($request) {
        if (!preg_match('/^a-f0-9$/', $request->getSessionId())) { 
          throw new IllegalArgumentException('Invalid characters in session id');
        }
        parent::handleSessionInitialization($request);
      }
    ]);
    $s->service($req, $res);
  }

  #[@test]
  public function handleSessionInitializationError() {
    $req= $this->newRequest('GET', new URL('http://localhost/?psessionid=MALFORMED'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'needsSession' => function($request) { return true; },
      'handleSessionInitialization' => function($request) {
        if (!preg_match('/^a-f0-9$/', $request->getSessionId())) { 
          throw new IllegalArgumentException('Invalid characters in session id');
        }
        parent::handleSessionInitialization($request);
      },
      'handleSessionInitializationError' => function($request, $response) {
        $request->getURL()->addParam('relogin', 1);
        return $request->session->initialize(null);
      }
    ]);
    $s->service($req, $res);
    $this->assertEquals(HttpConstants::STATUS_FOUND, $res->statusCode);
    
    // Check URL from Location: header contains the session ID
    with ($redirect= new URL(substr($res->headers[0], strlen('Location: ')))); {
      $this->assertEquals('http', $redirect->getScheme());
      $this->assertEquals('localhost', $redirect->getHost());
      $this->assertEquals('/', $redirect->getPath());
      $this->assertEquals(session_id(), $redirect->getParam('psessionid'));
      $this->assertEquals('1', $redirect->getParam('relogin'));
    }
  }
}