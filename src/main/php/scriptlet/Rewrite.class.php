<?php namespace scriptlet;

class Rewrite implements Filter {

  /** Creates a new instance */
  public function __construct($replace, $target= null) {
    $this->search= $this->replace= [];
    foreach ($replace as $pattern => $replacement) {
      $this->search[]= '#'.strtr($pattern, ['#' => '\#']).'#';
      $this->replace[]= $replacement;
    }
    $this->target= $target;
  }

  public function filter($request, $response, $invocation) {
    $url= $request->getURL();
    $url->setPath(preg_replace($this->search, $this->replace, $url->getPath()));

    // Also update request environment
    $query= $url->getQuery();
    $request->env['REQUEST_URI']= $url->getPath().('' === $query ? '' : '?'.$query);

    return $invocation->proceed($request, $response);
  }
}