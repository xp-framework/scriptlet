<?php namespace scriptlet;

use util\Locale;

/**
 * Class to aid website internationalization based on the
 * Accept-Language and Accept-Charset headers.
 *
 * Basic usage example:
 * ```php
 * $negotiator= new LocaleNegotiator(
 *   'de-at, de;q=0.75, en-us;q=0.50, en;q=0.25',
 *   'utf-8,ISO-8859-1;q=0.7,*;q=0.7'
 * );
 * var_dump(
 *   $negotiator, 
 *   $negotiator->getLocale(
 *     $supported= ['de_DE', 'en_US'],
 *     $default= 'de_DE'
 *   ),
 *   $negotiator->getCharset(
 *     $supported= ['iso-8859-1', 'utf-8'],
 *     $default= 'utf-8'
 *   )
 * );
 * ```
 * 
 * Within a scriptlet, use the getHeader() method of the request
 * object to retrieve the values of the Accept-Language / Accept-Charset
 * headers and the setHeader() method of the response object to
 * indicate language negotation has took place.
 *
 * Abbreviated example:
 * ```php
 * public function doGet($req, $res) {
 *   $negotiator= new LocaleNegotiator(
 *     $req->getHeader('Accept-Language'), 
 *     $req->getHeader('Accept-Charset')
 *   );
 *   $locale= $negotiator->getLocale(['de_DE', 'en_US'), 'de_DE');
 *
 *   // [... Do whatever needs to be done for this language ...]
 *
 *   $res->setHeader('Content-Language', $locale->getLanguage());
 *   $res->setHeader('Vary', 'Accept-Language');
 * }
 * ```
 *
 * @test   xp://scriptlet.unittest.LocaleNegotiatorTest
 * @see    http://www.w3.org/Protocols/rfc2616/rfc2616-sec14.html
 */
class LocaleNegotiator extends \lang\Object {
  public $languages;
  public $charsets;

  /**
   * Constructor
   *
   * @param   string languages
   * @param   string charsets
   */
  public function __construct($languages, $charsets= '') {
    $this->languages= $this->_parse($languages);
    $this->charsets= $this->_parse($charsets);
  }
  
  /**
   * Retrieve locale
   *
   * @param   string[] supported
   * @param   string default default NULL
   * @return  util.Locale
   */
  public function getLocale(array $supported, $default= null) {
    $chosen= null;
    foreach ($this->languages as $lang => $q) {
      if (
        ($chosen= $this->_find($lang, $supported)) ||
        ($chosen= $this->_find($lang, $supported, 2))
      ) break;
    }
    return new Locale($chosen ?: $default);
  }
  
  /**
   * Retrieve charset
   *
   * @param   string[] supported
   * @param   string default default NULL
   * @return  string charset or default if none matches
   */
  public function getCharset(array $supported, $default= null) {
    $chosen= null;
    foreach ($this->charsets as $charset => $q) {
      if ($chosen= $this->_find($charset, $supported)) {
        break;
      } else if ('*' === $charset) {
        $chosen= $supported[0];
        break;
      }
    }
    return $chosen ? $chosen : $default;
  }
  
  /**
   * Private helper that parses a string of the following format:
   *
   * ```
   * Accept-Language: en,de;q=0.5
   * Accept-Language: en-UK;q=0.7, en-US;q=0.6, no;q=1.0, dk;q=0.8
   * Accept-Charset: ISO-8859-1,utf-8;q=0.7,*;q=0.7
   * ```
   *
   * @param   string str
   * @return  array values
   */
  protected function _parse($str) {
    $values= [];
    $i= 0;
    if ($t= strtok($str, ', ')) do {
      if (false === ($p= strpos($t, ';'))) {
        $value= strtr($t, '-', '_');
        $q= 1.0 - $i++ * 0.0001;
      } else {
        $value= strtr(substr($t, 0, $p), '-', '_');
        $q= (float)substr($t, $p + 3);    // skip ";q="
      }
      $values[strtolower($value)]= $q;
    } while ($t= strtok(', '));
    
    arsort($values, SORT_NUMERIC);
    return $values;
  }
  
  /**
   * Private helper that searches an array using strncasecmp as comparator
   *
   * @see     php://strncasecmp
   * @param   string value
   * @param   string[] array
   * @param   int len default -1
   * @return  string found or NULL to indicate it wasn't found
   */
  protected function _find($value, $array, $len= -1) {
    foreach ($array as $cmp) {
      if (0 === strncasecmp($value, $cmp, -1 === $len ? strlen($cmp) : $len)) return $cmp;
    }
    return null;
  }
}
