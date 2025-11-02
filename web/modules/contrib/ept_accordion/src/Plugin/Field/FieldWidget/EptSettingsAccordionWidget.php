<?php

namespace Drupal\ept_accordion\Plugin\Field\FieldWidget;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\ept_core\Plugin\Field\FieldWidget\EptSettingsDefaultWidget;

/**
 * Plugin implementation of the 'ept_settings_accordion' widget.
 *
 * @FieldWidget(
 *   id = "ept_settings_accordion",
 *   label = @Translation("EPT Accordion settings"),
 *   field_types = {
 *     "ept_settings"
 *   }
 * )
 */
class EptSettingsAccordionWidget extends EptSettingsDefaultWidget {

  /**
   * {@inheritdoc}
   */
  public function formElement(FieldItemListInterface $items, $delta, array $element, array &$form, FormStateInterface $form_state) {
    $element = parent::formElement($items, $delta, $element, $form, $form_state);

    $element['ept_settings']['pass_options_to_javascript'] = [
      '#type' => 'hidden',
      '#value' => TRUE,
    ];

    $element['ept_settings']['styles'] = [
      '#title' => $this->t('Styles'),
      '#type' => 'radios',
      '#options' => [
        'default' => $this->t('Default'),
        'text_only' => $this->t('Text only'),
        'plus_minus_left' => $this->t('Plus/Minus icons on the left'),
        'plus_minus_right' => $this->t('Plus/Minus icons on the right'),
      ],
      '#default_value' => $items[$delta]->ept_settings['styles'] ?? 'default',
    ];

    // Get the path of image helper.
    $imageHelpPath = '/' . \Drupal::service('extension.path.resolver')->getPath('module', 'ept_accordion') . '/images/help/';

    // Define the field help text.
    $description = $this->t('Select predefined styles for accordion. You can see some examples below:');
    $description .= '<ul>';
    $description .= '<li>' . $this->t('<a href=":default_example">Default</a>', [':default_example' => $imageHelpPath . '/default.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":text_only_example">Text only</a>', [':text_only_example' => $imageHelpPath . '/text-only.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":plus_minus_icons_on_the_left_example">Plus/Minus icons on the left</a>', [':plus_minus_icons_on_the_left_example' => $imageHelpPath . '/plus-minus-icons-on-the-left.png']) . '</li>';
    $description .= '<li>' . $this->t('<a href=":plus_minus_icons_on_the_right_example">Plus/Minus icons on the right</a>', [':plus_minus_icons_on_the_right_example' => $imageHelpPath . '/plus-minus-icons-on-the-right.png']) . '</li>';
    $description .= '<ul>';

    // Set the help text in the field description.
    $element['ept_settings']['styles']['#description'] = $description;

    $element['ept_settings']['collapsible'] = [
      '#title' => $this->t('Collapsible'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['collapsible'] ?? 1,
      '#description' => $this->t('Whether all the sections can be closed at once. Allows collapsing the active section.'),
      '#attributes' => [
        'class' => ['ept-collapsible-field'],
      ],
    ];

    $element['ept_settings']['closed'] = [
      '#title' => $this->t('All closed'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['closed'] ?? NULL,
      '#description' => $this->t('This requires the collapsible option to be checked'),
      '#attributes' => [
        'class' => ['ept-closed-field'],
      ],
      // Set the field as "read-only" when the "collapsible" is unchecked.
      '#states' => [
        'disabled' => [
          ':input.ept-collapsible-field' => ['checked' => FALSE],
        ],
      ],
    ];

    $element['ept_settings']['opened'] = [
      '#title' => $this->t('All opened'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['opened'] ?? NULL,
      // Set the field as "read-only" when the "All closed" is checked.
      '#attributes' => [
        'class' => ['ept-opened-field'],
      ],
      '#states' => [
        'unchecked' => [
          ':input.ept-closed-field' => ['checked' => TRUE],
        ],
        'disabled' => [
          ':input.ept-closed-field' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['ept_settings']['closed_in_tablet'] = [
      '#title' => $this->t('All closed in Tablet'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['closed_in_tablet'] ?? NULL,
      '#description' => $this->t('You can set own breakpoint on <a target="_blank" href="@ept_settings">EPT Settings page</a>', [
        '@ept_settings' => Url::fromRoute('ept_core.settings')->toString(),
      ]),
      // Set the field as visible when the "All opened" is checked.
      '#states' => [
        'visible' => [
          ':input.ept-opened-field' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['ept_settings']['closed_in_mobile'] = [
      '#title' => $this->t('All closed in Mobile'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['closed_in_tablet'] ?? NULL,
      '#description' => $this->t('You can set own breakpoint on <a target="_blank" href="@ept_settings">EPT Settings page</a>', [
        '@ept_settings' => Url::fromRoute('ept_core.settings')->toString(),
      ]),
      // Set the field as visible when the "All opened" is checked.
      '#states' => [
        'visible' => [
          ':input.ept-opened-field' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['ept_settings']['active'] = [
      '#title' => $this->t('Active section'),
      '#type' => 'number',
      '#default_value' => $items[$delta]->ept_settings['active'] ?? NULL,
      '#description' => $this->t('The zero-based index of the panel that is active (open). A negative value selects panels going backward from the last panel.'),
      // Set the field "active" as "read-only" when the "closed" is checked.
      '#states' => [
        'disabled' => [
          ':input.ept-closed-field' => ['checked' => TRUE],
        ],
      ],
    ];

    $element['ept_settings']['disable'] = [
      '#title' => $this->t('Disable'),
      '#type' => 'checkbox',
      '#default_value' => $items[$delta]->ept_settings['disable'] ?? NULL,
      '#description' => $this->t('Disables the accordion.'),
    ];

    $element['ept_settings']['heightStyle'] = [
      '#title' => $this->t('Height style'),
      '#type' => 'radios',
      '#options' => [
        'auto' => $this->t('Auto'),
        'fill' => $this->t('Fill'),
        'content' => $this->t('Content'),
      ],
      '#default_value' => $items[$delta]->ept_settings['heightStyle'] ?? 'content',
      '#description' => $this->t('Controls the height of the accordion and each panel.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function massageFormValues(array $values, array $form, FormStateInterface $form_state) {
    foreach ($values as &$value) {
      $value += ['ept_settings' => []];
    }
    return $values;
  }

}
