<?php namespace scriptlet\unittest;

use scriptlet\HttpScriptlet;
use scriptlet\HttpScriptletRequest;
use lang\System;
use io\Folder;
use peer\URL;

/**
 * Base class for scriptlet test cases. Ensures sessions are stored
 * in a temporary directory which is removed after tests in the sub-
 * classes are run.
 *
 */
abstract class ScriptletTestCase extends \unittest\TestCase {
  protected static $temp= null;

  static function __static() {
    if (!function_exists('getallheaders')) {
      eval('function getallheaders() { return []; }');
    }
  }

  /**
   * Creates a new request object
   *
   * @param   string method
   * @param   peer.URL url
   * @return  scriptlet.HttpScriptletRequest
   */
  protected function newRequest($method, URL $url) {
    $q= $url->getQuery('');
    $req= new HttpScriptletRequest();
    $req->method= $method;
    $req->env['SERVER_PROTOCOL']= 'HTTP/1.1';
    $req->env['REQUEST_URI']= $url->getPath('/').($q ? '?'.$q : '');
    $req->env['QUERY_STRING']= $q;
    $req->env['HTTP_HOST']= $url->getHost();
    if ('https' === $url->getScheme()) { 
      $req->env['HTTPS']= 'on';
    }
    $req->setHeaders([]);
    $req->setParams($url->getParams());
    return $req;
  }

  /**
   * Set session path to temporary directory
   *
   * @return void
   */
  #[@beforeClass]
  public static function prepareTempDir() {
    self::$temp= new Folder(System::tempDir(), md5(uniqid()));
    self::$temp->create();
    session_save_path(self::$temp->getURI());
  }

  /**
   * Cleanup temporary directory
   *
   * @return void
   */
  #[@afterClass]
  public static function cleanupTempDir() {
    self::$temp->unlink();
  }
}
