<?php

/**
 * @file
 * Contains \Drupal\publication_date\publication_date.views.inc.
 */

/**
 * Implements hook_views_data_alter().
 */
function publication_date_views_data_alter(array &$data) {
  $data['node_field_data']['published_at']['argument']['id'] = 'date';
  $data['node_field_data']['published_at']['filter']['id'] = 'date';
  $data['node_field_data']['published_at']['sort']['id'] = 'date';

  $data['node_field_data']['published_fulldate'] = [
    'title' => t('Published date'),
    'help' => t('Date in the form of CCYYMMDD.'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_fulldate',
    ],
  ];

  $data['node_field_data']['published_year_month'] = [
    'title' => t('Published year + month'),
    'help' => t('Date in the form of YYYYMM.'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_year_month',
    ],
  ];

  $data['node_field_data']['published_year'] = [
    'title' => t('Published year'),
    'help' => t('Date in the form of YYYY.'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_year',
    ],
  ];

  $data['node_field_data']['published_month'] = [
    'title' => t('Published month'),
    'help' => t('Date in the form of MM (01 - 12).'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_month',
    ],
  ];

  $data['node_field_data']['published_day'] = [
    'title' => t('Published day'),
    'help' => t('Date in the form of DD (01 - 31).'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_day',
    ],
  ];

  $data['node_field_data']['published_week'] = [
    'title' => t('Published week'),
    'help' => t('Date in the form of WW (01 - 53).'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_week',
    ],
  ];

  $data['node_field_revision']['published_at']['argument']['id'] = 'date';
  $data['node_field_revision']['published_at']['filter']['id'] = 'date';
  $data['node_field_revision']['published_at']['sort']['id'] = 'date';

  $data['node_field_revision']['published_fulldate'] = [
    'title' => t('Published date'),
    'help' => t('Date in the form of CCYYMMDD.'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_fulldate',
    ],
  ];

  $data['node_field_revision']['published_year_month'] = [
    'title' => t('Published year + month'),
    'help' => t('Date in the form of YYYYMM.'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_year_month',
    ],
  ];

  $data['node_field_revision']['published_year'] = [
    'title' => t('Published year'),
    'help' => t('Date in the form of YYYY.'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_year',
    ],
  ];

  $data['node_field_revision']['published_month'] = [
    'title' => t('Published month'),
    'help' => t('Date in the form of MM (01 - 12).'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_month',
    ],
  ];

  $data['node_field_revision']['published_day'] = [
    'title' => t('Published day'),
    'help' => t('Date in the form of DD (01 - 31).'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_day',
    ],
  ];

  $data['node_field_revision']['published_week'] = [
    'title' => t('Published week'),
    'help' => t('Date in the form of WW (01 - 53).'),
    'argument' => [
      'field' => 'published_at',
      'id' => 'date_week',
    ],
  ];
}
