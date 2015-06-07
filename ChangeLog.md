Scriptlets for the XP Framework ChangeLog
========================================================================

## ?.?.? / ????-??-??

* Merged pull request #4: Implementation of web layouts:
  . `$ xpws -c de.thekid.dialog.WebLayout`
    Will start the XP web server reading the web layout from the given layout class
  . `$ xpws -c de.thekid.dialog.scriptlet.RssScriptlet`
    Will start the XP web server with a web layout with the given scriptlet at "/"
  . `$ xpws -c -`
    Will start the XP web server to serve static files from document root
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
