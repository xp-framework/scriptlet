<?php namespace scriptlet\xml\workflow;

use util\log\Traceable;

// Handler stati
define('HANDLER_SETUP',       'setup');
define('HANDLER_FAILED',      'failed');
define('HANDLER_INITIALIZED', 'initialized');
define('HANDLER_ERRORS',      'errors');
define('HANDLER_SUCCESS',     'success');
define('HANDLER_RELOADED',    'reloaded');
define('HANDLER_CANCELLED',   'cancelled');

// Value storages
define('HVAL_PERSISTENT',  0x0000);
define('HVAL_FORMPARAM',   0x0001);

/**
 * Handler
 *
 * @see      xp://scriptlet.xml.workflow.AbstractState#addHandler
 * @purpose  Base class
 */
class AbstractHandler implements Traceable {
  public
    $cat              = null,
    $wrapper          = null,
    $values           = [HVAL_PERSISTENT => [], HVAL_FORMPARAM => []],
    $errors           = [],
    $identifier       = '',
    $name             = '',
    $requestOverride  = false;

  /**
   * Constructor
   *
   */
  public function __construct() {
    $this->name= strtolower(typeof($this)->getSimpleName());
  }

  /**
   * Set a trace for debugging
   *
   * @param   util.log.LogCategory cat
   * @see     xp://util.log.Traceable
   */
  public function setTrace($cat) {
    $this->cat= $cat;
  }

  /**
   * Creates a string representation of this handler
   *
   * @return  string
   */
  public function toString() {
    $s= sprintf(
      "%s@{\n".
      "  [name               ] %s\n".
      "  [identifier         ] %s\n".
      "  [wrapper            ] %s\n",
      nameof($this),
      $this->name,
      $this->identifier,
      $this->wrapper ? nameof($this->wrapper) : '(null)'
    );
    foreach (array_keys($this->values[HVAL_PERSISTENT]) as $key) {
      $s.= sprintf("  [%-20s] %s\n", $key, typeof($this->values[$key])->getName());
    }
    return $s.'}';
  }

  /**
   * Set Wrapper
   *
   * @param   scriptlet.xml.workflow.Wrapper wrapper
   */
  public function setWrapper($wrapper) {
    $this->wrapper= $wrapper;
  }

  /**
   * Get Wrapper
   *
   * @return  scriptlet.xml.workflow.Wrapper
   */
  public function getWrapper() {
    return $this->wrapper;
  }

  /**
   * Check whether a wrapper is present
   *
   * @return  bool
   */
  public function hasWrapper() {
    return null != $this->wrapper;
  }
  
  /**
   * Set a value by a specified name
   *
   * @param   string name
   * @param   var value
   */
  public function setValue($name, $value) {
    $this->values[HVAL_PERSISTENT][$name]= $value;
  }

  /**
   * Set a form value by a specified name
   *
   * @param   string name
   * @param   var value
   */
  public function setFormValue($name, $value) {
    $this->values[HVAL_FORMPARAM][$name]= $value;
  }
  
  /**
   * Return all values
   *
   * @return  array
   */
  public function getValues() {
    return $this->values[HVAL_PERSISTENT];
  }

  /**
   * Return all form values
   *
   * @return  array
   */
  public function getFormValues() {
    return $this->values[HVAL_FORMPARAM];
  }
  
  /**
   * Retrieve a value by its name
   *
   * @param   string name
   * @param   var default default NULL
   * @return  var value
   */
  public function getValue($name, $default= null) {
    return (isset($this->values[HVAL_PERSISTENT][$name]) 
      ? $this->values[HVAL_PERSISTENT][$name] 
      : $default
    );
  }
  
  /**
   * Retrieve a form value by its name
   *
   * @param   string name
   * @param   var default default NULL
   * @return  var value
   */
  public function getFormValue($name, $default= null) {
    return (isset($this->values[HVAL_FORMPARAM][$name]) 
      ? $this->values[HVAL_FORMPARAM][$name] 
      : $default
    );
  }

  /**
   * Get name
   *
   * @return  string
   */
  public function getName() {
    return $this->name;
  }

  /**
   * Get identifier. Returns name in this default implementation.
   * Overwrite in subclasses.
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request
   * @param   scriptlet.xml.Context context
   * @return  string
   */
  public function identifierFor($request, $context) {
    return $this->name;
  }

  /**
   * Add an error
   *
   * @param   string code
   * @param   string field default '*'
   * @param   var info default NULL
   */
  public function addError($code, $field= '*', $info= null) {
    $this->errors[]= [$code, $field, $info];
    return false;
  }
  
  /**
   * Check whether errors occured
   *
   * @return  bool
   */
  public function errorsOccured() {
    return !empty($this->errors);
  }
  
  /**
   * Returns whether this handler is active. Returns TRUE in this 
   * default implementation in case the request has a parameter named
   * __handler whose value contains this handler's name.
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request
   * @param   scriptlet.xml.Context context
   * @return  bool
   */
  public function isActive($request, $context) {
    return ($request->getParam('__handler') == $this->identifier);
  }
  
  /**
   * Set up this handler. Called when this handler has not yet been
   * registered to the session
   *
   * Return TRUE to indicate success, FALSE to signal failure.
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.Context context
   * @return  bool
   */
  public function setup($request, $context) { 
    return true;
  }

  /**
   * Retrieve whether this handler needs data 
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.Context context
   * @return  bool
   */
  public function needsData($request, $context) {
    return true;
  }  
  
  /**
   * Retrieve whether this handler needs to be cancelled.
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.Context context
   * @return  bool
   */
  public function needsCancel($request, $context) {
    return false;
  }    

  /**
   * Handle error condition
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.Context context
   */
  public function handleErrorCondition($request, $context) {
    return false;
  }
  
  /**
   * Perform cancellation of this handler.
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.Context context
   */
  public function handleCancellation($request, $context) { }

  /**
   * Handle submitted data
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.Context context
   */
  public function handleSubmittedData($request, $context) {
    return false;
  }
  
  /**
   * Finalize this handler
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.XMLScriptletResponse response 
   * @param   scriptlet.xml.Context context
   */
  public function finalize($request, $response, $context) { }
  
  /**
   * Finalize this handler when the page was reloaded
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request
   * @param   scriptlet.xml.XMLScriptletResponse response
   * @param   scriptlet.xml.Context context
   */
  public function reloaded($request, $response, $context) {
    $this->finalize($request, $response, $context);
  }
  
  /**
   * Finalize this handler for cancellation
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request
   * @param   scriptlet.xml.XMLScriptletResponse response
   * @param   scriptlet.xml.Context context
   */
  public function cancelled($request, $response, $context) {
    $this->finalize($request, $response, $context);
  }
  
  /**
   * Finalize this handler with success
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request
   * @param   scriptlet.xml.XMLScriptletResponse response
   * @param   scriptlet.xml.Context context
   */
  public function success($request, $response, $context) {
    $this->finalize($request, $response, $context);
  }
}
