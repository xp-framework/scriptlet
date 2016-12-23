<?php namespace scriptlet\unittest\workflow;

use scriptlet\xml\workflow\Wrapper;
use scriptlet\xml\workflow\Handler;
use scriptlet\xml\workflow\Context;
use scriptlet\xml\XMLScriptletRequest;
use util\Date;

/**
 * TestCase
 *
 * @see  xp://scriptlet.xml.workflow.Wrapper
 */
class WrapperTest extends \unittest\TestCase {
  protected
    $wrapper= null,
    $handler= null;
 
  /**
   * Sets up test case
   *
   */
  public function setUp() {
    $this->wrapper= new Wrapper();
    $this->handler= newinstance('scriptlet.xml.workflow.Handler', [], '{}');
    $this->handler->setWrapper($this->wrapper);
    
    // Register parameters
    $this->wrapper->registerParamInfo(
      'orderdate',
      OCCURRENCE_OPTIONAL,
      new Date('1977-12-14'),
      ['scriptlet.xml.workflow.casters.ToDate']
    );
    $this->wrapper->registerParamInfo(
      'shirt_size',
      OCCURRENCE_UNDEFINED,
      null,                // No default, required attribute
      null,                // No cast necessary
      null,                // No precheck necessary, non-empty suffices
      ['scriptlet.xml.workflow.checkers.OptionChecker', ['S', 'M', 'L', 'XL']]
    );
    $this->wrapper->registerParamInfo(
      'shirt_qty',
      OCCURRENCE_UNDEFINED,
      null,                // No default, required attribute
      ['scriptlet.xml.workflow.casters.ToInteger'],
      ['scriptlet.xml.workflow.checkers.NumericChecker'],
      ['scriptlet.xml.workflow.checkers.IntegerRangeChecker', 1, 10]
    );
    $this->wrapper->registerParamInfo(
      'notify_me',
      OCCURRENCE_OPTIONAL | OCCURRENCE_MULTIPLE,
      [],
      null,
      null,
      null,
      'core:string',
      ['process', 'send']
    );
    $this->wrapper->registerParamInfo(
      'options',
      OCCURRENCE_OPTIONAL | OCCURRENCE_MULTIPLE,
      [0, 0],
      ['scriptlet.xml.workflow.casters.ToInteger']
    );
    $this->wrapper->registerParamInfo(
      'person_ids',
      OCCURRENCE_MULTIPLE,
      null,
      ['scriptlet.xml.workflow.casters.ToInteger']
    );
  }
  
  /**
   * Test the getParamNames() method
   *
   */
  #[@test]
  public function getParamNames() {
    $this->assertEquals(
      ['orderdate', 'shirt_size', 'shirt_qty', 'notify_me', 'options', 'person_ids'],
      $this->wrapper->getParamNames()
    );
  }

  /**
   * Test the getParamInfo() method
   *
   */
  #[@test]
  public function orderDateParamInfo() {
    $this->assertEquals(OCCURRENCE_OPTIONAL, $this->wrapper->getParamInfo('orderdate', PARAM_OCCURRENCE));
    $this->assertEquals(new Date('1977-12-14'), $this->wrapper->getParamInfo('orderdate', PARAM_DEFAULT));
    $this->assertEquals(null, $this->wrapper->getParamInfo('orderdate', PARAM_PRECHECK));
    $this->assertEquals(null, $this->wrapper->getParamInfo('orderdate', PARAM_POSTCHECK));
    $this->assertEquals('core:string', $this->wrapper->getParamInfo('orderdate', PARAM_TYPE));
    $this->assertEquals([], $this->wrapper->getParamInfo('orderdate', PARAM_VALUES));
    $this->assertInstanceOf('scriptlet.xml.workflow.casters.ToDate', $this->wrapper->getParamInfo('orderdate', PARAM_CASTER));
  }

  /**
   * Test the getParamInfo() method
   *
   */
  #[@test]
  public function shirtSizeParamInfo() {
    $this->assertEquals(OCCURRENCE_UNDEFINED, $this->wrapper->getParamInfo('shirt_size', PARAM_OCCURRENCE));
    $this->assertEquals(null, $this->wrapper->getParamInfo('shirt_size', PARAM_DEFAULT));
    $this->assertEquals(null, $this->wrapper->getParamInfo('shirt_size', PARAM_PRECHECK));
    $this->assertEquals(null, $this->wrapper->getParamInfo('shirt_size', PARAM_CASTER));
    $this->assertEquals('core:string', $this->wrapper->getParamInfo('shirt_size', PARAM_TYPE));
    $this->assertEquals([], $this->wrapper->getParamInfo('shirt_size', PARAM_VALUES));
    $this->assertInstanceOf('scriptlet.xml.workflow.checkers.OptionChecker',$this->wrapper->getParamInfo('shirt_size', PARAM_POSTCHECK));
  }

  /**
   * Test the getValue() method
   *
   */
  #[@test]
  public function getValue() {
    $this->assertEquals(null, $this->wrapper->getValue('orderdate'));
  }

  /**
   * Test the setValue() method
   *
   */
  #[@test]
  public function setValue() {
    with ($d= Date::now()); {
      $this->wrapper->setValue('orderdate', $d);
      $this->assertEquals($d, $this->wrapper->getValue('orderdate'));
    }
  }
  
  /**
   * Helper method to simulate form submission
   *
   */
  protected function loadFromRequest($params= []) {
    $r= new XMLScriptletRequest();
    
    foreach ($params as $key => $value) {
      $r->setParam($key, $value);
    }
    $this->wrapper->load($r, $this->handler);
  }

  /**
   * Helper method to assert a certain form error is available.
   *
   * Will fail if either no errors have occured at all or if the 
   * given error can not be found.
   *
   * @throws    unittest.AssertionFailedError
   */
  protected function assertFormError($field, $code) {
    if (!$this->handler->errorsOccured()) {     // Catch border-case
      $this->fail('No errors have occured', null, $code.' in field '.$field);
    }

    foreach ($this->handler->errors as $error) {
      if ($error[0].$error[1] == $code.$field) return;
    }

    $this->fail(
      'Error '.$code.' in field '.$field.' not in formerrors', 
      $this->handler->errors, 
      '(exists)'
    );
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function defaultValueUsedForMissingValue() {
    $this->loadFromRequest([
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals(
      $this->wrapper->getParamInfo('orderdate', PARAM_DEFAULT), 
      $this->wrapper->getValue('orderdate')
    );
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function defaultValueUsedForEmptyValue() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals(
      $this->wrapper->getParamInfo('orderdate', PARAM_DEFAULT), 
      $this->wrapper->getValue('orderdate')
    );
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function valueUsed() {
    $this->loadFromRequest([
      'orderdate'  => '1977-12-14',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals(
      new Date('1977-12-14'),
      $this->wrapper->getValue('orderdate')
    );
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function missingSizeValue() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_qty'  => 1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFormError('shirt_size', 'missing');
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function missingQtyValue() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFormError('shirt_qty', 'missing');
  }
  
  /**
   * Test the load() method
   *
   */
  #[@test]
  public function malformedSizeValue() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => '@',
      'shirt_qty'  => 1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFormError('shirt_size', 'scriptlet.xml.workflow.checkers.OptionChecker.invalidoption');
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function malformedQtyValue() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => -1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFormError('shirt_qty', 'scriptlet.xml.workflow.checkers.IntegerRangeChecker.toosmall');
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function multipleMalformedValues() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => '@',
      'shirt_qty'  => -1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFormError('shirt_size', 'scriptlet.xml.workflow.checkers.OptionChecker.invalidoption');
    $this->assertFormError('shirt_qty', 'scriptlet.xml.workflow.checkers.IntegerRangeChecker.toosmall');
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function missingValueForMultipleSelection() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([], $this->wrapper->getValue('notify_me'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function emptyValueForMultipleSelection() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => [],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([], $this->wrapper->getValue('notify_me'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function valueForMultipleSelection() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals(['send'], $this->wrapper->getValue('notify_me'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function valuesForMultipleSelection() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send', 'process'],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals(['send', 'process'], $this->wrapper->getValue('notify_me'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleOptionalField() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'options'    => ['0010', '0020'],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([10, 20], $this->wrapper->getValue('options'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleOptionalFieldFirstEmpty() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'options'    => [null, '0020'],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([0, 20], $this->wrapper->getValue('options'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleOptionalAllEmpty() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'options'    => ['', ''],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([0, 0], $this->wrapper->getValue('options'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleOptionalParameterMissing() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([0, 0], $this->wrapper->getValue('options'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleField() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'options'    => ['0010', '0020'],
      'person_ids' => ['1549', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([1549, 1552], $this->wrapper->getValue('person_ids'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleFieldFirstEmpty() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'options'    => [null, '0020'],
      'person_ids' => ['', '1552']
    ]);
    $this->assertFalse($this->handler->errorsOccured());
    $this->assertEquals([0, 1552], $this->wrapper->getValue('person_ids'));
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleFieldAllEmpty() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'options'    => [null, '0020'],
      'person_ids' => []
    ]);
    $this->assertTrue($this->handler->errorsOccured());
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function castMultipleFieldParameterMissing() {
    $this->loadFromRequest([
      'orderdate'  => '',
      'shirt_size' => 'S',
      'shirt_qty'  => 1,
      'notify_me'  => ['send'],
      'options'    => [null, '0020']
    ]);
    $this->assertTrue($this->handler->errorsOccured());
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function missingFileUpload() {

    // Register "file_upload" param
    $this->wrapper->registerParamInfo(
      'file_upload',
      OCCURRENCE_UNDEFINED,
      null,
      ['scriptlet.xml.workflow.casters.ToFileData'],
      ['scriptlet.xml.workflow.checkers.FileUploadPrechecker'],
      null
    );

    $this->loadFromRequest([
      'orderdate'   => '',
      'shirt_size'  => 'S',
      'shirt_qty'   => 1,
      'notify_me'   => ['send'],
      'options'     => [null, '0020'],
      'person_ids'  => ['', '1552'],
      'file_upload' => [
        'name'     => '',
        'type'     => '',
        'tmp_name' => '',
        'error'    => UPLOAD_ERR_NO_FILE,
        'size'     => 0
      ]
    ]);
    $this->assertFormError('file_upload', 'missing');
  }

  /**
   * Test the load() method
   *
   */
  #[@test]
  public function ignoreMissingOptionalFileUpload() {

    // Register "file_upload" param
    $this->wrapper->registerParamInfo(
      'file_upload',
      OCCURRENCE_OPTIONAL,
      null,
      ['scriptlet.xml.workflow.casters.ToFileData'],
      ['scriptlet.xml.workflow.checkers.FileUploadPrechecker'],
      null
    );

    $this->loadFromRequest([
      'orderdate'   => '',
      'shirt_size'  => 'S',
      'shirt_qty'   => 1,
      'notify_me'   => ['send'],
      'options'     => [null, '0020'],
      'person_ids'  => ['', '1552'],
      'file_upload' => [
        'name'     => '',
        'type'     => '',
        'tmp_name' => '',
        'error'    => UPLOAD_ERR_NO_FILE,
        'size'     => 0
      ]
    ]);
    $this->assertFalse($this->handler->errorsOccured());
  }

  /**
   * Test the load() method for multiple files
   *
   */
  #[@test]
  public function multipleFileUpload() {
    $this->wrapper->registerParamInfo(
      'attachments',
      Wrapper::OCCURRENCE_MULTIPLE | OCCURRENCE_OPTIONAL,
      null,
      ['scriptlet.xml.workflow.casters.ToFileData'],
      ['scriptlet.xml.workflow.checkers.FileUploadPrechecker'],
      null
    );

    $this->loadFromRequest([
      'attachments' => [
        'name'     => ['test.txt', 'test2.txt'],
        'type'     => ['text/plain', 'text/plain'],
        'tmp_name' => ['/tmp/phpKLEGHj', '/tmp/phpKLEGew'],
        'error'    => [0,0],
        'size'     => [0,0]
      ]
    ]);
    $this->assertInstanceOf('array', $this->wrapper->getValue('attachments'));
    $this->assertInstanceOf(
      'scriptlet\xml\workflow\FileData', $this->wrapper->getValue('attachments')[0]
    );
  }

  #[@test]
  public function deepArrays() {
    $this->wrapper->registerParamInfo(
      'array',
      Wrapper::OCCURRENCE_MULTIPLE,
      null,
      [],
      [],
      null
    );
    $val= [
      'foo' => [1, 2, 3],
      'bar' => [4, 5, 6]
    ];
    $this->loadFromRequest([
      'array' => $val
    ]);
    $this->assertEquals($val, $this->wrapper->getValue('array'));
  }
}
