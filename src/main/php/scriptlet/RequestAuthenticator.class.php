<?php namespace scriptlet;

/**
 * This interface describes objects that take care of request
 * authentication.
 *
 * @see   xp://scriptlet.HttpScriptlet#getAuthenticator
 * @test  xp://scriptlet.unittest.RequestAuthenticatorTest
 */
interface RequestAuthenticator {

  /**
   * Authenticate a request
   *
   * @param   scriptlet.HttpScriptletRequest request
   * @param   scriptlet.HttpScriptletResponse response
   * @param   scriptlet.xml.workflow.Context context
   * @return  bool
   */
  public function authenticate($request, $response, $context);
}
