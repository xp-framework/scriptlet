Scriptlets for the XP Framework
========================================================================

[![Build Status on TravisCI](https://secure.travis-ci.org/xp-framework/scriptlet.svg)](http://travis-ci.org/xp-framework/scriptlet)
[![XP Framework Module](https://raw.githubusercontent.com/xp-framework/web/master/static/xp-framework-badge.png)](https://github.com/xp-framework/core)
[![BSD Licence](https://raw.githubusercontent.com/xp-framework/web/master/static/licence-bsd.png)](https://github.com/xp-framework/core/blob/master/LICENCE.md)
[![Required PHP 5.4+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-5_4plus.png)](http://php.net/)
[![Supports PHP 7.0+](https://raw.githubusercontent.com/xp-framework/web/master/static/php-7_0plus.png)](http://php.net/)
[![Latest Stable Version](https://poser.pugx.org/xp-framework/scriptlet/version.png)](https://packagist.org/packages/xp-framework/scriptlet)

Everything that runs in the web in the XP Framework, is a scriptlet, at the beginning. Every higher-class API is derived from the simple `HttpScriptlet` class: `RestScriptlet`, `WorkflowScriptlet`, ...

The HttpScriptlet class
-----------------------
The `scriptlet.HttpScriptlet` class is the base class for any so-called scriptlet. A scriptlet is something that can serve HTTP requests.

The simplest form of answering an HTTP request in XP Framework goes like this:

```php
namespace com\example\web;

class HelloScriptlet extends \scriptlet\HttpScriptlet {

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

Running it
----------
Use the `xpws` runner to serve your scriptlet:

```sh
$ xpws -c com.example.web.HelloScriptlet
[xpws-dev#7312] running localhost:8080 @ /path/to/web/project - Press <Enter> to exit
```

Now open http://localhost:8080/ in your browser.
