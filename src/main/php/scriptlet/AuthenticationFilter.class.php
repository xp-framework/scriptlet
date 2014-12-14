<?php namespace scriptlet;

use lang\Throwable;
use peer\http\HttpConstants;

/**
 * Filters wrap around the request processing.
 *
 * @test  xp://scriptlet.unittest.RequestAuthenticatorTest
 */
class AuthenticationFilter extends \lang\Object implements Filter {
  private $auth;

  /**
   * Creates a new AuthenticationFilter
   *
   * @param  scriptlet.RequestAuthenticator $auth
   */
  public function __construct(RequestAuthenticator $auth) {
    $this->auth= $auth;
  }

  /**
   * Filters request and response,
   *
   * @param  scriptlet.Request $request
   * @param  scriptlet.Response $response
   * @param  scriptlet.Invocation $invocation
   * @return bool
   */
  public function filter($request, $response, $invocation) {
    try {
      $r= $this->auth->authenticate($request, $response, null);
      return false === $r ? $r : $invocation->proceed($request, $response);
    } catch (ScriptletException $e) {
      throw $e;
    } catch (Throwable $e) {
      throw new ScriptletException('Authentication failed: '.$e->getMessage(), HttpConstants::STATUS_FORBIDDEN, $e);
    }
  }
}
