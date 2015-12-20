<?php namespace scriptlet\unittest;

use unittest\TestCase;
use util\Locale;
use scriptlet\LocaleNegotiator;


/**
 * TestCase
 *
 * @see      xp://scriptlet.LocaleNegotiator
 */
class LocaleNegotiatorTest extends TestCase {
  protected $fixture= null;

  /**
   * Create fixture
   *
   */
  public function setUp() {
    $this->fixture= new LocaleNegotiator(
      $acceptLanguage = 'de-at, de;q=0.75, en-us;q=0.50, en;q=0.25',
      $acceptCharset  = 'ISO-8859-1,utf-8;q=0.7,*;q=0.7'
    );
  }

  #[@test, @values([
  #  ['de_DE, en_UK', 'de_DE'],
  #  ['es_ES, de_DE', 'es_ES'],
  #  ['en_US'       , 'en_US'],
  #  ['fr_FR'       , 'en_US'],
  #  ['fr_FR, en_UK', 'en_UK'],
  #])]
  public function languageNegotiation($usersetting, $expected) {
    $this->assertEquals(
      new Locale($expected),
      (new LocaleNegotiator($usersetting))->getLocale(['de_DE', 'en_UK', 'en_US', 'es_ES'], 'en_US')
    );
  }

  #[@test]
  public function languagePreference() {
    $this->assertEquals(
      new Locale('de_AT'), 
      $this->fixture->getLocale(['de_AT', 'de_DE', 'en_US'], 'C')
    );
    $this->assertEquals(
      new Locale('de_AT'), 
      $this->fixture->getLocale(['de_DE', 'de_AT', 'en_US'], 'C')
    );
  }

  #[@test]
  public function secondLanguagePreference() {
    $this->assertEquals(
      new Locale('de_DE'), 
      $this->fixture->getLocale(['de_DE', 'en_US'], 'C')
    );
  }

  #[@test]
  public function thirdLanguagePreference() {
    $this->assertEquals(
      new Locale('en_US'), 
      $this->fixture->getLocale(['en_US'], 'C')
    );
  }

  #[@test]
  public function fourthLanguagePreference() {
    $this->assertEquals(
      new Locale('en_UK'), 
      $this->fixture->getLocale(['en_UK'], 'C')
    );
  }

  #[@test]
  public function defaultLanguage() {
    $this->assertEquals(
      new Locale('C'), 
      $this->fixture->getLocale(['es_ES'], 'C')
    );
  }

  #[@test]
  public function charsetPreference() {
    $this->assertEquals(
      'iso-8859-1',
      $this->fixture->getCharset(['iso-8859-1', 'utf-8', 'iso-8859-15'], 'ASCII')
    );
  }

  #[@test]
  public function secondCharsetPreference() {
    $this->assertEquals(
      'utf-8',
      $this->fixture->getCharset(['utf-8', 'iso-8859-15'], 'ASCII')
    );
  }

  #[@test]
  public function anyCharsetPreference() {
    $this->assertEquals(
      'iso-8859-15',
      $this->fixture->getCharset(['iso-8859-15'], 'ASCII')
    );
  }
}
