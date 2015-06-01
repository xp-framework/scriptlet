<?php namespace scriptlet\unittest\workflow;

use scriptlet\xml\workflow\casters\ToUnixLineBreaks;

/**
 * Test the ToUnixLineBreaks caster
 *
 * @see   xp://net.xp_framework.unittest.scriptlet.workflow.AbstractCasterTest
 * @see   scriptlet.xml.workflow.casters.ToUnixLineBreaks
 */
class ToUnixLineBreaksTest extends AbstractCasterTest {

  /**
   * Return the caster
   *
   * @return  scriptlet.xml.workflow.casters.ParamCaster
   */
  protected function caster() {
    return new ToUnixLineBreaks();
  }

  #[@test]
  public function emptyValue() {
    $this->assertEquals("", $this->castValue(""));
  }

  #[@test]
  public function singleUnixLineBreak() {
    $this->assertEquals("\n", $this->castValue("\n"));
  }

  #[@test]
  public function nonFullWindowsLineBreak() {
    $this->assertEquals("test\rtest", $this->castValue("test\rtest"));
  }

  #[@test]
  public function doubleUnixLineBreak() {
    $this->assertEquals("\n\n", $this->castValue("\n\n"));
  }

  #[@test]
  public function singleWindowsLineBreak() {
    $this->assertEquals("\n", $this->castValue("\r\n"));
  }

  #[@test]
  public function doubleWindowsLineBreak() {
    $this->assertEquals("\n\n", $this->castValue("\r\n\r\n"));
  }

  #[@test]
  public function mixedLineBreaks() {
    $this->assertEquals("\n\n\n\n", $this->castValue("\r\n\n\r\n\n"));
  }

  #[@test]
  public function lineBreaksWithText() {
    $this->assertEquals("First line\nSecond line\n", $this->castValue("First line\r\nSecond line\r\n"));
  }

}
