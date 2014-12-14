<?php namespace scriptlet\unittest;

use scriptlet\RequestAuthenticator;
use scriptlet\HttpScriptlet;
use scriptlet\HttpScriptletRequest;
use scriptlet\HttpScriptletResponse;
use scriptlet\ScriptletException;
use peer\URL;
use peer\http\HttpConstants;
use lang\IllegalAccessException;

/**
 * TestCase
 *
 * @see   xp://scriptlet.RequestAuthenticator
 */
class RequestAuthenticatorTest extends ScriptletTestCase {

  #[@test, @expect('scriptlet.ScriptletException')]
  public function unconditionalDenyAuthenticator() {
    $req= $this->newRequest('GET', new URL('http://localhost/members/profile'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'getAuthenticator' => function($request) {
        if (!strstr($request->getURL()->getPath(), '/members')) return null;
        
        return newinstance('scriptlet.RequestAuthenticator', [], [
          'authenticate' => function($request, $response, $context) {
            throw new IllegalAccessException('Valid user required');
          }
        ]);
      },
    ]);
    $s->service($req, $res);
  }

  #[@test]
  public function redirectingAuthenticator() {
    $req= $this->newRequest('GET', new URL('http://localhost/members/profile'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'getAuthenticator' => function($request) {
        if (!strstr($request->getURL()->getPath(), '/members')) return null;
        
        return newinstance('scriptlet.RequestAuthenticator', [], [
          'authenticate' => function($request, $response, $context) {
            $response->sendRedirect('http://localhost/login');
            return false;
          }
        ]);
      },
      'doGet' => function($request, $response) {
        throw new llegalAccessException('Valid user required');
      }
    ]);
    $s->service($req, $res);
    $this->assertEquals(HttpConstants::STATUS_FOUND, $res->statusCode);
    $this->assertEquals('Location: http://localhost/login', $res->headers[0]);
  }

  #[@test]
  public function unconditionalAllowAuthenticator() {
    $req= $this->newRequest('GET', new URL('http://localhost/members/profile'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'getAuthenticator' => function($request) {
        if (!strstr($request->getURL()->getPath(), '/members')) return null;
        
        return newinstance('scriptlet.RequestAuthenticator', [], [
          'authenticate' => function($request, $response, $context) {
            return true;
          }
        ]);
      },
      'doGet' => function($request, $response) {
        $response->write('Welcome!');
      }
    ]);
    $s->service($req, $res);
    $this->assertEquals('Welcome!', $res->getContent());
  }
}