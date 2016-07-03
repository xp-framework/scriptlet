<?php namespace xp\scriptlet;

use util\Properties;
use util\PropertyManager;
use util\RegisteredPropertySource;
use util\log\Logger;
use util\log\context\EnvironmentAware;
use rdbms\ConnectionManager;
use scriptlet\HttpScriptlet;
use scriptlet\HttpScriptletRequest;
use scriptlet\HttpScriptletResponse;
use peer\http\HttpConstants;
use lang\IllegalStateException;
new import('lang.ResourceProvider');

/**
 * Scriptlet runner
 *
 * @test   xp://scriptlet.unittest.RunnerTest
 */
class Runner extends \lang\Object {
  protected
    $webroot    = null,
    $profile    = null,
    $mappings   = null;

  static function __static() {
    if (!function_exists('getallheaders')) {
      eval('function getallheaders() {
        $headers= [];
        foreach ($_SERVER as $name => $value) {
          if (0 !== strncmp("HTTP_", $name, 5)) continue;
          $headers[strtr(ucwords(strtolower(strtr(substr($name, 5), "_", " "))), " ", "-")]= $value;
        }
        return $headers;
      }');
    }
  }
  
  /**
   * Creates a new scriptlet runner
   *
   * @param  string $webroot
   * @param  string $profile
   */
  public function __construct($webroot, $profile= null) {
    $this->webroot= $webroot;
    $this->profile= $profile;
  }
  
  /**
   * Configure this runner with a web.ini
   *
   * @param  util.Properties $conf
   * @throws lang.IllegalStateException if the web is misconfigured
   */
  public function configure(Properties $conf) {
    $this->layout(new WebConfiguration($conf, new Config([], [$this, 'expand'])));
  }

  /**
   * Sets layout to use
   *
   * @param  xp.scriptlet.WebLayout $layout
   * @return self this
   */
  public function layout(WebLayout $layout) {
    $this->mappings= $layout->mappedApplications($this->profile);
    return $this;
  }

  /**
   * Entry point method. Receives the following arguments from web.php:
   * 
   * 0. The web root - a directory
   * 1. The application source - either a directory or a layout or scriptlet class name
   * 2. The server profile - any name, really, defaulting to "dev"
   * 3. The script URL - the resolved path, including leading "/"
   *
   * @param  string[] $args
   * @return void
   */
  public static function main(array $args) {
    $self= new self($args[0], $args[2]);
    $config= explode(PATH_SEPARATOR, $args[1]);
    $source= array_shift($config);
    $self->layout((new Source($source, new Config($config, [$self, 'expand'])))->layout())->run($args[3]);
  }
  
  /**
   * Find which application the given url maps to
   *
   * @param  string $url
   * @return xp.scriptlet.WebApplication
   * @throws lang.IllegalArgumentException if no app can be found
   */
  public function applicationAt($url) {
    $url= '/'.ltrim($url, '/');
    foreach ($this->mappings as $pattern => $application) {
      if ('/' !== $pattern && !preg_match('#^('.preg_quote($pattern, '#').')($|/.+)#', $url)) continue;
      return $application;
    }

    throw new \lang\IllegalArgumentException('Could not find app responsible for request to '.$url);
  }

  /**
   * Return mappings
   *
   * @return [string:xp.scriptlet.WebApplication]
   */
  public function mappedApplications() {
    return $this->mappings;
  }

  /**
   * Adds an application
   *
   * @param  string $url
   * @param  xp.scriptlet.WebApplication $application
   * @return xp.scriptlet.WebApplication the added application
   */
  public function mapApplication($url, WebApplication $application) {
    $this->mappings[$url]= $application;
    return $application;
  }

  /**
   * Expand variables in string. Handles the following placeholders:
   * <ul>
   *   <li>WEBROOT</li>
   *   <li>PROFILE</li>
   * </ul>
   *
   * @param  string $value
   * @return string
   */
  public function expand($value) {
    return is_string($value) ? strtr($value, [
      '{WEBROOT}' => $this->webroot,
      '{PROFILE}' => $this->profile
    ]) : $value;
  }
  
  /**
   * Creates the scriptlet instance for the given URL and runs it
   *
   * @param  string $url default '/'
   */
  public function run($url= '/') {
  
    // Determine which scriptlet should be run
    $application= $this->applicationAt($url);

    // Determine debug level
    $flags= $application->debug();
    
    // Initializer logger, properties and connections to property base, 
    // defaulting to the same directory the web.ini resides in
    $pm= PropertyManager::getInstance();
    foreach ($application->config()->sources() as $source) {
      $pm->appendSource($source);
    }
    
    $l= Logger::getInstance();
    $pm->hasProperties('log') && $l->configure($pm->getProperties('log'));

    $cm= ConnectionManager::getInstance();
    $pm->hasProperties('database') && $cm->configure($pm->getProperties('database'));

    // Setup logger context for all registered log categories
    foreach (Logger::getInstance()->getCategories() as $category) {
      if (null === ($context= $category->getContext()) || !($context instanceof EnvironmentAware)) continue;
      $context->setHostname($_SERVER['SERVER_NAME']);
      $context->setRunner(nameof($this));
      $context->setInstance($application->getScriptlet());
      $context->setResource($url);
      $context->setParams($_SERVER['QUERY_STRING']);
    }

    // Set environment variables
    foreach ($application->environment() as $key => $value) {
      $_SERVER[$key]= $this->expand($value);
    }

    // Instantiate and initialize
    $cat= $l->getCategory('scriptlet');
    $instance= null;
    $e= null;
    try {
      if (!($class= $application->scriptlet())) {
        throw new IllegalStateException('No scriptlet in '.$application->toString());
      }
      if (!$class->hasConstructor()) {
        $instance= $class->newInstance();
      } else {
        $args= [];
        foreach ($application->arguments() as $arg) {
          $args[]= $this->expand($arg);
        }
        $instance= $class->getConstructor()->newInstance($args);
      }
      
      if ($flags & WebDebug::TRACE && $instance instanceof \util\log\Traceable) {
        $instance->setTrace($cat);
      }

      foreach ($application->filters() as $filter) {
        $instance->filter($filter);
      }

      $instance->init();

      // Set up request and response
      $request= $instance->request();
      $request->method= $_SERVER['REQUEST_METHOD'];
      $request->env= $_ENV;
      $request->setHeaders(getallheaders());
      $request->setParams($_REQUEST);
      $response= $instance->response();

      // Service
      $instance->service($request, $response);
    } catch (\scriptlet\ScriptletException $e) {
      $cat->error($e);

      // TODO: Instead of checking for a certain method, this should
      // check if the scriptlet class implements a certain interface
      if (method_exists($instance, 'fail')) {
        $response= $instance->fail($e);
      } else {
        $this->error($response, $e, $e->getStatus(), $flags & WebDebug::STACKTRACE);
      }
    } catch (\lang\SystemExit $e) {
      if (0 === $e->getCode()) {
        $response->setStatus(HttpConstants::STATUS_OK);
        if ($message= $e->getMessage()) {
          $response->setProcessed(false);
          $response->setContent($message);
        }
      } else {
        $cat->error($e);
        $this->error($response, $e, HttpConstants::STATUS_INTERNAL_SERVER_ERROR, false);
      }
    } catch (\lang\Throwable $e) {
      $cat->error($e);

      // Here, we might not have a scriptlet instance; and thus not a response
      if (!isset($response)) {
        $response= isset($instance) ? $instance->response() : new HttpScriptletResponse();
      }

      $this->error($response, $e, HttpConstants::STATUS_PRECONDITION_FAILED, $flags & WebDebug::STACKTRACE);
    }

    // Send output
    $response->isCommitted() || $response->flush();
    $response->sendContent();

    // Call scriptlet's finalizer
    $instance && $instance->finalize();

    // Debugging
    if (($flags & WebDebug::XML) && isset($response->document)) {
      flush();
      echo '<xmp>', $response->document->getDeclaration()."\n".$response->document->getSource(0), '</xmp>';
    }
    
    if (($flags & WebDebug::ERRORS)) {
      flush();
      echo '<xmp>', $e ? $e->toString() : '', \xp::stringOf(\xp::$errors), '</xmp>';
    }
  }

  /**
   * Handle exception from scriptlet
   *
   * @param   scriptlet.Response $response
   * @param   lang.Throwable $t
   * @param   int $status
   * @param   bool $trace whether to show stacktrace
   * @return  scriptlet.HttpScriptletResponse
   */
  protected function error($response, \lang\Throwable $t, $status, $trace) {
    $package= $this->getClass()->getPackage();
    $errorPage= $package->getResource($package->providesResource('error'.$status.'.html')
      ? 'error'.$status.'.html'
      : 'error500.html'
    );

    $response->setProcessed(false);
    $response->setStatus($status);
    $response->setContent(str_replace(
      '<xp:value-of select="reason"/>',
      $trace ? $t->toString() : $t->getMessage(),
      $errorPage
    ));
  }
}
