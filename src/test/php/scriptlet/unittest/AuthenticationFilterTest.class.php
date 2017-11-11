<?php namespace scriptlet\unittest;

use lang\IllegalAccessException;
use lang\IllegalArgumentException;
use scriptlet\AuthenticationFilter;
use scriptlet\Invocation;
use unittest\TestCase;

/**
 * TestCase
 *
 * @see   xp://scriptlet.AuthenticationFilter
 */
class AuthenticationFilterTest extends TestCase {

  /**
   * Regression test for fixed error handling in pull request #22
   * https://github.com/xp-framework/scriptlet/pull/22
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function fix_global_error_handling_regression() {
    $mockAuthenticator= newinstance('scriptlet.RequestAuthenticator', [], [
      'authenticate' => function($request, $response, $context) {
        return true;
      }
    ]);
    $mockInvocation= new Invocation(function() {
      throw new IllegalArgumentException('Test');
    }, [new AuthenticationFilter($mockAuthenticator)]);
    $mockInvocation->proceed(null, null);
  }

}