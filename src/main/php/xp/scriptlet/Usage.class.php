<?php namespace xp\scriptlet;

/**
 * Scriptlet usage
 */
class Usage {

  /**
   * Entry point method
   *
   * @param   string[] args
   */
  public static function main(array $args) {
    \util\cmd\Console::$err->writeLine((new \lang\XPClass(__CLASS__))->getPackage()->getResource($args[0]));
    return 0xFF;
  }
}
