<?php namespace scriptlet\unittest\workflow;

use scriptlet\xml\workflow\casters\ToInteger;


/**
 * Test the ToInteger caster
 *
 * @see  xp://scriptlet.unittest.workflow.AbstractCasterTest
 * @see  xp://scriptlet.xml.workflow.casters.ToInteger
 */
class ToIntegerTest extends AbstractCasterTest {

  /**
   * Return the caster
   *
   * @return  scriptlet.xml.workflow.casters.ParamCaster
   */
  protected function caster() {
    return new ToInteger();
  }

  /**
   * Test positive and negative numbers
   */
  #[@test, @values([['1', 1], ['-1', -1], ['0', 0]])]
  public function wholeNumbers($input, $expect) {
    $this->assertEquals($expect, $this->castValue($input), $input);
  }

  /**
   * Test empty input
   */
  #[@test]
  public function emptyInput() {
    $this->assertEquals(0, $this->castValue(''));
  }
}
