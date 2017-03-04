<?php namespace scriptlet;

/**
 * Adapter class for new web API
 *
 * @see  https://github.com/xp-forge/web
 */
class Run implements \web\Handler {

  /**
   * Creates a new scriptlet runner
   *
   * @param  scriptlet.HttpScriptlet $scriptlet
   */
  public function __construct(HttpScriptlet $scriptlet) {
    $this->scriptlet= $scriptlet;
    $this->scriptlet->init();
  }

  public function handle($request, $response) {
    with ($req= $this->scriptlet->request(), $res= $this->scriptlet->response()); {
      $uri= $request->uri();
      $port= $uri->getPort(-1);

      // Proxy request
      $req->url= new HttpScriptletURL($uri->getURL());
      $req->method= $request->method();
      $req->env= [
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'REQUEST_URI'     => $uri->getPath(),
        'QUERY_STRING'    => $uri->getQuery(),
        'HTTP_HOST'       => $uri->getHost().(-1 === $port ? '' : ':'.$port)
      ];
      $req->setParams($request->params());
      $req->setHeaders(array_merge($request->headers(), $request->values()));
      $req->readData= function() use($request) {
        // TBI
      };

      // Proxy response
      $res->sendHeaders= function($version, $statusCode, $headers) use($response) {
        $response->answer($statusCode);
        foreach ($headers as $header) {
          sscanf($header, "%[^:]: %[^\r]", $name, $value);
          $response->header($name, $value);
        }
      };
      $res->sendContent= function($content) use($response) {
        $response->write($content);
      };

      // Run scriptlet
      try {
        $this->scriptlet->service($req, $res);
      } catch (ScriptletException $e) {
        throw new InternalServerError($e);
      }

      $res->isCommitted() || $res->flush();
      $res->sendContent();
    }
  }
}