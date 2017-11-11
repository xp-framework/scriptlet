<?php namespace xp\scriptlet;

/**
 * Web debugging constants
 *
 * @see    xp://xp.scriptlet.WebApplication#setDebug
 */
abstract class WebDebug {
  const
    NONE        = 0x0000,
    XML         = 0x0001,
    ERRORS      = 0x0002,
    STACKTRACE  = 0x0004,
    TRACE       = 0x0008;
  
  /**
   * Returns a debug flag by a given name
   *
   * @param   string name
   * @return  int
   * @throws  lang.IllegalArgumentException
   */
  public static function flagNamed($name) {
    static $lookup= [
      'NONE'        => self::NONE,
      'XML'         => self::XML,
      'ERRORS'      => self::ERRORS,
      'STACKTRACE'  => self::STACKTRACE,
      'TRACE'       => self::TRACE,
    ];

    if (!isset($lookup[$name])) {
      throw new \lang\IllegalArgumentException('No flag named WebDebug::'.$name);
    }
    return $lookup[$name];
  }
  
  /**
   * Returns the names
   *
   * @param   int flags
   * @return  string[] names
   */
  public static function namesOf($flags) {
    static $lookup= [
      self::XML         => 'XML',
      self::ERRORS      => 'ERRORS',
      self::STACKTRACE  => 'STACKTRACE',
      self::TRACE       => 'TRACE',
    ];
  
    if (0 === $flags) return ['NONE'];
    $names= [];
    foreach ($lookup as $flag => $name) {
      $flags & $flag && $names[]= $name;
    }
    return $names;
  }
}
