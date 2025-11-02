<?php

namespace Drupal\minifyjs\Form;

use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Pager\PagerManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\Url;
use Drupal\minifyjs\MinifyJsInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Manage files form class.
 *
 * Displays a list of detected javascript files and allows actions to be
 * performed on them.
 */
class ManageFilesForm extends FormBase {

  /**
   * Module extension list service.
   *
   * @var \Drupal\Core\Extension\ModuleExtensionList
   */
  protected ModuleExtensionList $moduleExtensionList;

  /**
   * File URL generator service.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected FileUrlGeneratorInterface $fileUrlGenerator;

  /**
   * Minify JS service.
   *
   * @var \Drupal\minifyjs\MinifyJsInterface
   */
  protected MinifyJsInterface $minifyJs;

  /**
   * Pager manager service.
   *
   * @var \Drupal\Core\Pager\PagerManagerInterface
   */
  protected PagerManagerInterface $pagerManager;

  /**
   * Renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected RendererInterface $renderer;

  /**
   * Private temp store service.
   *
   * @var \Drupal\Core\TempStore\PrivateTempStoreFactory
   */
  protected PrivateTempStoreFactory $privateTempStore;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('extension.list.module'),
      $container->get('file_url_generator'),
      $container->get('minifyjs'),
      $container->get('pager.manager'),
      $container->get('renderer'),
      $container->get('tempstore.private')
    );
  }

  /**
   * Constructor.
   *
   * @param \Drupal\Core\Extension\ModuleExtensionList $module_extension_list
   *   The module extension list service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator service.
   * @param \Drupal\minifyjs\MinifyJsInterface $minify_js
   *   The minify JS service.
   * @param \Drupal\Core\Pager\PagerManagerInterface $pager_manager
   *   The pager manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\TempStore\PrivateTempStoreFactory $private_temp_store
   *   The private temp store service.
   */
  public function __construct(
    ModuleExtensionList $module_extension_list,
    FileUrlGeneratorInterface $file_url_generator,
    MinifyJsInterface $minify_js,
    PagerManagerInterface $pager_manager,
    RendererInterface $renderer,
    PrivateTempStoreFactory $private_temp_store,
  ) {
    $this->moduleExtensionList = $module_extension_list;
    $this->fileUrlGenerator = $file_url_generator;
    $this->minifyJs = $minify_js;
    $this->pagerManager = $pager_manager;
    $this->privateTempStore = $private_temp_store;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $files = $this->minifyJs->loadAllFiles();
    $form = [];

    // Statistics.
    $number_of_files = 0;
    $minified_files = 0;
    $unminified_size = 0;
    $minified_size = 0;
    $saved_size = 0;

    // Get search query.
    $session = $this->privateTempStore->get('minifyjs');
    $query = $session->get('query');

    // Filter the files based on query.
    if ($query) {
      $new_files = [];
      foreach ($files as $fid => $file) {
        if (stripos($file->uri, $query) !== FALSE) {
          $new_files[$fid] = $file;
        }
      }
      $files = $new_files;
    }

    // Pager init.
    $limit = 100;
    $start = 0;
    $page = $this->getRequest()->query->get('page');
    if (isset($page)) {
      $start = $page * $limit;
    }
    $total = count($files);
    $this->pagerManager->createPager($total, $limit);

    // Build the rows of the table.
    $rows = [];
    if ($total) {

      // Statistics for all files.
      foreach ($files as $fid => $file) {
        $number_of_files++;
        $unminified_size += $file->size;
        $minified_size += $file->minified_size;
        if ($file->minified_uri) {
          $saved_size += $file->size - $file->minified_size;
          $minified_files++;
        }
      }

      // Build table rows.
      $files_subset = array_slice($files, $start, $limit, TRUE);
      foreach ($files_subset as $fid => $file) {
        $operations = ['#type' => 'operations', '#links' => $this->operations($file)];

        $rows[$fid] = [
          Link::fromTextAndUrl($file->uri, Url::fromUri('base:' . $file->uri, ['attributes' => ['target' => '_blank']])),
          date('Y-m-d', $file->modified),
          $this->formatFilesize($file->size),
          $this->minifiedFilesize($file),
          $this->percentage($file),
          $this->minifiedDate($file),
          $this->minifiedFile($file),
          $this->renderer->render($operations),
        ];
      }
    }

    // Report on statistics.
    $this->messenger()->addMessage(
      $this->t(
        '@files javascript files (@min_files minified). The size of all original files is @size and the size of all of the minified files is @minified for a savings of @diff (@percent% smaller overall)',
        [
          '@files' => $number_of_files,
          '@min_files' => $minified_files,
          '@size' => $this->formatFilesize($unminified_size),
          '@minified' => ($minified_size) ? $this->formatFilesize($minified_size) : 0,
          '@diff' => ($minified_size) ? $this->formatFilesize($saved_size) : 0,
          '@percent' => ($minified_size) ? round($saved_size / $unminified_size * 100, 2) : 0,
        ]
      ),
      'status'
    );

    $form['search'] = [
      '#type' => 'container',
      '#attributes' => ['class' => 'container-inline'],
    ];
    $form['search']['query'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Search'),
      '#title_display' => 'hidden',
      '#default_value' => $query,
    ];
    $form['search']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Search'),
      '#submit' => [[$this, 'filterList']],
    ];
    if ($query) {
      $form['search']['reset'] = [
        '#type' => 'submit',
        '#value' => $this->t('Reset'),
        '#submit' => [[$this, 'filterListReset']],
      ];
    }

    // Bulk minify button.
    if ($total) {
      $form['actions'] = [
        '#type'  => 'container',
        '#attributes' => [
          'class' => ['container-inline'],
        ],
      ];
      $form['actions']['action'] = [
        '#type' => 'select',
        '#options' => [
          'minify' => $this->t('Minify (and re-minify)'),
          'minify_skip' => $this->t('Minify (and skip minified)'),
          'restore' => $this->t('Restore'),
        ],
      ];
      $form['actions']['scope'] = [
        '#type' => 'select',
        '#options' => [
          'selected' => $this->t('Selected files'),
          'all' => $this->t('All files'),
        ],
      ];
      $form['actions']['go'] = [
        '#type' => 'submit',
        '#value' => $this->t('Perform action'),
      ];
    }

    // The table.
    $form['files'] = [
      '#type' => 'tableselect',
      '#header' => [
        $this->t('Original File'),
        $this->t('Last Modified'),
        $this->t('Original Size'),
        $this->t('Minified Size'),
        $this->t('Savings'),
        $this->t('Last Minified'),
        $this->t('Minified File'),
        $this->t('Operations'),
      ],
      '#options' => $rows,
      '#empty' => $this->t('No files have been found. Please scan using the action link above.'),
    ];

    $form['pager'] = ['#type' => 'pager'];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'minifyjs_manage_files';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    if (count($form_state->getValue('files'))) {
      $files = $this->minifyJs->loadAllFiles();

      // Get the files to process.
      $selected_files = [];
      if ($form_state->getValue('scope') == 'selected') {
        foreach ($form_state->getValue('files') as $fid => $selected) {
          if ($selected) {
            $selected_files[] = $fid;
          }
        }
      }
      else {
        $selected_files = array_keys($files);
      }

      // Build operations.
      $operations = [];
      foreach ($selected_files as $fid) {
        switch ($form_state->getValue('action')) {

          // Minify all files.
          case 'minify':
            $operations[] = ['minifyjs_batch_minify_file_operation', [$fid]];
            break;

          // Minify files that have not yet been minified.
          case 'minify_skip':
            $file = $files[$fid];
            if (!$file->minified_uri) {
              $operations[] = ['minifyjs_batch_minify_file_operation', [$fid]];
            }
            break;

          // Restore un-minified version of a file.
          case 'restore':
            $operations[] = ['minifyjs_batch_remove_minified_file_operation', [$fid]];
            break;
        }
      }

      // Build the batch.
      $batch = [
        'operations' => $operations,
        'file' => $this->moduleExtensionList->getPath('minifyjs') . '/minifyjs.module',
        'error_message' => $this->t('There was an unexpected error while processing the batch.'),
        'finished' => 'minifyjs_batch_finished',
      ];
      switch ($form_state->getValue('action')) {
        case 'minify':
          $batch['title'] = $this->t('Minifying Javascript Files.');
          $batch['init_message'] = $this->t('Initializing minify javascript files batch.');
          break;

        case 'restore':
          $batch['title'] = $this->t('Restoring Un-Minified Javascript Files.');
          $batch['init_message'] = $this->t('Initializing restore un-minified javascript files batch.');
          break;

      }

      // Start the batch.
      batch_set($batch);
    }
  }

  /**
   * Filter list submit callback.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function filterList(array &$form, FormStateInterface $form_state) {
    $session = $this->privateTempStore->get('minifyjs');
    $session->set('query', $form_state->getValue('query'));
  }

  /**
   * Filter list reset submit callback.
   *
   * @param array $form
   *   The form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function filterListReset(array &$form, FormStateInterface $form_state) {
    $session = $this->privateTempStore->get('minifyjs');
    $session->set('query', NULL);
  }

  /**
   * Helper function to format the filesize.
   *
   * @param int $size
   *   The size in bytes.
   *
   * @return string|int
   *   The converted size string or 0.
   */
  protected function formatFilesize($size) {
    if ($size) {
      $suffixes = ['', 'k', 'M', 'G', 'T'];
      $base = log($size) / log(1024);
      $base_floor = floor($base);

      return round(pow(1024, $base - $base_floor), 2) . $suffixes[$base_floor];
    }

    return 0;
  }

  /**
   * Helper function to format date.
   *
   * @param object $file
   *   The file that has the date to be formatted.
   *
   * @return string
   *   The formatted date.
   */
  protected function minifiedDate($file) {
    if ($file->minified_modified > 0) {
      return date('Y-m-d', $file->minified_modified);
    }

    return '-';
  }

  /**
   * Helper function to format the minified filesize.
   *
   * @param object $file
   *   The file that has the filesize to format.
   *
   * @return string|int
   *   The formatted filesize or 0.
   */
  protected function minifiedFilesize($file) {
    if ($file->minified_uri) {
      if ($file->minified_size > 0) {
        return $this->formatFilesize($file->minified_size);
      }

      return 0;
    }

    return '-';
  }

  /**
   * Helper function to format the file url.
   *
   * @param object $file
   *   The file to return the URL for.
   *
   * @return string
   *   The URL.
   */
  protected function minifiedFile($file) {
    if (!empty($file->minified_uri)) {
      return Link::fromTextAndUrl(
        basename($file->minified_uri),
        Url::fromUri(
          $this->fileUrlGenerator->generateAbsoluteString($file->minified_uri),
          ['attributes' => ['target' => '_blank']]
        )
      );
    }

    return '-';
  }

  /**
   * Helper function to format the savings percentage.
   *
   * @param object $file
   *   The file to generate the percentage for.
   *
   * @return string
   *   The percentage value.
   */
  protected function percentage($file) {
    if ($file->minified_uri) {
      if ($file->minified_size > 0) {
        return round(($file->size - $file->minified_size) / $file->size * 100, 2) . '%';
      }

      return 0 . '%';
    }

    return '-';
  }

  /**
   * Helper function to return the operations available for the file.
   *
   * @param object $file
   *   The file to generate operations for.
   *
   * @return array
   *   The list of operations.
   */
  protected function operations($file) {
    $operations = [];

    if (empty($file->minified_uri)) {
      $operations['minify'] = [
        'title' => $this->t('Minify'),
        'url' => Url::fromRoute('minifyjs.minify', ['file' => $file->fid]),
      ];
    }
    else {
      $operations['reminify'] = [
        'title' => $this->t('Re-Minify'),
        'url' => Url::fromRoute('minifyjs.minify', ['file' => $file->fid]),
      ];
      $operations['restore'] = [
        'title' => $this->t('Restore'),
        'url' => Url::fromRoute('minifyjs.restore', ['file' => $file->fid]),
      ];
    }

    return $operations;
  }

}
