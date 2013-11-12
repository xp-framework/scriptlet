<?php namespace scriptlet\xml\workflow\casters;



/**
 * Casts given values to floating point numbers
 *
 * @deprecated Use ToDouble caster instead
 * @test xp://scriptlet.unittest.workflow.ToFloatTest
 */
class ToFloat extends ParamCaster {

  /**
   * Cast a given value.
   *
   * @see     php://intval
   * @see     xp://scriptlet.xml.workflow.casters.ParamCaster
   * @param   string[] value
   * @return  double[] value
   */
  public function castValue($value) {
    $return= array();
    foreach ($value as $k => $v) {
      if ('' == ltrim($v, ' +-0')) {
        $return[$k]= 0.0;
      } else {
        $return[$k]= floatval(strtr($v, ',', '.'));
      }
    }
    return $return;
  }
}
