<?php namespace scriptlet\unittest;

use scriptlet\Filter;
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
 * @see   xp://scriptlet.Filter
 */
class FilterTest extends ScriptletTestCase {

  #[@test]
  public function one_filter() {
    $req= $this->newRequest('GET', new URL('http://localhost'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'doGet' => function($request, $response) {
        $response->write('Invoked');
      }
    ]);
    $s->filter(newinstance('scriptlet.Filter', [], [
      'filter' => function($request, $response, $invocation) {
        $response->setHeader('Filtered', 'true');
        $invocation->proceed($request, $response);
      }
    ]));
    $s->service($req, $res);

    $this->assertEquals(['Filtered: true'], $res->headers);
    $this->assertEquals('Invoked', $res->content);
  }

  #[@test]
  public function two_filters() {
    $req= $this->newRequest('GET', new URL('http://localhost'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'doGet' => function($request, $response) {
        $response->write('Invoked');
      }
    ]);
    $s->filter(newinstance('scriptlet.Filter', [], [
      'filter' => function($request, $response, $invocation) {
        $response->setHeader('Filtered', 'one');
        $invocation->proceed($request, $response);
      }
    ]));
    $s->filter(newinstance('scriptlet.Filter', [], [
      'filter' => function($request, $response, $invocation) {
        $response->setHeader('Filtered', 'two');
        $invocation->proceed($request, $response);
      }
    ]));
    $s->service($req, $res);

    $this->assertEquals(['Filtered: one', 'Filtered: two'], $res->headers);
    $this->assertEquals('Invoked', $res->content);
  }
}