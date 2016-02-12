Scriptlets for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 7.0.1 / 2016-02-12

* Fixed problem delivering files with an incorrect content length when
  file size has changed between requests
  (@thekid)

## 7.0.0 / 2016-02-01

* Adjusted location of web/config help, see xp-runners/reference#32
  (@thekid)
* Added "xp web" command in alignment with xp-framework/rfc#303
  See https://github.com/xp-framework/rfc/issues/303#issuecomment-174542126
  (@thekid)
* Adopted semantic versioning according to xp-framework/rfc#300 - @thekid
* **Heads up: Changed HttpScriptletURL's values** from util.Hashmap to
  a map. This can affect subclasses, which will need to be refactored! 
  (@thekid)

## 6.3.2 / 2016-01-24

* Replaced calls to deprecated Properties::readHash() with readMap()
  (@thekid)

## 6.3.1 / 2016-01-24

* Fix code to use `nameof()` instead of the deprecated `getClassName()`
  method from lang.Generic. See xp-framework/core#120
  (@thekid)

## 6.3.0 / 2015-12-20

* **Heads up: Dropped PHP 5.4 support**. *Note: As the main source is not
  touched, unofficial PHP 5.4 support is still available though not tested
  with Travis-CI*.
  (@thekid)

## 6.2.3 / 2015-11-08

* Added forward compatibility with XP 6.6.0 - @thekid

## 6.2.2 / 2015-07-12

* Added forward compatibility with XP 6.4.0 - @thekid

## 6.2.1 / 2015-06-23

* Added support for event-based server via `-m event`. The event based
  server is based on [PECL/event](http://pecl.php.net/package/event).
  (@thekid)
* Added possibility to pass arguments to the server implementation in the
  "-m" command line switch: `xpws -c com.example.shorturl.Api -m prefork,5`
  for example will make the PreforkingServer implementation use 5 children
  instead of the default 10.
  (@thekid)

## 6.2.0 / 2015-06-13

* Added forward compatibility with PHP7 - @thekid
* Fixed `scriptlet.LocaleNegotiator` in PHP7 - @thekid
* Fixed HHVM compatibility issue with HTTP protocol version verification,
  broken because HHVM handles sscanf() differently regarding to `%*...`.
  (@thekid)

## 6.1.0 / 2015-06-08

* Merged pull request #3: Filters. Filters wrap around request/response
  processing and can be used for authentication, compression, caching, etc.
  (@thekid)
* Merged pull request #4: Implementation of web layouts:
  . `$ xpws -c de.thekid.dialog.WebLayout`
    Will start reading the web layout from the given layout class
  . `$ xpws -c de.thekid.dialog.scriptlet.RssScriptlet`
    Will start with a web layout with the given scriptlet at "/"
  . `$ xpws -c -`
    Will start to serve static files from document root
  . `$ xpws -c etc` (*existing behaviour*)
    Will start with a web layout read from etc/web.ini
  (@thekid)

## 6.0.3 / 2015-06-01

* Added `ToUnixLineBreaks` caster - see PR xp-framework/xp-framework#363
  (@treuter, @thekid)

## 6.0.2 / 2015-02-12

* Changed dependency to use XP ~6.0 (instead of dev-master) - @thekid

## 6.0.1 / 2015-10-01

* Fix issue #6: Cannot call constructor - (@thekid)

## 6.0.0 / 2015-10-01

* Merged pull request #1: XPWS and persistent PHP webservers - (@thekid)
* Heads up: Converted classes to PHP 5.3 namespaces - (@thekid)
