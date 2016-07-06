<?php namespace scriptlet\xml\workflow\checkers;

use lang\IllegalArgumentException;
use lang\Object;
use util\Date;

/**
 * Checks given date on validity
 *
 * Error codes returned are:
 * <ul>
 *   <li>invalid - if the given value is no valid date</li>
 * </ul>
 *
 * @purpose  Checker
 */
class DateChecker extends Object {

  /**
   * Check a given value
   *
   * @param   array value
   * @return  string error or NULL on success
   */
  public function check($value) {
    foreach ($value as $v) {
      try {
        new Date($v);
      } catch (IllegalArgumentException $e) {
        return 'invalid';
      }
    }
  }
}