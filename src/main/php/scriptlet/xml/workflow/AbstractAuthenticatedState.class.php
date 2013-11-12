<?php namespace scriptlet\xml\workflow;



/**
 * Authenticated state
 *
 * @purpose  Base class for states needing an authentication
 */
class AbstractAuthenticatedState extends AbstractState {

  /**
   * Returns whether we need an authentication. Always returns
   * TRUE in this implementation.
   *
   * @return  bool
   */
  public function requiresAuthentication() {
    return true;
  }
}
