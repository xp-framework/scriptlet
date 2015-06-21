Scriptlets for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

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
