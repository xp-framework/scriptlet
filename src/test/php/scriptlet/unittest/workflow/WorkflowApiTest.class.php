<?php namespace scriptlet\unittest\workflow;

use unittest\TestCase;
use scriptlet\xml\workflow\AbstractXMLScriptlet;
use scriptlet\xml\workflow\AbstractState;
use scriptlet\unittest\workflow\mock\MockRequest;
use scriptlet\unittest\workflow\mock\MockResponse;

/**
 * Scriptlet/Workflow API test case
 *
 * @see   xp://scriptlet.xml.workflow.AbstractXMLScriptlet
 * @see   xp://scriptlet.xml.workflow.AbstractState
 */
class WorkflowApiTest extends TestCase {
  protected $scriptlet  = null;

  /**
   * Setup method.
   *
   */
  public function setUp() {
    $this->scriptlet= new AbstractXMLScriptlet($this->getClass()->getPackage()->getName());
    $this->scriptlet->init();
  }

  /**
   * Teardown method.
   *
   */
  public function tearDown() {
    $this->scriptlet->finalize();
  }
  
  /**
   * Process a request
   *
   * @param   scriptlet.unittest.mock.MockRequest
   * @return  scriptlet.unittest.mock.MockResponse
   */
  public function process($request) {
    $request->initialize();
    $response= new MockResponse();
    $this->scriptlet->processWorkflow($request, $response);
    return $response;
  }

  /**
   * Tests that a state's setup() and process() methods are called.
   *
   */
  #[@test]
  public function setupAndProcessCalled() {
    $request= new MockRequest($this->scriptlet->package, ucfirst($this->name), '{
      public $called= array();
      
      public function setup($request, $response, $context) {
        parent::setup($request, $response, $context);
        $this->called["setup"]= TRUE;
      }

      public function process($request, $response, $context) {
        $this->called["process"]= TRUE;
      }
    }');
    $this->process($request);      
    $this->assertTrue($request->state->called['setup']);
    $this->assertTrue($request->state->called['process']);
  }
  
  /**
   * Tests IllegalAccessException thrown in state setup
   *
   */
  #[@test]
  public function illegalAccessInStateSetup() {
    $request= new MockRequest($this->scriptlet->package, ucfirst($this->name), '{
      public function setup($request, $response, $context) {
        parent::setup($request, $response, $context);
        throw new \lang\IllegalAccessException("Access denied");
      }
    }');
    try {
      $this->process($request);
      $this->fail('Expected exception not caught', null, 'ScriptletException');
    } catch (\scriptlet\ScriptletException $expected) {
      $this->assertEquals(403, $expected->statusCode);
      $this->assertInstanceOf('lang.IllegalAccessException', $expected->getCause());
    }
  }

  /**
   * Tests IllegalAccessException thrown in state setup
   *
   */
  #[@test]
  public function illegalStateInStateSetup() {
    $request= new MockRequest($this->scriptlet->package, ucfirst($this->name), '{
      public function setup($request, $response, $context) {
        parent::setup($request, $response, $context);
        throw new \lang\IllegalStateException("Misconfigured");
      }
    }');
    try {
      $this->process($request);
      $this->fail('Expected exception not caught', null, 'ScriptletException');
    } catch (\scriptlet\ScriptletException $expected) {
      $this->assertEquals(500, $expected->statusCode);
      $this->assertInstanceOf('lang.IllegalStateException', $expected->getCause());
    }
  }

  /**
   * Tests IllegalArgumentException thrown in state setup
   *
   */
  #[@test]
  public function illegalArgumentInStateSetup() {
    $request= new MockRequest($this->scriptlet->package, ucfirst($this->name), '{
      public function setup($request, $response, $context) {
        parent::setup($request, $response, $context);
        throw new \lang\IllegalArgumentException("Query string format");
      }
    }');
    try {
      $this->process($request);
      $this->fail('Expected exception not caught', null, 'ScriptletException');
    } catch (\scriptlet\ScriptletException $expected) {
      $this->assertEquals(406, $expected->statusCode);
      $this->assertInstanceOf('lang.IllegalArgumentException', $expected->getCause());
    }
  }

  #[@test]
  public function cancelFurtherProcessingInSetup() {
    $request= new MockRequest($this->scriptlet->package, ucfirst($this->name), '{
      public $called= [
        "setup" => false,
        "process" => false
      ];
      
      public function setup($request, $response, $context) {
        parent::setup($request, $response, $context);
        $this->called["setup"]= TRUE;
        return false;
      }

      public function process($request, $response, $context) {
        $this->called["process"]= TRUE;
      }
    }');
    $this->process($request);      
    $this->assertTrue($request->state->called['setup']);
    $this->assertFalse($request->state->called['process']);
  }
}
