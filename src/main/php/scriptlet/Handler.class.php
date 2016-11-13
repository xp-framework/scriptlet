<?php namespace scriptlet;

class Handler implements Routing {

  /** Creates a new instance */
  public function __construct($actions, $templates) {
    $this->actions= $actions;
    $this->templates= $templates;
  }

  public function route($request, $response) {

    // / => action=Index, argument=null
    // /home => action=Home, argument=null
    // /home/blog => action=Home, argument=blog
    // /by/date/2007 => action=ByDate, argument=2007
    $path= rtrim($request->getURL()->getPath(), '/');
    $p= strrpos($path, '/');
    if (0 === $p) {
      $action= ucfirst(substr($path, 1)) ?: 'Index';
      $argument= null;
    } else {
      $action= implode('', array_map('ucfirst', explode('/', substr($path, 0, $p))));
      $argument= substr($path, $p + 1);
    }

    try {
      $structure= $this->actions->named($action)->handle($request, $response, $argument);
      if (null !== $structure) {
        $response->write($this->templates->render($action, array_merge($structure, [
          'request' => [
            'action'  => ['name' => $action, 'argument' => $argument],
            'headers' => $request->getHeaders(),
            'params'  => $request->getParams(),
            'url'     => $request->getURL()
          ]
        ])));
      }
    } catch (ScriptletException $e) {
      throw $e;
    } catch (\Throwable $t) {   // PHP 7
      throw new HttpScriptletException('Cannot handle '.$action, 500, $t);
    } catch (\Exception $e) {   // PHP 5
      throw new HttpScriptletException('Cannot handle '.$action, 500, $e);
    }
  }
}