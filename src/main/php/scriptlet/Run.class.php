<?php namespace scriptlet;

use web\InternalServerError;
use web\Error;
use lang\Throwable;

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

  /**
   * Handler
   *
   * @param  web.Request $request
   * @param  web.Response $response
   */
  public function handle($request, $response) {
    with ($req= $this->scriptlet->request(), $res= $this->scriptlet->response()); {
      $uri= $request->uri();
      $port= $uri->port() ?: -1;

      // Proxy request
      $req->url= new HttpScriptletURL($uri->asString(true));
      $req->method= $request->method();
      $req->env= [
        'SERVER_PROTOCOL' => 'HTTP/1.1',
        'REQUEST_URI'     => $uri->path(true),
        'QUERY_STRING'    => $uri->query(true),
        'HTTP_HOST'       => $uri->host().(-1 === $port ? '' : ':'.$port)
      ];
      $req->setParams($request->params());
      $req->setHeaders(array_merge($request->headers(), $request->values()));
      $req->readData= function() use($request) {
        return $request->stream();
      };

      // Proxy response
      $stream= null;
      $res->sendHeaders= function($version, $statusCode, $headers) use($response, &$stream) {
        $response->answer($statusCode);

        foreach ($headers as $header) {
          sscanf($header, "%[^:]: %[^\r]", $name, $value);
          if (0 === strcasecmp('Content-Length', $name)) {
            $stream= $response->stream($value);
          }
          $response->header($name, $value);
        }

        // Fall back to chunked encoding
        if (null === $stream) $stream= $response->stream(null);
      };
      $res->sendContent= function($content) use(&$stream) {
        $stream->write($content);
      };

      // Run scriptlet
      try {
        $this->scriptlet->service($req, $res);
      } catch (ScriptletException $e) {
        throw new Error($e->getStatus(), $e->getMessage(), $e);
      } catch (Throwable $t) {
        throw new InternalServerError($t);
      }

      $res->isCommitted() || $res->flush();
      $res->sendContent();
      $stream->close();
    }
  }
}