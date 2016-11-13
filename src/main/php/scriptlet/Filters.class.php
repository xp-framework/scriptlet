<?php namespace scriptlet;

class Filters {

  /** Creates a new instance */
  public function __construct($filters, $arg) {
    $this->filters= $filters;

    // ['/pattern' => target1, '/pattern2' => target2]
    if (is_array($arg)) {
      $this->target= function($request, $response) use($arg) {
        $path= $request->getURL()->getPath();
        foreach ($arg as $pattern => $target) {
          if (preg_match('#'.strtr($pattern, ['#' => '\#']).'#', $path)) {
            return $target->route($request, $response);
          }
        }

        $response->setStatus(400);
        $response->write('Cannot handle "'.$path.'"');
      };
    } else {
      $this->target= [$arg, 'route'];
    }
  }

  public function route($request, $response) {
    return (new Invocation($this->target, $this->filters))->proceed($request, $response);
  }
}