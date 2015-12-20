<?php namespace scriptlet\unittest\workflow;

use lang\IllegalArgumentException;
use scriptlet\xml\workflow\casters\ToEmailAddress;


/**
 * Test the ToEmailAddress caster
 *
 * @see  xp://peer.mail.InternetAddress
 * @see  xp://scriptlet.unittest.workflow.AbstractCasterTest
 * @see  xp://scriptlet.xml.workflow.casters.ToEmailAddress
 */
class ToEmailAddressTest extends AbstractCasterTest {

  /**
   * Return the caster
   *
   * @return  scriptlet.xml.workflow.casters.ParamCaster
   */
  protected function caster() {
    return new ToEmailAddress();
  }

  /**
   * Test numerous valid email addresses
   */
  #[@test, @values(['xp@example.com', 'xp-cvs@example.com'])]
  public function validEmailAdresses($email) {
    $this->assertEquals(new \peer\mail\InternetAddress($email), $this->castValue($email));
  }

  /**
   * Test input without an @ sign
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function stringWithoutAt() {
    $this->castValue('FOO');
  }

  /**
   * Test empty input
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function emptyInput() {
    $this->castValue('');
  }

  /**
   * Test "@" by itself
   */
  #[@test, @expect(IllegalArgumentException::class)]
  public function onlyAtSign() {
    $this->castValue('@');
  }
}
