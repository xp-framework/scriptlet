<?php namespace scriptlet;

class Handler implements Routing {

  /** Creates a new instance */
  public function __construct($actions, $templates) {
    $this->actions= $actions;
    $this->templates= $templates;
  }

  public function route($request, $response) {
    $name= 'index';
    sscanf(rtrim($request->getURL()->getPath(), '/'), "/%[^\r]", $name);

    try {
      $structure= $this->actions->named($name)->handle($request, $response);
      if (null !== $structure) {
        $response->write($this->templates->render($name, array_merge($structure, [
          'request' => [
            'headers' => $request->getHeaders(),
            'params'  => $request->getParams(),
            'url'     => $request->getURL()
          ]
        ])));
      }
    } catch (ScriptletException $e) {
      throw $e;
    } catch (\Throwable $t) {   // PHP 7
      throw new HttpScriptletException('Cannot handle '.$name, 500, $t);
    } catch (\Exception $e) {   // PHP 5
      throw new HttpScriptletException('Cannot handle '.$name, 500, $e);
    }
  }
}