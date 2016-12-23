<?php namespace scriptlet\xml\workflow;

// Obsolete
define('OCCURRENCE_UNDEFINED',    0x0000);
define('OCCURRENCE_OPTIONAL',     0x0001);
define('OCCURRENCE_MULTIPLE',     0x0002);
define('OCCURRENCE_PASSBEHIND',   0x0004);

// Obsolete
define('PARAM_OCCURRENCE', 'occurrence');
define('PARAM_DEFAULT',    'default');   
define('PARAM_PRECHECK',   'precheck');  
define('PARAM_CASTER',     'caster');    
define('PARAM_POSTCHECK',  'postcheck'); 
define('PARAM_TYPE',       'type');      
define('PARAM_VALUES',     'values');    

/**
 * Wrapper
 *
 * @see      xp://scriptlet.xml.workflow.Handler#setWrapper
 * @test     xp://scriptlet.unittest.workflow.WrapperTest
 * @purpose  Base class
 */
class Wrapper extends \lang\Object {
  const
    OCCURRENCE_UNDEFINED  = 0x0000,
    OCCURRENCE_OPTIONAL   = 0x0001,
    OCCURRENCE_MULTIPLE   = 0x0002,
    OCCURRENCE_PASSBEHIND = 0x0004;

  const
    PARAM_OCCURRENCE      = 'occurrence',
    PARAM_DEFAULT         = 'default',
    PARAM_PRECHECK        = 'precheck',
    PARAM_CASTER          = 'caster',
    PARAM_POSTCHECK       = 'postcheck',
    PARAM_TYPE            = 'type',
    PARAM_VALUES          = 'values';

  public
    $paraminfo    = [],
    $values       = [];

  /**
   * Set up this handler. Called when the handler has not yet been
   * registered to the session.
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.workflow.Handler handler
   * @param   scriptlet.xml.workflow.Context context
   */
  public function setup($request, $handler, $context) {
    foreach ($this->paraminfo as $name => $definitions) {
      
      // Pre-fill form value if a default is defined and the request
      // does not define such a parameter.
      //
      // Note: This will only happen when the handler itself is set up.
      if (isset($definitions[self::PARAM_DEFAULT]) && '' == $request->getParam($name, '')) {
        $request->setParam($name, $definitions[self::PARAM_DEFAULT]);
      }
      
      // If this is a pass-behind value, register it to the handler's 
      // values. "Pass-behind" means this value is retrieved from the 
      // session (where it has been registered to during this call)
      // rather than from the request data (GET / POST / COOKIE).
      if ($definitions[self::PARAM_OCCURRENCE] & self::OCCURRENCE_PASSBEHIND) {
        $handler->setValue($name, $request->getParam($name));
      }
    } 
  }
  
  /**
   * Retrieve a checker instance
   *
   * @param   array defines
   * @return  lang.Object
   */
  public function checkerInstanceFor($defines) {
    static $class= [];

    if (!$defines) return null;

    $name= array_shift($defines);
    try {
      if (!isset($class[$name])) $class[$name]= \lang\XPClass::forName($name);
    } catch (\lang\ClassNotFoundException $e) {
      unset($class[$name]);
      return null;
    }

    return call_user_func_array([$class[$name], 'newInstance'], $defines);
  }
  
  /**
   * Register definitions for a specified parameter
   *
   * Examples:
   * <code>
   *   // Order date, optional, retrieve as date object, defaulting to today
   *   $this->registerParamInfo(
   *     'orderdate',
   *     Wrapper::OCCURRENCE_OPTIONAL,
   *     Date::now(),
   *     array('scriptlet.xml.workflow.casters.ToDate')
   *   );
   *
   *   // T-Shirt size, may be either S, M, L or XL
   *   $this->registerParamInfo(
   *     'tshirt.size',
   *     Wrapper::OCCURRENCE_UNDEFINED,
   *     NULL,                // No default, required attribute
   *     NULL,                // No cast necessary
   *     NULL,                // No precheck necessary, non-empty suffices
   *     array('scriptlet.xml.workflow.checkers.OptionChecker', array('S', 'M', 'L', 'XL'))
   *   );
   *
   *   // Quantity check, must be numeric, must be in range 1 to 10
   *   $this->registerParamInfo(
   *     'tshirt.quantity',
   *     Wrapper::OCCURRENCE_UNDEFINED,
   *     NULL,                // No default, required attribute
   *     array('scriptlet.xml.workflow.casters.ToInteger'),
   *     array('scriptlet.xml.workflow.checkers.NumericChecker'),
   *     array('scriptlet.xml.workflow.checkers.IntegerRangeChecker', 1, 10)
   *   );
   * </code>
   *
   * @param   string name
   * @param   int occurrence default Wrapper::OCCURRENCE_UNDEFINED
   * @param   var default default NULL
   * @param   string[] caster default NULL
   * @param   string[] precheck default NULL
   * @param   string[] postcheck default NULL
   * @param   string type default 'core:string'
   * @param   array values default array()
   */
  public function registerParamInfo(
    $name, 
    $occurrence= self::OCCURRENCE_UNDEFINED,
    $default= null,
    $caster= null, 
    $precheck= null, 
    $postcheck= null,
    $type= 'core:string',
    $values= []
  ) {
    $this->paraminfo[$name]= [
      self::PARAM_OCCURRENCE => $occurrence,
      self::PARAM_DEFAULT    => $default,
      self::PARAM_PRECHECK   => $this->checkerInstanceFor($precheck),
      self::PARAM_CASTER     => $this->checkerInstanceFor($caster),
      self::PARAM_POSTCHECK  => $this->checkerInstanceFor($postcheck),
      self::PARAM_TYPE       => $type,
      self::PARAM_VALUES     => $values
    ];
  }

  /**
   * Retrieve parameter names
   *
   * @return  string[]
   */
  public function getParamNames() {
    return array_keys($this->paraminfo);
  }

  /**
   * Retrieve parameter info
   *
   * @param   string name
   * @param   string type one of of the PARAM_* constants
   * @return  var
   */
  public function getParamInfo($name, $type) {
    return $this->paraminfo[$name][$type];
  }
  
  /**
   * Retrieve a value by its name
   *
   * @param   string name
   * @param   var default default NULL
   * @return  var value
   */
  public function getValue($name, $default= null) {
    return isset($this->values[$name]) ? $this->values[$name] : $default;
  }

  /**
   * Set a value by its name
   *
   * @param   string name
   * @param   var value
   */
  public function setValue($name, $value) {
    $this->values[$name]= $value;
  }
  
  /**
   * Load request values from request data
   *
   * @param   scriptlet.xml.workflow.WorkflowScriptletRequest request 
   * @param   scriptlet.xml.workflow.Handler handler
   */
  public function load($request, $handler) { 
    foreach ($this->paraminfo as $name => $definitions) {

      // Retrieve the parameter's value from the request (or from the
      // handler's values, if it is passed behind the scenes).
      if ($definitions[self::PARAM_OCCURRENCE] & self::OCCURRENCE_PASSBEHIND) {
        $value= (array)$handler->getValue($name, '');
      } else if ($definitions[self::PARAM_OCCURRENCE] & self::OCCURRENCE_MULTIPLE) {
        $value= (array)$request->getParam($name, []);
      } else {
        $value= (array)$request->getParam($name, '');
      }
    
      // Check if the parameter is present (and evaluates to a non-empty
      // string). In case the definitions state this parameter is optional, 
      // it may be omitted and no further checks will be imposed on it.
      //
      // We use the "trick" of casting it to an array because request 
      // parameters might also come as an array. This way, we always get
      // an array, as casting an array to an array simply results in the
      // same array (breaking nothing) and casting scalars will end up in
      // an array with the scalar as the first element.
      //
      // If the value is validated and the occurrence definition contains
      // the string "multiple", the array will be preserved. Otherwise, the
      // first element will be copied to the values hash, thus making 
      // accessibility easy.
      if (
        (self::isFileUpload($value) && self::isEmptyFileUpload($value)) ||
        (!self::isFileUpload($value) && static::isEmptyValue($value))
      ) {
        if (!($definitions[self::PARAM_OCCURRENCE] & self::OCCURRENCE_OPTIONAL)) {
          $handler->addError('missing', $name);
          continue;
        }
        
        // Set it to the default value
        if ($definitions[self::PARAM_DEFAULT]) {
          if ($definitions[self::PARAM_OCCURRENCE] & self::OCCURRENCE_MULTIPLE) {
            $value= $definitions[self::PARAM_DEFAULT];
          } else {
            $value[key($value)]= $definitions[self::PARAM_DEFAULT];
          }
        }
      } else {
 
        // Run the precheck. This can be utilized for assertion-style checks
        // and to prevent casting (which may be expensive). For example, we 
        // needn't try "casting" the string "foo" to a peer.mail.InternetAddress
        // object as it doesn't even contain the "@".
        //
        // Pre- and postchecks return an error code or NULL if they are content
        if ($definitions[self::PARAM_PRECHECK]) {
          if (null !== ($code= call_user_func([$definitions[self::PARAM_PRECHECK], 'check'], $value))) {
            $handler->addError(nameof($definitions[self::PARAM_PRECHECK]).'.'.$code, $name);
            continue;
          }
        }

        // Cast the parameter if requested before doing any other checks 
        // on it. The casters return an array in case it succeeds. Any other
        // type indicates an error and will be used as informational data
        // for the form error (an exception message, for instance).
        if ($definitions[self::PARAM_CASTER]) {
          if (!is_array($value= call_user_func([$definitions[self::PARAM_CASTER], 'castValue'], $value))) {
            $handler->addError(nameof($definitions[self::PARAM_CASTER]).'.invalidcast', $name, $value);
            continue;
          }
        }

        // Now, run the postcheck. The postcheck receives the already casted
        // values.
        if ($definitions[self::PARAM_POSTCHECK]) {
          if (null !== ($code= call_user_func([$definitions[self::PARAM_POSTCHECK], 'check'], $value))) {
            $handler->addError(nameof($definitions[self::PARAM_POSTCHECK]).'.'.$code, $name);
            continue;
          }
        }
      }
      
      // If we get here, the parameter is validated. Copy the value into
      // the values hash which is publicly accessible.
      reset($value);
      if ($definitions[self::PARAM_OCCURRENCE] & self::OCCURRENCE_MULTIPLE) {
        $this->values[$name]= $value;
      } else if (isset($value[key($value)])) {
        $this->values[$name]= $value[key($value)];
      } else {
        $this->values[$name]= null;
      }
    }
  }

  /**
   * Check if the provided value is empty
   *
   * @param   var $value
   * @return  bool
   */
  protected static function isEmptyValue($value) {
    if (empty($value)) {
      return true;
    } else {
      foreach ($value as $item) {
        if (is_array($item) || strlen($item) > 0) {
          return false;
        }
      }
      return true;
    }
  }

  /**
   * Check if the provided value is an empty file upload field
   *
   * @param   var value
   * @return  bool
   */
  protected static function isEmptyFileUpload($value) {
    return (
      isset($value['name'])     && '' === $value['name'] &&
      isset($value['type'])     && '' === $value['type'] &&
      isset($value['tmp_name']) && '' === $value['tmp_name'] &&
      isset($value['error'])    && UPLOAD_ERR_NO_FILE === $value['error'] &&
      isset($value['size'])     && 0 === $value['size']
    );
  }

  /**
   * Check if the provided value is file upload field
   *
   * @param   var value
   * @return  bool
   */
  protected static function isFileUpload($value) {
    return (
      array_key_exists('name', $value) &&
      array_key_exists('type', $value) &&
      array_key_exists('tmp_name', $value) &&
      array_key_exists('error', $value) &&
      array_key_exists('size', $value)
    );
  }
}
