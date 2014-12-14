<?php namespace scriptlet;

use peer\URL;
use peer\http\HttpConstants;

/**
 * Scriptlets are the counterpart to Java's Servlets - as one might
 * have guessed from their name. Scriptlets, in comparison to Java
 * servlets, are terminated at the end of a request, their resources
 * freed and (non-persistent) connections, files etc. closed. 
 * Scriptlets are not a 1:1 implementation of Servlets though one
 * might find a lot of similarities!
 * 
 * This class is the base class for your application and really does
 * nothing except for providing you whith a simple way of creating
 * dynamic web pages. 
 *
 * For the beginning, in your class extending this one, simply override
 * the `doGet()` method and put any source there to be executed 
 * on a HTTP GET request.
 *
 * Example:
 * ```php
 * class MyScriptlet extends HttpScriptlet {
 *   public function doGet($request, $response) {
 *     $response->write('Hello World');
 *   }
 * }
 * ```
 *
 * @test   xp://scriptlet.unittest.HttpScriptletTest
 * @test   xp://scriptlet.unittest.HttpScriptletProcessTest
 */
class HttpScriptlet extends \lang\Object {
  private $filters= [];
  
  /**
   * Create a request object. Override this method to define
   * your own request object
   *
   * @return  scriptlet.HttpScriptletRequest
   */
  protected function _request() {
    return new HttpScriptletRequest();
  }
  
  /**
   * Create a session object. Override this method to define
   * your own session object
   *
   * @return  scriptlet.Session
   */
  protected function _session() {
    return new HttpSession();
  }
  
  /**
   * Create a response object. Override this method to define
   * your own response object
   *
   * @return  scriptlet.HttpScriptletResponse
   */
  protected function _response() {
    return new HttpScriptletResponse();
  }
  
  /**
   * Returns an URL object for the given URL
   *
   * @param string url The current requested URL
   * @return scriptlet.HttpScriptletURL
   */
  protected function _url($url) {
    return new HttpScriptletURL($url);
  }
  
  /**
   * Get authenticator for a certain request. Returns NULL in this default
   * implementation to indicate no authentication is required.
   *
   * @param   scriptlet.HttpScriptletRequest request 
   * @return  scriptlet.RequestAuthenticator
   */
  public function getAuthenticator($request) {
    return null;
  }

  /**
   * Adds a filter
   *
   * @param  scriptlet.Filter $filter
   * @return scriptlet.Filter The added filter
   */
  public function filter(Filter $filter) {
    $this->filters[]= $filter;
    return $filter;
  }

  /**
   * Initialize session
   *
   * @param   scriptlet.HttpScriptletRequest request
   */
  public function handleSessionInitialization($request) {
    $request->session->initialize($request->getSessionId());
  }

  /**
   * Handle the case when we find the given session invalid. By default, 
   * we create a new session and therefore gracefully handle this case.
   *
   * This function must return TRUE if the scriptlet is supposed to 
   * continue processing the request.
   *
   * @param   scriptlet.HttpScriptletRequest request 
   * @param   scriptlet.HttpScriptletResponse response 
   * @return  bool continue
   */
  public function handleInvalidSession($request, $response) {
    return $request->session->initialize(null);
  }

  /**
   * Handle the case when session initialization fails. By default, we 
   * just return an error for this, a derived class may choose to 
   * gracefully handle this case.
   *
   * This function must return TRUE if the scriptlet is supposed to 
   * continue processing the request.
   *
   * @param   scriptlet.HttpScriptletRequest request 
   * @param   scriptlet.HttpScriptletResponse response 
   * @return  bool continue
   */
  public function handleSessionInitializationError($request, $response) {
    return false;
  }
  
  /**
   * Decide whether a session is needed. Returns FALSE in this
   * implementation.
   *
   * @param   scriptlet.HttpScriptletRequest request
   * @return  bool
   */
  public function needsSession($request) {
    return false;
  }
  
  /**
   * Handles the different HTTP methods. Supports all HTTP verbs,
   * if they do have a handler method in the scriptlet class.
   *
   * HTTP GET, HEAD and POST are always available, but are
   * implemented as noop in the default implementation.
   *
   * If you want to support these methods, override this method - 
   * make sure you call `parent::handleMethod($request)`
   * so that the request object gets set up correctly before any
   * of your source is executed
   *
   * @see     rfc://2616
   * @param   scriptlet.HttpScriptletRequest request
   * @return  string class method (one of doGet, doPost, doHead)
   */
  public function handleMethod($request) {
    switch (strtoupper($request->method)) {
      case HttpConstants::POST:
      case HttpConstants::PATCH:
      case HttpConstants::PUT: {
        if (!empty($_FILES)) {
          $request->setParams(array_merge($request->getParams(), $_FILES));
        }
        
        // Break missing intentionally
      }
      case HttpConstants::DELETE:
      case HttpConstants::OPTIONS:
      case HttpConstants::TRACE:
      case HttpConstants::CONNECT: {
        break;
      }
        
      case HttpConstants::GET:
      case HttpConstants::HEAD:
        $request->setData($request->getEnvValue('QUERY_STRING'));
        break;
        
      default: {
        throw new ScriptletException(
          'Unknown HTTP method: "'.strtoupper($request->method).'"',
          HttpConstants::STATUS_METHOD_NOT_IMPLEMENTED
        );
      }
    }

    $method= 'do'.ucfirst(strtolower($request->method));
    if (!method_exists($this, $method)) return null;
    return $method;
  }
  
  /**
   * Receives an HTTP GET request from the `process()` method
   * and handles it.
   *
   * When overriding this method, request parameters are read and acted
   * upon and the response object is used to set headers and add
   * output. The request objects contains a session object if one was
   * requested via `needsSession()`. Return FALSE to indicate no
   * farther processing is needed - the response object's method 
   * `process` will not be called.
   * 
   * Example:
   * ```php
   * public function doGet($request, $response) {
   *   if (null === ($name= $request->getParam('name'))) {
   *     // Display a form where name is entered
   *     // ...
   *     return;
   *   }
   *   $response->write('Hello '.$name);
   * }
   * ```
   *
   * @return  bool processed
   * @param   scriptlet.HttpScriptletRequest request 
   * @param   scriptlet.HttpScriptletResponse response 
   * @throws  lang.XPException to indicate failure
   */
  public function doGet($request, $response) {
  }
  
  /**
   * Receives an HTTP POST request from the `process()` method
   * and handles it.
   *
   * @return  bool processed
   * @param   scriptlet.HttpScriptletRequest request 
   * @param   scriptlet.HttpScriptletResponse response 
   * @throws  lang.XPException to indicate failure
   */
  public function doPost($request, $response) {
  }
  
  /**
   * Receives an HTTP HEAD request from the `process()` method
   * and handles it.
   *
   * Remember:
   * The HEAD method is identical to GET except that the server MUST NOT
   * return a message-body in the response. The metainformation contained
   * in the HTTP headers in response to a HEAD request SHOULD be identical
   * to the information sent in response to a GET request. This method can
   * be used for obtaining metainformation about the entity implied by the
   * request without transferring the entity-body itself. This method is
   * often used for testing hypertext links for validity, accessibility,
   * and recent modification.
   *
   * @return  bool processed
   * @param   scriptlet.HttpScriptletRequest request 
   * @param   scriptlet.HttpScriptletResponse response 
   * @throws  lang.XPException to indicate failure
   */
  public function doHead($request, $response) {
  }
  
  /**
   * Creates a session. This method will only be called if 
   * `needsSession()` return TRUE and no session
   * is available or the session is unvalid.
   *
   * @return  bool processed
   * @param   scriptlet.HttpScriptletRequest request 
   * @param   scriptlet.HttpScriptletResponse response 
   * @throws  lang.XPException to indicate failure
   */
  public function doCreateSession($request, $response) {
    $redirect= $request->getURL();
    $redirect->setSessionId($request->session->getId());
    $response->sendRedirect($redirect->getURL());
    return false;
  }
  
  /**
   * Initialize the scriptlet. This method is called before any 
   * method processing is done.
   *
   * In this method, you can set up "global" requirements such as a 
   * configuration manager.
   *
   */
  public function init() { }
  
  /**
   * Finalize the scriptlet. This method is called after all response
   * headers and data has been sent and allows you to handle things such
   * as cleaning up resources or closing database connections.
   *
   */
  public function finalize() { }
  
  /**
   * Set the request from the environment.
   *
   * @deprecated  Uses raw environment
   * @param   scriptlet.HttpRequest request
   */
  protected function _setupRequest($request) {
    $request->method= $request->getEnvValue('REQUEST_METHOD');
    $request->setHeaders(getallheaders());
    $request->setParams(array_merge($_GET, $_POST));
  }    
  
  /**
   * This method is called to process any request and dispatches
   * it to on of the do* -methods of the scriptlet. It will also
   * call the `doCreateSession()` method if necessary.
   *
   * @param   scriptlet.HttpScriptletRequest request 
   * @param   scriptlet.HttpScriptletResponse response 
   * @throws  scriptlet.ScriptletException indicating fatal errors
   */
  public function service(HttpScriptletRequest $request, HttpScriptletResponse $response) {
    $host= $request->getHeader('X-Forwarded-Host', $request->getEnvValue('HTTP_HOST'));
    $request->setURL($this->_url(
      ('on' == $request->getEnvValue('HTTPS') ? 'https' : 'http').'://'.
      substr($host, 0, strcspn($host, ',')).
      $request->getEnvValue('REQUEST_URI')
    ));

    // Check if this method can be handled. In case it can't, throw a
    // ScriptletException with the HTTP status code 501 ("Method not
    // implemented"). The request object will already have all headers
    // and the request method set when this method is called.
    if (!($method= $this->handleMethod($request))) {
      throw new ScriptletException(
        'HTTP method "'.$request->method.'" not supported',
        HttpConstants::STATUS_METHOD_NOT_IMPLEMENTED
      );
    }

    // Call the request's initialization method
    $request->initialize();

    // Create response object. Answer with the same protocol version that the
    // user agent sends us with the request. The only versions we should be 
    // getting are 1.0 (some proxies or do this) or 1.1 (any current browser).
    // Answer with a "HTTP Version Not Supported" statuscode (#505) for any 
    // other protocol version.
    $response->setURI($request->getURL());
    if (2 != sscanf($proto= $request->getEnvValue('SERVER_PROTOCOL'), 'HTTP/%*[1].%[01]', $minor)) {
      throw new ScriptletException(
        'Unsupported HTTP protocol version "'.$proto.'" - expected HTTP/1.0 or HTTP/1.1', 
        HttpConstants::STATUS_HTTP_VERSION_NOT_SUPPORTED
      );
    }
    $response->version= '1.'.$minor;

    // Check if a session is present. This is either the case when a session
    // is already in the URL or if the scriptlet explicetly states it needs 
    // one (by returning TRUE from needsSession()).
    if ($this->needsSession($request) || $request->getSessionId()) {
      $request->setSession($this->_session());
      $valid= false;
      try {
        $this->handleSessionInitialization($request);
        $valid= $request->session->isValid();
      } catch (\lang\XPException $e) {
      
        // Check if session initialization errors can be handled gracefully
        // (default: no). If not, throw a HttpSessionInvalidException with
        // the HTTP status code 503 ("Service temporarily unavailable").
        if (!$this->handleSessionInitializationError($request, $response)) {
          throw new HttpSessionInvalidException(
            'Session initialization failed: '.$e->getMessage(),
            HttpConstants::STATUS_SERVICE_TEMPORARILY_UNAVAILABLE,
            $e
          );
        }
        
        // Fall through, otherwise
      }

      // Check if invalid sessions can be handled gracefully (default: no).
      // If not, throw a HttpSessionInvalidException with the HTTP status
      // code 400 ("Bad request").
      if (!$valid) {
        if (!$this->handleInvalidSession($request, $response)) {
          throw new HttpSessionInvalidException(
            'Session is invalid',
            HttpConstants::STATUS_BAD_REQUEST
          );
        }

        // Fall through, otherwise
      }
      
      // Call doCreateSession() in case the session is new
      if ($request->session->isNew()) $method= 'doCreateSession';
    }

    // If this scriptlet has an authenticator, run its authenticate()
    // method. This method may return FALSE to indicate no further
    // processing is to be done (e.g., in case it redirects to a login
    // site). Exceptions thrown are wrapped in a ScriptletException
    // with status code 403 ("Forbidden").
    if ($auth= $this->getAuthenticator($request)) {
      array_unshift($this->filters, new AuthenticationFilter($auth));
    }

    // Call method handler and, in case the method handler returns anything
    // else than FALSE, the response processor. Exceptions thrown from any of
    // the two methods will result in a ScriptletException with the HTTP
    // status code 500 ("Internal Server Error") being thrown.
    try {
      $r= (new Invocation([$this, $method], $this->filters))->proceed($request, $response);
      if (false !== $r && !is(null, $r)) {
        $response->process();
      }
    } catch (ScriptletException $e) {
      throw $e;
    } catch (\lang\XPException $e) {
      throw new ScriptletException(
        'Request processing failed ['.$method.']: '.$e->getMessage(),
        HttpConstants::STATUS_INTERNAL_SERVER_ERROR,
        $e
      );
    }
  }

  /**
   * This method is called to process any request and dispatches
   * it to on of the do* -methods of the scriptlet. It will also
   * call the `doCreateSession()` method if necessary.
   *
   * @return  scriptlet.HttpScriptletResponse the response object
   * @throws  scriptlet.ScriptletException indicating fatal errors
   */
  public function process() {
    $request= $this->_request();
    $response= $this->_response();

    // Call service()
    $this->_setupRequest($request);
    $this->service($request, $response);
    
    // Return it
    return $response;
  }
}
