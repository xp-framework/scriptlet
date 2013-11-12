<?php namespace scriptlet\xml\workflow\casters;



/**
 * Casts given values to booleans
 *
 * @purpose  Caster
 */
class ToBoolean extends ParamCaster {

  /**
   * Cast a given value
   *
   * @see     xp://scriptlet.xml.workflow.casters.ParamCaster
   * @param   array value
   * @return  array value
   */
  public function castValue($value) {
    static $map= array(
      'true'  => true,
      'yes'   => true,
      'on'    => true,
      '1'     => true,
      'false' => false,
      'no'    => false,
      'off'   => false,
      '0'     => false
    );
    
    $return= array();
    foreach ($value as $k => $v) {
      $lookup= trim(strtolower($v));
      if (!isset($map[$lookup])) return null; // An error occured
      
      $return[$k]= $map[$lookup];
    }

    return $return;
  }
}
