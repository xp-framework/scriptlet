<?php namespace xp\scriptlet;

use io\IOException;
use util\cmd\Console;
use peer\Socket;
use lang\Throwable;
use lang\FormatException;
use scriptlet\ScriptletException;

/**
 * HTTP protocol implementation
 */
class HttpProtocol implements \peer\server\ServerProtocol {
  private $routing, $logging;
  public $server= null;

  /**
   * Creates a new instance with a given logging
   *
   * @param  var $logging
   */
  public function __construct($logging) {
    $this->routing= newinstance(\lang\Object::class, [], [
      'targets' => [],
      'route' => function($request, $response) {
        $path= $request->getURL()->getPath();
        foreach ($this->targets as $pattern => $target) {
          if (preg_match($pattern, $path)) {
            try {
              return $target->route($request, $response);
            } catch (ScriptletException $e) {
              $response->setStatus($e->getStatus());
              $response->write($e->getMessage());
              return $e->toString();
            } catch (\Throwable $t) {
              $response->setStatus(500);
              $response->write('Internal server error');
              return Throwable::wrap($t)->toString();
            }
          }
        }

        $response->setStatus(400);
        $response->write('Cannot handle request');
        return 'No route matches "'.$path.'"';
      },
      'handle' => function($socket) use($logging) {
        try {
          $request= new HttpRequest($socket);
          $response= new HttpResponse($socket, $request->env['SERVER_PROTOCOL']);
        } catch (FormatException $ignored) {
          // Ignore malformed HTTP requests
          return;
        }

        $message= $this->route($request, $response);
        $logging($request, $response, $message);

        $response->isCommitted() || $response->flush();
        $response->sendContent();
      }
    ]);
  }

  /**
   * Initialize Protocol
   *
   * @return  bool
   */
  public function initialize() {
    return true;
  }

  /**
   * Handle client connect
   *
   * @param   peer.Socket socket
   */
  public function handleConnect($socket) {
    // Intentionally empty
  }

  /**
   * Handle client disconnect
   *
   * @param   peer.Socket socket
   */
  public function handleDisconnect($socket) {
    $socket->close();
  }

  /**
   * Supply a URL handler for a given regex
   *
   * @param   string $pattern
   * @param   var $target
   */
  public function install($pattern, $target) {
    $this->routing->targets['#'.strtr($pattern, ['#' => '\#']).'#']= $target;
  }

  /**
   * Handle client data
   *
   * @param   peer.Socket socket
   * @return  mixed
   */
  public function handleData($socket) {
    gc_enable();
    try {
      $this->routing->handle($socket);
    } finally {
      gc_collect_cycles();
      gc_disable();
      \xp::gc();
      $socket->close();
    }
  }

  /**
   * Handle I/O error
   *
   * @param   peer.Socket socket
   * @param   lang.XPException e
   */
  public function handleError($socket, $e) {
    // Console::$err->writeLine('* ', $socket->host, '~', $e);
    $socket->close();
  }
}
