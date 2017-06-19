<?php namespace scriptlet\xml\portlet;

/**
 * Abstract portlet
 *
 * @see      xp://scriptlet.xml.portlet.Portlet
 * @purpose  Abstract base class
 */
class AbstractPortlet implements Portlet {
  public
    $name       = '',
    $properties = null,
    $layout     = null,
    $attributes = [];

  /**
   * Constructor
   *
   */
  public function __construct() {
    $this->setName(substr(typeof($this)->getSimpleName(), 0, -1* strlen('Portlet')));
  }

  /**
   * Get portlet's name
   *
   * @return  string name
   */
  public function getName() { 
    return $this->name;
  }
  
  /**
   * Set portlet's name
   *
   * @param   string name
   */
  public function setName($name) { 
    $this->name= $name;
  }
  
  /**
   * Set layout of portlet usage
   *
   * @param   string layout
   */
  public function setLayout($layout) {
    $this->layout= $layout;
  }
  
  /**
   * Get layout of portlet usage
   *
   * @return  string layout
   */
  public function getLayout() {
    return $this->layout;
  }

  /**
   * Initialize portlet
   *
   * @param   util.Properties properties
   */
  public function init($properties) { 
    $this->properties= $properties;
  }
  
  /**
   * Set an attribut by name
   *
   * @param   string name
   * @param   var value
   * @param   scriptlet.xml.portlet.RunData rundata
   */
  public function setAttribute($name, $value, $rundata) {
    $this->attributes[$name]= $value;
  }

  /**
   * Get an attribute by name. Returns default value if the specified 
   * value is non-existant.
   *
   * @param   string name
   * @param   var default
   * @param   scriptlet.xml.portlet.RunData rundata
   * @return  var
   */
  public function getAttribute($name, $default, $rundata) {
    if (!array_key_exists($name, $this->attributes)) return $default;

    return $this->attributes[$name];
  }
  
  /**
   * Retrieve portlet content as Node object
   *
   * @param   scriptlet.xml.portlet.RunData rundata
   * @return  xml.Node
   */
  public function getContent($rundata) { }

  /**
   * Retrieve whether this portlet provides customization mechanisms.
   * Returns FALSE in this default implementation
   *
   * @return  bool
   */
  public function providesCustomization() { 
    return false;
  }

} 
