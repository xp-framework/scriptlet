<?php namespace scriptlet\xml\workflow\casters;

/**
 * Remove windows line breaks (\r)
 *
 * @test      xp://net.xp_framework.unittest.scriptlet.workflow.ToUnixLineBreaksTest
 * @purpose   Caster
 */
class ToUnixLineBreaks extends ParamCaster {

  /**
   * Cast a given value.
   *
   * @see     xp://scriptlet.xml.workflow.casters.ParamCaster
   * @param   string[] value
   * @return  string[] value
   */
  public function castValue($value) {
    $return= [];
    foreach ($value as $k => $v) {
      $return[$k]= str_replace("\r\n", "\n", $v);
    }
    return $return;
  }
}