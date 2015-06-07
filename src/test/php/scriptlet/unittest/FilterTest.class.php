<?php namespace scriptlet\unittest;

use scriptlet\Filter;
use scriptlet\HttpScriptlet;
use scriptlet\HttpScriptletRequest;
use scriptlet\HttpScriptletResponse;
use scriptlet\ScriptletException;
use peer\URL;
use lang\IllegalStateException;

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

  #[@test]
  public function do_not_proceed() {
    $req= $this->newRequest('GET', new URL('http://localhost'));
    $res= new HttpScriptletResponse();

    $s= newinstance('scriptlet.HttpScriptlet', [], [
      'doGet' => function($request, $response) {
        throw new IllegalStateException('Will not be reached');
      }
    ]);
    $s->filter(newinstance('scriptlet.Filter', [], [
      'filter' => function($request, $response, $invocation) {
        $response->setHeader('Filtered', 'true');
        return;
      }
    ]));
    $s->service($req, $res);

    $this->assertEquals(['Filtered: true'], $res->headers);
    $this->assertEquals(null, $res->content);
  }
}