<?php

namespace Drupal\minifyjs\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\minifyjs\MinifyJsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller routines for minifyjs routes.
 */
class FileManager extends ControllerBase {

  /**
   * Minify JS service.
   *
   * @var \Drupal\minifyjs\MinifyJsInterface
   */
  protected MinifyJsInterface $minifyJs;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('minifyjs')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\minifyjs\MinifyJsInterface $minify_js
   *   The minify JS service.
   */
  public function __construct(MinifyJsInterface $minify_js) {
    $this->minifyJs = $minify_js;
  }

  /**
   * Minify a single file.
   *
   * @param object $file
   *   The file to minify.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the manage javascript page.
   */
  public function minify($file) {
    $this->minifyJs->minify($file);
    return $this->redirect('minifyjs.manage');
  }

  /**
   * Remove the minified version of a single file (restore it).
   *
   * @param object $file
   *   The file to restore.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the manage javascript page.
   */
  public function restore($file) {
    $this->minifyJs->restore($file);
    return $this->redirect('minifyjs.manage');
  }

  /**
   * Scans the system for javascript.
   *
   * @return \Symfony\Component\HttpFoundation\RedirectResponse
   *   Returns a redirect to the manage javascript page.
   */
  public function scan() {
    $this->minifyJs->scan();
    return $this->redirect('minifyjs.manage');
  }

}
