<?php

declare(strict_types=1);

namespace Drupal\form_decorator\Drush;

use DrupalCodeGenerator\Asset\AssetCollection as Assets;
use DrupalCodeGenerator\Attribute\Generator;
use DrupalCodeGenerator\Command\BaseGenerator;
use DrupalCodeGenerator\GeneratorType;

/**
 * Form decorator generator.
 */
#[Generator(
  name: 'plugin:form-decorator',
  aliases: ['form-decorator'],
  description: 'Generates a form decorator plugin class.',
  templatePath: __DIR__,
  type: GeneratorType::MODULE_COMPONENT,
)]
class FormDecoratorGenerator extends BaseGenerator {

  /**
   * {@inheritdoc}
   */
  protected function generate(array &$vars, Assets $assets): void {
    $ir = $this->createInterviewer($vars);
    $vars['machine_name'] = $ir->askMachineName();
    $vars['class'] = $ir->askClass('Class name', 'MyFormDecorator');
    $vars['services'] = $ir->askServices(FALSE);
    $vars['base_class'] = $ir->choice('What type of form do you want to decorate?', [
      'ContentEntityFormDecoratorBase' => 'Content entity form',
      'EntityFormDecoratorBase' => 'Entity form',
      'FormDecoratorBase' => 'Generic form',
    ]);
    $vars['method'] = $ir->choice('How you want to decide which forms are decorated?', [
      'form_id' => 'Decorate a specific form ID (Or base form ID)',
      'hook_form_alter' => 'I know how I would write a hook_form_FORM_ID_alter',
      'applies' => 'I want to implement a custom applies method',
    ]);
    switch ($vars['method']) {
      case 'form_id':
        $vars['hook'] = 'form_' . $ir->ask('Form ID', 'user_login_form') . '_alter';
        break;

      case 'hook_form_alter':
        $vars['hook'] = $ir->ask('The hook_form_alter, hook_form_FORM_ID_alter or hook_form_BASE_FORM_ID_alter function name without the hook_ prefix:', 'user_login_form_alter');
        break;
    }
    $assets->addFile('src/FormDecorator/' . $vars['class'] . '.php', 'FormDecorator.php.twig');
  }

}
