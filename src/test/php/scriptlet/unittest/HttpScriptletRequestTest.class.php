<?php namespace scriptlet\unittest;

use peer\URL;
use scriptlet\HttpScriptletRequest;
use scriptlet\Cookie;

/**
 * TestCase
 *
 * @see   xp://scriptlet.HttpScriptletRequest
 */
class HttpScriptletRequestTest extends \unittest\TestCase {

  /**
   * Creates a new request object
   *
   * @see     xp://scriptlet.HttpScriptlet#_setupRequest
   * @param   string method
   * @param   string url
   * @param   [:string] headers
   * @return  scriptlet.HttpScriptletRequest
   */
  protected function newRequest($method, $url, array $headers) {
    $u= parse_url($url);
    isset($u['query']) ? parse_str($u['query'], $params) : $params= [];
  
    $r= new HttpScriptletRequest();
    $r->method= $method;
    $r->setURI(new URL($u['scheme'].'://'.$u['host'].'/'.$u['path']));
    $r->setParams($params);
    $r->setHeaders($headers);
    
    return $r;
  }

  #[@test, @values(['paramname', 'ParamName', 'PARAMNAME'])]
  public function get_param($name) {
    $r= $this->newRequest('GET', 'http://localhost/?paramname=b', []);
    $this->assertEquals('b', $r->getParam($name));
  }

  #[@test, @values(['%DCber', '%C3%9Cber'])]
  public function get_param_encoded_as($encoded) {
    $r= $this->newRequest('GET', 'http://localhost/?coder='.$encoded, []);
    $this->assertEquals('Über', $r->getParam('coder'));
  }

  #[@test, @values(['', '?other=value'])]
  public function get_non_existant_param($query) {
    $r= $this->newRequest('GET', 'http://localhost/'.$query, []);
    $this->assertNull($r->getParam('any'));
  }

  #[@test, @values(['', '?other=value'])]
  public function get_non_existant_param_with_default($query) {
    $r= $this->newRequest('GET', 'http://localhost/'.$query, []);
    $this->assertEquals('default', $r->getParam('any', 'default'));
  }

  #[@test, @values(['paramname', 'ParamName', 'PARAMNAME'])]
  public function has_param($name) {
    $r= $this->newRequest('GET', 'http://localhost/?paramname=b', []);
    $this->assertTrue($r->hasParam($name));
  }

  #[@test, @values(['', '?other=value'])]
  public function does_not_have_non_existant_param($query) {
    $r= $this->newRequest('GET', 'http://localhost/'.$query, []);
    $this->assertFalse($r->hasParam('any'));
  }

  #[@test, @values(['a', 'b'])]
  public function has_param_and_get_param_with_two_params($name) {
    $r= $this->newRequest('GET', 'http://localhost/?a=value&b=value', []);
    $this->assertEquals([true, 'value'], [$r->hasParam($name), $r->getParam($name)]);
  }

  #[@test]
  public function has_param_and_get_param_with_array_params() {
    $r= $this->newRequest('GET', 'http://localhost/?a[]=1&a[]=2', []);
    $this->assertEquals([true, ['1', '2']], [$r->hasParam('a'), $r->getParam('a')]);
  }

  #[@test, @values(['%DCber', '%C3%9Cber'])]
  public function get_array_param_encoded_as($encoded) {
    $r= $this->newRequest('GET', 'http://localhost/?coder[]='.$encoded, []);
    $this->assertEquals(['Über'], $r->getParam('coder'));
  }

  #[@test, @values(['%DCber', '%C3%9Cber'])]
  public function get_deep_array_param_encoded_as($encoded) {
    $r= $this->newRequest('GET', 'http://localhost/?coder[foo][]='.$encoded, []);
    $this->assertEquals(['foo' => ['Über']], $r->getParam('coder'));
  }

  #[@test, @values(['5Pb8', 'w6TDtsO8'])]
  public function encode_to_xp_framework_charset($value) {
    $value= base64_decode($value);

    // String
    $r= $this->newRequest('GET', 'http://localhost/?coder='.$value, []);
    $this->assertEquals('äöü', $r->getParam('coder'));

    // Array
    $r= $this->newRequest('GET', 'http://localhost/?coder[]='.$value, []);
    $this->assertEquals(['äöü'], $r->getParam('coder'));

    // Deep Array
    $r= $this->newRequest('GET', 'http://localhost/?coder[foo][]='.$value, []);
    $this->assertEquals(['foo' => ['äöü']], $r->getParam('coder'));
  }

  #[@test]
  public function has_param_and_get_param_with_hash_params() {
    $r= $this->newRequest('GET', 'http://localhost/?a[one]=1&a[two]=2', []);
    $this->assertEquals([true, ['one' => '1', 'two' => '2']], [$r->hasParam('a'), $r->getParam('a')]);
  }

  #[@test, @values(['%DCber', '%C3%9Cber'])]
  public function get_hash_param_encoded_as($encoded) {
    $r= $this->newRequest('GET', 'http://localhost/?coder[uber]='.$encoded, []);
    $this->assertEquals(['uber' => 'Über'], $r->getParam('coder'));
  }

  #[@test]
  public function has_param_and_get_param_with_valueless_param() {
    $r= $this->newRequest('GET', 'http://localhost/?a', []);
    $this->assertEquals([true, ''], [$r->hasParam('a'), $r->getParam('a')]);
  }

  #[@test, @values(['a', 'b'])]
  public function has_param_and_get_param_with_two_valueless_params($name) {
    $r= $this->newRequest('GET', 'http://localhost/?a&b', []);
    $this->assertEquals([true, ''], [$r->hasParam($name), $r->getParam($name)]);
  }

  #[@test]
  public function has_param_and_get_param_with_dotted_param() {
    $r= $this->newRequest('GET', 'http://localhost/?login.SessionId=4711', []);
    $this->assertEquals([true, '4711'], [$r->hasParam('login.SessionId'), $r->getParam('login.SessionId')]);
  }

  #[@test]
  public function setParamAndHasParamRoundtrip() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->setParam('a', 'b');
    $this->assertTrue($r->hasParam('a'));
  }

  #[@test]
  public function setParamAndGetParamRoundtrip() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->setParam('a', 'b');
    $this->assertEquals('b', $r->getParam('a'));
  }

  #[@test]
  public function setParamAndgetParamRoundtripMixedCaseHeaderLowerCaseQuery() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->setParam('ParamName', 'b');
    $this->assertEquals('b', $r->getParam('paramname'));
  }

  #[@test]
  public function setParamAndgetParamRoundtripLowerCaseHeaderMixedCaseQuery() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->setParam('paramname', 'b');
    $this->assertEquals('b', $r->getParam('ParamName'));
  }

  #[@test]
  public function paramsEmpty() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $this->assertEquals([], $r->getParams());
  }

  #[@test]
  public function params() {
    $r= $this->newRequest('GET', 'http://localhost/?CustomerId=1&Sort=ASC', []);
    $this->assertEquals(['CustomerId' => '1', 'Sort' => 'ASC'], $r->getParams());
  }

  #[@test]
  public function paramsLowerCase() {
    $r= $this->newRequest('GET', 'http://localhost/?CustomerId=1&Sort=ASC', []);
    $this->assertEquals(['customerid' => '1', 'sort' => 'ASC'], $r->getParams(CASE_LOWER));
  }

  #[@test]
  public function headersEmpty() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $this->assertEquals([], $r->getHeaders());
  }

  #[@test]
  public function headers() {
    $headers= ['Referer' => 'http://example.com/', 'User-Agent' => 'XP'];
    $r= $this->newRequest('GET', 'http://localhost/', $headers);
    $this->assertEquals($headers, $r->getHeaders());
  }

  #[@test]
  public function getHeader() {
    $r= $this->newRequest('GET', 'http://localhost/', [
      'Referer' => 'http://example.com/'
    ]);
    $this->assertEquals('http://example.com/', $r->getHeader('Referer'));
  }

  #[@test]
  public function getNonExistantHeader() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $this->assertNull($r->getHeader('User-Agent'));
  }

  #[@test]
  public function getNonExistantHeaderDefault() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $this->assertEquals('default', $r->getHeader('User-Agent', 'default'));
  }

  #[@test]
  public function headerLookupCaseInsensitive() {
    $r= $this->newRequest('GET', 'http://localhost/', [
      'UPPERCASE' => 1,
    ]);

    $this->assertEquals(1, $r->getHeader('uppercase'));
    $this->assertEquals(1, $r->getHeader('UpPeRCaSe'));
  }

  #[@test]
  public function addHeaderAndGetHeadersRoundtrip() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->addHeader('a', 'b');
    $this->assertEquals(['a' => 'b'], $r->getHeaders());
  }

  #[@test]
  public function addHeaderOverwritingAndGetHeadersRoundtrip() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->addHeader('a', 'b');
    $r->addHeader('A', 'c');
    $this->assertEquals(['a' => 'c'], $r->getHeaders());
  }

  #[@test]
  public function addHeaderAndGetHeaderRoundtrip() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->addHeader('a', 'b');
    $this->assertEquals('b', $r->getHeader('a'));
  }

  #[@test]
  public function addHeaderOverwritingAndGetHeaderRoundtrip() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->addHeader('a', 'b');
    $r->addHeader('a', 'c');
    $this->assertEquals('c', $r->getHeader('a'));
  }

  #[@test]
  public function addHeaderOverwritingCaseInsensitiveAndGetHeaderRoundtrip() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->addHeader('a', 'b');
    $r->addHeader('A', 'c');
    $this->assertEquals('c', $r->getHeader('a'));
  }

  #[@test]
  public function addHeaderAndGetHeaderRoundtripMixedCaseHeaderLowerCaseQuery() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->addHeader('HeaderName', 'b');
    $this->assertEquals('b', $r->getHeader('headername'));
  }

  #[@test]
  public function addHeaderAndGetHeaderRoundtripLowerCaseHeaderMixedCaseQuery() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $r->addHeader('headername', 'b');
    $this->assertEquals('b', $r->getHeader('HeaderName'));
  }

  #[@test]
  public function cookiesInitiallyEmpty() {
    $r= $this->newRequest('GET', 'http://localhost/', []);
    $this->assertEquals([], $r->getCookies());
  }

  #[@test]
  public function addCookie() {
    $r= $this->newRequest('GET', 'http://localhost/', []);

    $this->assertInstanceOf('scriptlet.Cookie', $r->addCookie(new Cookie('cookie', 'value')));
  }

  #[@test]
  public function hasCookieDetectsAddedCookie() {
    $r= $this->newRequest('GET', 'http://localhost/', []);

    $r->addCookie(new Cookie('cookie', 'value'));
    $this->assertTrue($r->hasCookie('cookie'));
  }

  #[@test]
  public function getCookieReturnsCookie() {
    $r= $this->newRequest('GET', 'http://localhost/', []);

    $r->addCookie(new Cookie('cookie', 'value'));
    $this->assertEquals('value', $r->getCookie('cookie')->getValue());
  }

  #[@test]
  public function cookieSuperglobalHonored() {
    $_COOKIE['name']= 'from-superglobal';
    $r= $this->newRequest('GET', 'http://localhost/', []);

    $this->assertEquals('from-superglobal', $r->getCookie('name')->getValue());
  }

  #[@test]
  public function cookieHeaderHonored() {
    $_COOKIE= [];
    $r= $this->newRequest('GET', 'http://localhost/', ['Cookie' => 'name=from-header']);

    $this->assertEquals('from-header', $r->getCookie('name')->getValue());
  }

  #[@test]
  public function setMultipleArrayAsParam() {
    $fileArray= [
      "__handler"=> "handler.employee/support.employeesupporthandler",
      "attachment" => [
        "name"     => ["test.txt"],
        "type"     => ["text/plain"],
        "tmp_name" => ["/tmp/phpKLEGHj"],
        "error"    => [0],
        "size"     => [0]
      ]
    ];

    $r= $this->newRequest('GET', 'http://localhost/', []);

    $this->assertNull($r->setParams($fileArray));
  }
}
