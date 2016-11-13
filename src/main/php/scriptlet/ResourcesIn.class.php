<?php namespace scriptlet;

use io\File;
use io\Folder;
use io\IOException;
use util\MimeType;

class ResourcesIn {

  /** Creates a new instance */
  public function __construct($path) {
    $this->path= new Folder($path);
  }

  public function route($request, $response) {
    $file= new File($this->path, preg_replace(
      ['#\.\./?#', '#/#'],
      [DIRECTORY_SEPARATOR, DIRECTORY_SEPARATOR],
      $request->getURL()->getPath()
    ));

    if (!is_file($file->getURI())) {
      $response->setStatus(404);
      $response->write('File '.$request->getURL()->getPath().' not found');
      return;
    }

    // Implement If-Modified-Since/304 Not modified
    $lastModified= $file->lastModified();
    if ($ifModifiedSince= $request->getHeader('If-Modified-Since')) {
      if ($lastModified <= strtotime($ifModifiedSince)) {
        $response->setStatus(304);
        return '-';
      }
    }

    clearstatcache();
    try {
      $file->open(File::READ);
    } catch (IOException $e) {
      $response->setStatus(403);
      $response->write('Cannot access file '.$request->getURL()->getPath());
      $file->close();
      return $e->getMessage();
    }

    // Send OK header and data in 8192 byte chunks
    $response->setStatus(200);
    $response->setHeader('Last-Modified', gmdate('D, d M Y H:i:s T', $lastModified));
    $response->setHeader('Content-Type', MimeType::getByFileName($file->getFilename()));
    $response->setHeader('Content-Length', $file->size());

    $response->flush();
    while (!$file->eof()) {
      $response->write($file->read(8192));
    }
    $file->close();
    return $file->size();
  }
}