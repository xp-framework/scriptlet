<?php namespace scriptlet;

/**
 * Filters wrap around the request processing.
 *
 * @test  xp://scriptlet.unittest.FilterTest
 */
interface Filter {

  /**
   * Filters request and response. To proceed with the invocation,
   * call its `proceed` method inside your filter as follows:
   *
   * ```php
   * $invocation->proceed($request, $response);
   * ```
   *
   * @param  scriptlet.Request $request
   * @param  scriptlet.Response $response
   * @param  scriptlet.Invocation $invocation
   * @return bool
   */
  public function filter($request, $response, $invocation);
}
