<?php namespace scriptlet\xml\workflow\casters;



/**
 * Casts given values to integers
 *
 * @test xp://scriptlet.unittest.workflow.ToIntegerTest
 */
class ToInteger extends ParamCaster {

  /**
   * Cast a given value.
   *
   * @see     php://intval
   * @see     xp://scriptlet.xml.workflow.casters.ParamCaster
   * @param   string[] value
   * @return  int[] value
   */
  public function castValue($value) {
    $return= array();
    foreach ($value as $k => $v) {
      if ('' == ltrim($v, ' +-0')) {
        $return[$k]= 0;
      } else {
        if (0 == ($return[$k]= intval($v))) return null;
      }
    }
    return $return;
  }
}
