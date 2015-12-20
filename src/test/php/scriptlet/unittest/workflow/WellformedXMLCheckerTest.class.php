<?php namespace scriptlet\unittest\workflow;

use scriptlet\xml\workflow\checkers\WellformedXMLChecker;

/**
 * TestCase for WellformedXMLChecker
 */
class WellformedXMLCheckerTest extends \unittest\TestCase {
  protected $fixture= null;

  /**
   * Sets up test case
   */
  public function setUp() {
    $this->fixture= new WellformedXMLChecker();
  }
  
  #[@test]
  public function emptyInput() {
    $this->assertNull($this->fixture->check(['']));
  }
  
  #[@test]
  public function validXml() {
    $this->assertNull($this->fixture->check(['<document/>']));
  }
  
  #[@test]
  public function noRootNode() {
    $this->assertNull($this->fixture->check(['<node1/><node2/>']));
  }
  
  #[@test]
  public function notWellFormedXml() {
    $this->assertEquals(
      'not_well_formed',
      $this->fixture->check(['<outer><inner></outer>'])
    );
  }
  
  #[@test]
  public function invalidCharacters() {
    $this->assertEquals(
      'invalid_chars',
      $this->fixture->check(["\0"])
    );
  }
}
