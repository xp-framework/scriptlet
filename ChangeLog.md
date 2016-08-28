Scriptlets for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

## 8.3.0 / 2016-08-28

* Added forward compatibility with XP 8.0.0: Refrain from using deprecated
  `util.Properties::fromString()`
  (@thekid)

## 8.2.5 / 2016-08-17

* Merged PR #15: Remove duplicate '/etc' in case of apache request. Fixes
  a bug causes by the refactoring done in 8.2.4.
  (@beorgler, @kiesel, @thekid)

## 8.2.4 / 2016-08-14

* Merged PR #14: Fix classpath passed to development webserver 
  (@thekid)

## 8.2.3 / 2016-08-14

* Fixed code in XP webserver to merge POST and GET parameters in the same
  way that PHP's SAPIs do.
  (@thekid)

## 8.2.2 / 2016-07-23

* Add compatibility with `xp-framework/rdbms` 8.0-SERIES - @thekid

## 8.2.1 / 2016-07-23

* Also allow explicitely passing `-s [source]` instead of having to pass
  it as last parameter. Useful e.g. in Dockerfiles.
  (@thekid)
* Allow omitting the port in `-a`; defaulting it to **8080** - @thekid.

## 8.2.0 / 2016-07-11

* Merged PR #10: Development webserver - @thekid

## 8.1.2 / 2016-07-04

* Merged PR #12: Remove obsolete class text/parser/DateParser - @guel1973

## 8.1.1 / 2016-07-04

* Restored PHP 5.4 runtime compatibility - @thekid

## 8.1.0 / 2016-07-04

* Merged PR #11: Web config - @thekid

## 8.0.2 / 2016-07-03

* Fixed issue #9: Undefined offset error in AbstractState
  (@thekid)

## 8.0.1 / 2016-05-25

* Deprecated `Request::getInputStream()` in favor of new `in()` method.
  The interface hasn't changed though, retaining BC until the next major
  release!
  (@thekid)
* Deprecated `Response::getOutputStream()` in favor of new `out()` method.
  The interface hasn't changed though, retaining BC until the next major
  release!
  (@thekid)
* Changed ScriptletOutputStream's close method to only flush response
  if not previously done. Calling `close()` multiple times shouldn't be a
  problem and streams typically don't mind.
  (@thekid)

## 8.0.0 / 2016-02-21

* **Adopted semantic versioning. See xp-framework/rfc#300** - @thekid 
* Added version compatibility with XP 7 - @thekid

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
