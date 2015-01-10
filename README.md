Scriptlets for the XP Framework
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/scriptlet.svg)](http://travis-ci.org/xp-framework/scriptlet)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/scriptlet/version.png)](https://packagist.org/packages/xp-framework/scriptlet)

Everything that runs in the web in the XP Framework, is a scriptlet, at the beginning. Every higher-class API is derived from the simple `HttpScriptlet` class: `RestScriptlet`, `WorkflowScriptlet`, ...

The HttpScriptlet class
-----------------------
The `scriptlet.HttpScriptlet` class is the base class for any so-called scriptlet. A scriptlet is something that can serve HTTP requests.

The simplest form of answering an HTTP request in XP Framework goes like this:

```php
class MyScriptlet extends \scriptlet\HttpScriptlet {

  /**
   * Perform GET request
   *
   * @param   scriptlet.Request $request
   * @param   scriptlet.Response $response
   * @throws  scriptlet.ScriptletException
   */
  public function doGet($request, $response) {
    $response->write(sprintf('<!DOCTYPE html>
      <html>
        <head><title>Hello World scriptlet</title></head>
        <body>
          <h1>Hello %s</h1>
        </body>
      </html>',
      $request->getParam('name', 'World')
    ));
  }
}
```

This code generates a HTML page that shows a headline "Hello World" or "Hello $something" when something was given as GET-parameter "name".

Override `doPost()` or any of the other methods named after HTTP request types to serve these request types, as well.
