<?php namespace scriptlet\unittest\workflow;

use scriptlet\xml\workflow\casters\ToDouble;


/**
 * Test the ToDouble caster
 *
 * @see  xp://scriptlet.unittest.workflow.AbstractCasterTest
 * @see  xp://scriptlet.xml.workflow.casters.ToDouble
 */
class ToDoubleTest extends AbstractCasterTest {

  /**
   * Return the caster
   *
   * @return  scriptlet.xml.workflow.casters.ParamCaster
   */
  protected function caster() {
    return new ToDouble();
  }

  /**
   * Test whole numbers
   */
  #[@test, @values([['1', 1.0], ['-1', -1.0], ['0', 0.0]])]
  public function wholeNumbers($input, $expect) {
    $this->assertEquals($expect, $this->castValue($input), $input);
  }

  /**
   * Test fractional numbers
   */
  #[@test, @values([['0.5', 0.5], ['-0.5', -0.5], ['.5', 0.5]])]
  public function fractionalNumbers($input, $expect) {
    $this->assertEquals($expect, $this->castValue($input), $input);
  }

  /**
   * Test fractional numbers
   */
  #[@test, @values([['0,5', 0.5], ['-0,5', -0.5], [',5', 0.5]])]
  public function fractionalNumbersWithCommas($input, $expect) {
    $this->assertEquals($expect, $this->castValue($input), $input);
  }

  /**
   * Test empty input
   */
  #[@test]
  public function emptyInput() {
    $this->assertEquals(0.0, $this->castValue(''));
  }
}
