<?php namespace scriptlet\xml\workflow\checkers;

/**
 * Checks given values
 *
 * @purpose  Abstract base class
 */
abstract class ParamChecker extends \lang\Object {

  /**
   * Check a given value
   *
   * @param   array value
   * @return  string error or NULL on success
   */
  abstract public function check($value);
}
