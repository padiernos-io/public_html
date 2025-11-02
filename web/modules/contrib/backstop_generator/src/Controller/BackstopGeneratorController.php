<?php

namespace Drupal\backstop_generator\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\File\FileSystemInterface;
use Drupal\node\Entity\Node;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * Returns responses for BackstopJS routes.
 */
class BackstopGeneratorController extends ControllerBase {

  /**
   * The file system service.
   *
   * @var \Drupal\Core\File\FileSystemInterface
   */
  protected $fileSystem;

  /**
   * Constructs a new BackstopGeneratorController object.
   *
   * @param \Drupal\Core\File\FileSystemInterface $file_system
   *   The file system service.
   */
  public function __construct(FileSystemInterface $file_system) {
    $this->fileSystem = $file_system;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('file_system'),
    );
  }

  /**
   * Builds the response.
   */
  public function build() {

    $build['content'] = [
      '#type' => 'item',
      '#markup' => $this->t('For each test Profile, start from your project root and run the Terminal commands under each heading.'),
    ];
    $build['info'] = [
      '#markup' => $this->detailMarkup(),
    ];

    return $build;
  }

  /**
   * Returns the profile directories within the backstop directory.
   *
   * @return array|false
   *   An array of the Backstop JSON files in the tests/backstop directory.
   */
  private function getBackstopProfiles() {
    $directory = $this->config('backstop_generator.settings')->get('backstop_directory');
    $directory = dirname(DRUPAL_ROOT) . $directory;

    $files = is_dir($directory) ? scandir($directory) : [];

    foreach ($files as $key => $file) {

      if ($this->excludeFile($file)) {
        unset($files[$key]);
      }
    }
    return $files;
  }

  /**
   * Excludes files from the list of profiles.
   *
   * @param string $filename
   *   The filename to check.
   *
   * @return bool
   *   TRUE if the file should be excluded, FALSE otherwise.
   */
  private function excludeFile($filename): bool {
    // Exclude values that begin with a dot (.)
    if (str_starts_with($filename, '.')) {
      return TRUE;
    }

    // Exclude directories (in a real-world case, use is_dir()).
    // Mock check; replace with `is_dir()` in a real environment.
    if ($filename === 'backstop_data') {
      return TRUE;
    }

    // Include only files that end with ".json".
    if (!str_ends_with($filename, '.json')) {
      return TRUE;
    }

    // Include only ".json" files containing "backstop" or "bsg_".
    if (!str_contains($filename, 'backstop') && !str_contains($filename, 'bsg_')) {
      return TRUE;
    }
    return FALSE;
  }

  /**
   * Generates the text for the commands to run backstop tests.
   *
   * @return string
   *   The markup of the commands to run backstop tests.
   */
  private function detailMarkup() {
    $profiles = $this->getBackstopProfiles();
    $directory = $this->config('backstop_generator.settings')->get('backstop_directory');

    $markup = "<div class=\"test-code-paths\">";
    foreach ($profiles as $profile) {
      preg_match('/(\w+)\.json/', $profile, $config_name);
      $config_flag = " --config=$config_name[1]";

      // Accommodate for the default test profile.
      if ($profile == 'backstop.json') {
        $profile = 'Default';
        $config_flag = '';
      }

      $markup .= "<h2>Run the \"$profile\" test</h2>";
      $markup .= "<pre>";
      $markup .= "cd $directory\n";
      $markup .= "backstop reference $config_flag\n";
      $markup .= "backstop test $config_flag";
      $markup .= "</pre>";
    }
    $markup .= "</div>";

    return $markup;
  }

  /**
   * Provides the autocomplete results when creating backstop scenarios.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function scenarioAutocomplete(Request $request) {
    $results = [];

    $keyword = Xss::filter($request->query->get('q'));
    if (empty($keyword)) {
      return new JsonResponse($results);
    }

    $query = $this->entityTypeManager()
      ->getStorage('node')
      ->getQuery()
      ->condition('title', $keyword, 'CONTAINS')
      ->sort('title', 'ASC')
      ->range(0, 10)
      ->accessCheck(FALSE);

    $ids = $query->execute();
    $items = Node::loadMultiple($ids);

    foreach ($items as $item) {
      $label = [];
      $label[] = $item->getTitle();
      $results[] = [
        'value' => EntityAutocomplete::getEntityLabels([$item]),
        'label' => implode(', ', $label),
      ];
    }
    return new JsonResponse($results);
  }

}
