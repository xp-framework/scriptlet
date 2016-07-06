<?php namespace scriptlet\unittest;

use lang\IllegalStateException;
use scriptlet\HttpSession;
use unittest\TestCase;
use util\Date;
use io\Folder;

class HttpSessionTest extends TestCase {
  private $session= null;

  /** @return scriptlet.HttpSession */
  private function _session() { return new HttpSession(); }

  /**
   * Setup testcase environment for next testcase
   *
   * @return void
   */
  public function setUp() {
    $this->session= $this->_session();
  }
  
  /**
   * Cleanup last testcase run. Invalidate old sessions and
   * remove environment leftovers
   *
   * @return void
   */
  public function tearDown() {
    if ($this->session->isValid()) {
      $this->session->invalidate();
    }
  }
  
  /**
   * Session mess: Set session save path
   *
   * @return void
   */
  #[@beforeClass]
  public static function setSessionSavePath() {
    session_save_path(getcwd());
  }
  
  /**
   * Session mess: Cleanup session save path
   *
   * @return void
   */
  #[@afterClass]
  public static function cleanupSessionSavePath() {
    $f= new Folder(session_save_path());
    while ($e= $f->getEntry()) {
      if (0 === strncmp('sess_', $e, 5)) unlink($f->getURI().$e);
    }
  }

  #[@test]
  public function create() {
    $this->session->initialize(null);
    $this->assertTrue($this->session->isValid());
  }
  
  #[@test]
  public function isNew() {
    $this->session->initialize(null);
    $this->assertTrue($this->session->isNew());
  }
  
  #[@test]
  public function reattach() {
    $this->session->initialize(null);
    
    $copy= new HttpSession();
    $copy->initialize($this->session->getId());
    $this->assertFalse($copy->isNew());
  }
  
  #[@test]
  public function invalidate() {
    $this->session->initialize(null);
    $this->assertTrue($this->session->isValid());
    
    $this->session->invalidate();
    $this->assertFalse($this->session->isValid());
  }

  #[@test]
  public function valueNames() {
    $this->session->initialize(null);
    $this->session->putValue('foo', 1);
    $this->session->putValue('bar', 2);
    
    $this->assertEquals(
      ['foo', 'bar'],
      $this->session->getValueNames()
    );
  }

  #[@test]
  public function putDoesNotOverwriteValue() {
    $this->session->initialize(null);
    $fixture= new \lang\Object();
    $hash= $fixture->hashCode();
    $this->session->putValue('foo', $fixture);
    $this->assertInstanceOf('lang.Object', $fixture);
    $this->assertEquals($hash, $fixture->hashCode());
  }
  
  #[@test]
  public function reset() {
    $this->session->initialize(null);
    $this->session->putValue('foo', $f= null);
    $this->assertEquals(1, sizeof($this->session->getValueNames()));
    
    $this->session->reset();
    $this->assertEquals(0, sizeof($this->session->getValueNames()));
  }

  #[@test, @values([
  #  [0], [1], [1.0], [-0.5], [true], [false],
  #  [null], [''], ['Test'],
  #  [[]], [[1, 2, 3]], [['test' => 'Bar']],
  #  [new Date('1977-12-14')]
  #])]
  public function roundtrip($value) {
    $this->session->initialize(null);
    $this->session->putValue('foo', $value);
    $this->assertEquals($value, $this->session->getValue('foo'));
  }
  
  #[@test, @ignore('Creates an unremovable file sess_ILLEGALSESSIONID')]
  public function testIllegalConstruct() {
    $this->assertFalse($this->session->initialize('ILLEGALSESSIONID'));
  }
  
  #[@test, @ignore('Creates an unremovable file sess_ILLEGALSESSIONID'), @expect(IllegalStateException::class)]
  public function testIllegalSessionAccess() {
    $this->session->initialize('ILLEGALSESSIONID');
    $this->session->putValue('foo', $f= 3);
  }
}
