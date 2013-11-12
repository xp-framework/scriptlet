<?php namespace scriptlet\xml\workflow\checkers;

use xml\Node;
use xml\parser\XMLParser;

/**
 * Removes illegal characters from given string(s) and checks input for well formed XML
 *
 * @see  xp://scriptlet.unittest.workflow.WellformedXMLCheckerTest
 */
class WellformedXMLChecker extends ParamChecker {

  /**
   * Cast a given value
   *
   * Error codes returned are:
   * <ul>
   *   <li>invalid_chars - if input contains characters not allowed for XML</li>
   *   <li>not_well_formed - if input cannot be parsed to XML</li>
   * </ul>
   *
   * @param   array value
   * @return  string error or array on success
   */
  public function check($value) { 
    foreach ($value as $v) {
      if (strlen($v) > strcspn($v, Node::XML_ILLEGAL_CHARS)) return 'invalid_chars';
      try {
        $p= new XMLParser();
        $p->parse('<doc>'.$v.'</doc>');
      } catch (\xml\XMLFormatException $e) {
        return 'not_well_formed';
      }
    }
    
    return null;
  }
}
