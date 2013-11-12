Scriptlets for the XP Framework
========================================================================

Everything that runs in the web in the XP Framework, is a scriptlet, at the 
beginning. Every higher-class API is derived from the simple `HttpScriptlet` 
class: `RestScriptlet`, `WorkflowScriptlet`, ...

The HttpScriptlet class
-----------------------
The `scriptlet.HttpScriptlet` class is the base class for any so-called 
scriptlet. A scriptlet is something that can serve HTTP requests.

The simplest form of answering an HTTP request in XP Framework goes like
this:

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

This code generates a HTML page that shows a headline "Hello World" or "Hello
$something" when something was given as GET-parameter "name".

Override `doPost()` or any of the other methods named after HTTP request types 
to serve these request types, as well.
