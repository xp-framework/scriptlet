<?php namespace scriptlet\unittest\workflow;

use unittest\TestCase;
use scriptlet\xml\workflow\casters\ToFileData;


/**
 * Test the ToFileData caster
 *
 * @see       xp://scriptlet.xml.workflow.casters.ToFileData
 */
class ToFileDataTest extends TestCase {

  /**
   * Return the caster
   *
   * @return  scriptlet.xml.workflow.casters.ParamCaster
   */
  protected function caster() {
    return new ToFileData();
  }

  /**
   * Test single file upload
   *
   */
  #[@test]
  public function singleFileUpload() {
    $data= [
      'name' => 'test.jpg',
      'type' => 'image/jpeg',
      'tmp_name' => '/tmp/php1234',
      'error' => UPLOAD_ERR_OK,
      'size' => 12345
    ];

    $casted= $this->caster()->castValue($data);
    $this->assertInstanceOf('var[]', $casted);
    $this->assertEquals(1, count($casted));
    $this->assertInstanceOf('scriptlet.xml.workflow.FileData', $casted[0]);
    $this->assertInstanceOf('io.File', $casted[0]->getFile());
    $this->assertEquals(
      [new \scriptlet\xml\workflow\FileData('test.jpg', 'image/jpeg', 12345, '/tmp/php1234')],
      $casted
    );
  }

  /**
   * Multiple files upload
   *
   */
  #[@test]
  public function multipleFilesUpload() {
    $data= [
      'name' => ['test.jpg', 'test2.jpg'],
      'type' => ['image/jpeg', 'image/jpeg'],
      'tmp_name' => ['/tmp/php1234', '/tmp/php5678'],
      'error' => [UPLOAD_ERR_OK, UPLOAD_ERR_OK],
      'size' => [12345, 67890]
    ];

    $casted= $this->caster()->castValue($data);
    $this->assertInstanceOf('var[]', $casted);
    $this->assertEquals(2, count($casted));
    
    $this->assertInstanceOf('scriptlet.xml.workflow.FileData', $casted[0]);
    $this->assertInstanceOf('io.File', $casted[0]->getFile());
    $this->assertInstanceOf('scriptlet.xml.workflow.FileData', $casted[1]);
    $this->assertInstanceOf('io.File', $casted[1]->getFile());
    
    $this->assertEquals(
      [
        new \scriptlet\xml\workflow\FileData('test.jpg', 'image/jpeg', 12345, '/tmp/php1234'), 
        new \scriptlet\xml\workflow\FileData('test2.jpg', 'image/jpeg', 67890, '/tmp/php5678')
      ],
      $casted
    );
  }
}
