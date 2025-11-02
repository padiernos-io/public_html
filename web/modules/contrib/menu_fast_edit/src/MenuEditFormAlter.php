<?php

namespace Drupal\menu_fast_edit;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RedirectDestinationInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\link\LinkItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides alterations to the menu edit form.
 */
class MenuEditFormAlter implements ContainerInjectionInterface {

  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The entity repository.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * The redirect destination service.
   *
   * @var \Drupal\Core\Routing\RedirectDestinationInterface
   */
  protected $redirectDestination;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * The current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   *   The current user.
   */
  protected $currentUser;

  /**
   * Constructs a MenuLinkEditForm object.
   *
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   * @param \Drupal\Core\Routing\RedirectDestinationInterface $redirect_destination
   *   The redirect destination service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   The current user.
   */
  public function __construct(EntityRepositoryInterface $entity_repository, RedirectDestinationInterface $redirect_destination, EntityTypeManagerInterface $entity_type_manager, MenuLinkManagerInterface $menu_link_manager, AccountProxyInterface $current_user) {
    $this->entityRepository = $entity_repository;
    $this->redirectDestination = $redirect_destination;
    $this->entityTypeManager = $entity_type_manager;
    $this->menuLinkManager = $menu_link_manager;
    $this->currentUser = $current_user;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('redirect.destination'),
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.menu.link'),
      $container->get('current_user'),
    );
  }

  /**
   * Alter the menu edit form.
   *
   * @param array $form
   *   The form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param string $form_id
   *   The form ID.
   */
  public function alterForm(array &$form, FormStateInterface $form_state, $form_id) {
    // Build the query, starting with the current destination.
    $query = $this->redirectDestination->getAsArray();

    // Add attributes.
    $form['#attributes']['class'][] = 'menu-fast-edit';

    // All changes are within the "links" of the parent.
    $form_links = &$form['links'];

    // Restructure the links. Note if at least one was editable.
    $has_editable = FALSE;
    $operations_offset = NULL;
    $links = Element::children($form_links['links']);
    foreach ($links as $id) {

      // Extract the form element and its corresponding link plugin entity.
      /** @var \Drupal\Core\Menu\MenuLinkTreeElement $item */
      $element = &$form_links['links'][$id];
      $item = $element['#item'];
      $link = $item->link;
      $item_id = $link->getPluginId();

      // Re-order the array to place title and url fields before "operations".
      $keys = array_keys($element);
      $operations_offset = array_search('operations', $keys, TRUE);
      $start = array_slice($element, 0, $operations_offset, TRUE);
      $end = array_slice($element, $operations_offset, NULL, TRUE);

      // If a link is not deletable, then it is provided by a module and
      // can't have its title or url edited.
      // @todo Confirm if a link is deletable that means it is editable.
      if ($link->isDeletable()) {
        $has_editable = TRUE;

        // Edit title.
        $start['title_edit'] = [
          '#type' => 'textfield',
          '#title' => $this->t('Edit Title'),
          '#title_display' => 'invisible',
          '#size' => 20,
          '#default_value' => $link->getTitle(),
        ];

        // Get the raw uri from the link entity.
        // @todo Re-work how to get the raw uri from the link entity.
        $parts = explode(':', $link->getPluginId(), 2);
        if (isset($parts[1]) && $parts[1] !== '') {
          $uuid = $parts[1];
          $entity = $this->entityRepository->loadEntityByUuid('menu_link_content', $uuid);
          if (isset($link)) {
            // Check if the link entity is valid.
            if (!isset($entity) || !$entity->hasField('link')) {
              continue;
            }

            /** @var \Drupal\link\Plugin\Field\FieldType\LinkItem $uri_obj */
            $uri_obj = $entity->link;
            $uri_value = $uri_obj->getEntity()->get('link')->getValue();
            $uri = $uri_value[0]['uri'];

            // Add the "Edit URL" widget.
            // @todo Convert to be a proper Link field widget, if possible.
            /** @var \Drupal\link\LinkItemInterface $item */
            $start['url'] = [
              '#type' => 'entity_autocomplete',
              '#url' => $link->getUrlObject(),
              '#title' => $this->t('Edit URL'),
              '#title_display' => 'invisible',
              '#link_type' => LinkItemInterface::LINK_GENERIC,
              '#target_type' => 'node',
              '#process_default_value' => FALSE,
              '#size' => 30,
              '#default_value' => (($this->currentUser->hasPermission('link to any page') || $item->getUrl()->access())) ? self::getUriAsDisplayableString($uri) : NULL,
              '#element_validate' => [
                [
                  'Drupal\link\Plugin\Field\FieldWidget\LinkWidget',
                  'validateUriElement',
                ],
              ],
            ];
          }
          else {
            $start['url'] = [];
            throw new \Exception('Unable to load link entity.');
          }
        }

      }
      else {
        $start['title_edit'] = [];
        $start['url'] = [];
      }

      // Re-assemble.
      $element = array_merge($start, $end);
    }

    // If any links were editable, add the headers.
    if ($has_editable) {
      // Add columns to the header.
      $header = $form_links['links']['#header'];
      // @todo Is there a reliable way to dynamically find the Operations header?
      $operations_offset = 3;
      $start = array_slice($header, 0, $operations_offset, TRUE);
      $end = array_slice($header, $operations_offset, NULL, TRUE);
      $start[] = $this->t('Edit Title');
      $start[] = $this->t('Edit URL');
      $form_links['links']['#header'] = array_merge($start, $end);
    }
    else {

      // Remove all Edit Title and URL cells.
      foreach ($links as $id) {
        /** @var \Drupal\Core\Menu\MenuLinkTreeElement $item */
        $element = &$form_links['links'][$id];
        unset($element['title_edit']);
        unset($element['url']);
      }

    }

    // Add submit handler.
    $form['actions']['submit']['#submit'][] = [$this, 'submitForm'];
  }

  /**
   * Handle submission of the menu edit form.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\menu_ui\MenuForm::submitForm()
   * @see \Drupal\menu_ui\MenuForm::submitOverviewForm()
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Form API supports constructing and validating self-contained sections
    // within forms, but does not allow to handle the form section's submission
    // equally separated yet. Therefore, we use a $form_state key to point to
    // the parents of the form section.
    $parents = $form_state->get('menu_overview_form_parents');
    $input = NestedArray::getValue($form_state->getUserInput(), $parents);
    $form = &NestedArray::getValue($form, $parents);

    // When dealing with saving menu items, the order in which these items are
    // saved is critical. If a changed child item is saved before its parent,
    // the child item could be saved with an invalid path past its immediate
    // parent. To prevent this, save items in the form in the same order they
    // are sent, ensuring parents are saved first, then their children.
    // See https://www.drupal.org/node/181126#comment-632270.
    $order = is_array($input) ? array_flip(array_keys($input)) : [];
    // Update our original form with the new order.
    $form = array_intersect_key(array_merge($order, $form), $form);

    // Process the custom fields.
    $fields = ['title_edit', 'url'];
    $form_links = $form['links'];
    foreach (Element::children($form_links) as $id) {
      if (isset($form_links[$id]['#item'])) {
        $element = $form_links[$id];
        $updated_values = [];
        $url = '';

        // Use the ID from the actual plugin instance since the hidden value
        // in the form could be tampered with.
        $plugin_id = $element['#item']->link->getPLuginId();

        // Update any fields that have changed in this menu item.
        foreach ($fields as $field) {
          if (isset($element[$field]['#value']) && $element[$field]['#value'] != $element[$field]['#default_value']) {
            if ($field === 'url') {
              $url = $element[$field]['#value'];
            }
            elseif ($field === 'title_edit') {
              $updated_values['title'] = $element[$field]['#value'];
            }
            else {
              $updated_values[$field] = $element[$field]['#value'];
            }
          }
        }
        if ($updated_values) {
          $this->menuLinkManager->updateDefinition($plugin_id, $updated_values);
        }

        // Update the URL, if applicable.
        // @todo If "url" is an allowed override in MenuLinkContent, why does it throw an error during "updateDefinition"?
        // @todo Update menu link content uri the correct way.
        if ($url !== '') {
          $uri = self::getUserEnteredStringAsUri($url);
          $menu_link_plugin = $this->menuLinkManager->createInstance($plugin_id);
          /** @var \Drupal\menu_link_content\Entity\MenuLinkContent $entity */
          $entity = $this->entityRepository->loadEntityByUuid('menu_link_content', $menu_link_plugin->getDerivativeId());
          $entity->link->setValue(['uri' => $uri]);
          $entity->save();
        }

      }
    }
  }

  /**
   * Gets the URI without the 'internal:' or 'entity:' scheme.
   *
   * NOTE:
   * This is a copy of \Drupal\link\Plugin\Field\FieldWidget\LinkWidget
   * until the proper implementation of the uri field has been applied.
   *
   * The following two forms of URIs are transformed:
   * - 'entity:' URIs: to entity autocomplete ("label (entity id)") strings;
   * - 'internal:' URIs: the scheme is stripped.
   *
   * This method is the inverse of ::getUserEnteredStringAsUri().
   *
   * @param string $uri
   *   The URI to get the displayable string for.
   *
   * @return string
   *   The URI.
   *
   * @see static::getUserEnteredStringAsUri()
   */
  protected static function getUriAsDisplayableString($uri) {
    $scheme = parse_url($uri, PHP_URL_SCHEME);

    // By default, the displayable string is the URI.
    $displayable_string = $uri;

    // A different displayable string may be chosen in case of the 'internal:'
    // or 'entity:' built-in schemes.
    if ($scheme === 'internal') {
      $uri_reference = explode(':', $uri, 2)[1];

      // @todo '<front>' is valid input for BC reasons, may be removed by
      //   https://www.drupal.org/node/2421941
      $path = parse_url($uri, PHP_URL_PATH);
      if ($path === '/') {
        $uri_reference = '<front>' . substr($uri_reference, 1);
      }

      $displayable_string = $uri_reference;
    }
    elseif ($scheme === 'entity') {
      [$entity_type, $entity_id] = explode('/', substr($uri, 7), 2);
      // Show the 'entity:' URI as the entity autocomplete would.
      // @todo Support entity types other than 'node'. Will be fixed in
      //   https://www.drupal.org/node/2423093.
      if ($entity_type == 'node' && $entity = \Drupal::entityTypeManager()->getStorage($entity_type)->load($entity_id)) {
        $displayable_string = EntityAutocomplete::getEntityLabels([$entity]);
      }
    }
    elseif ($scheme === 'route') {
      $displayable_string = ltrim($displayable_string, 'route:');
    }

    return $displayable_string;
  }

  /**
   * Gets the user-entered string as a URI.
   *
   * NOTE:
   * This is a copy from \Drupal\link\Plugin\Field\FieldWidget
   * until the proper implementation of the uri field has been applied.
   *
   * The following two forms of input are mapped to URIs:
   * - entity autocomplete ("label (entity id)") strings: to 'entity:' URIs;
   * - strings without a detectable scheme: to 'internal:' URIs.
   *
   * This method is the inverse of ::getUriAsDisplayableString().
   *
   * @param string $string
   *   The user-entered string.
   *
   * @return string
   *   The URI, if a non-empty $uri was passed.
   *
   * @see static::getUriAsDisplayableString()
   */
  protected static function getUserEnteredStringAsUri($string) {
    // By default, assume the entered string is a URI.
    $uri = trim($string);

    // Detect entity autocomplete string, map to 'entity:' URI.
    $entity_id = EntityAutocomplete::extractEntityIdFromAutocompleteInput($string);
    if ($entity_id !== NULL) {
      // @todo Support entity types other than 'node'. Will be fixed in
      //   https://www.drupal.org/node/2423093.
      $uri = 'entity:node/' . $entity_id;
    }
    // Support linking to nothing.
    elseif (in_array($string, ['<nolink>', '<none>', '<button>'], TRUE)) {
      $uri = 'route:' . $string;
    }
    // Detect a schemeless string, map to 'internal:' URI.
    elseif ($string !== '' && parse_url($string, PHP_URL_SCHEME) === NULL) {
      // @todo '<front>' is valid input for BC reasons, may be removed by
      //   https://www.drupal.org/node/2421941
      // - '<front>' -> '/'
      // - '<front>#foo' -> '/#foo'
      if (strpos($string, '<front>') === 0) {
        $string = '/' . substr($string, strlen('<front>'));
      }
      $uri = 'internal:' . $string;
    }

    return $uri;
  }

}
