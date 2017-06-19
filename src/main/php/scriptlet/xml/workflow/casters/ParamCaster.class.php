<?php namespace scriptlet\xml\workflow\casters;

/**
 * Casts given values
 *
 * @purpose  Abstract base class
 */
abstract class ParamCaster {

  /**
   * Cast a given value
   *
   * @param   array value
   * @return  array value
   */
  abstract public function castValue($value);
}
