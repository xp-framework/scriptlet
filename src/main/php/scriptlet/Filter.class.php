<?php namespace scriptlet;

/**
 * Filters wrap around the request processing.
 *
 * @test  xp://scriptlet.unittest.FilterTest
 */  
interface Filter {

  /**
   * Filters request and response
   *
   * @param  scriptlet.Request $request
   * @param  scriptlet.Response $response
   * @param  scriptlet.Invocation $invocation
   */
  public function filter($request, $response, $invocation);
}
