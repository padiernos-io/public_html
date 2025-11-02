<?php

namespace Drupal\Tests\themespace\Unit;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Tests\UnitTestCase;
use Drupal\Tests\themespace\DerivableTestPluginDefinition;
use Drupal\Tests\themespace\TestPluginDefinition;
use Drupal\themespace\Plugin\Definition\ProviderTypedPluginDefinition;

/**
 * Tests to verify plugin definitions.
 *
 * @group themespace
 */
class PluginDefinitionTest extends UnitTestCase {

  /**
   * The a sample of the base plugin definition values.
   *
   * @return array
   *   The basic example plugin definition information to start testing
   *   plugin definitions with.
   */
  protected function getSampleDefinition(): array {
    return [
      'id' => 'test',
      'label' => 'hello',
      'class' => '\Drupal\Component\Plugin\PluginBase',
      'provider' => 'themespace_test',
      'providerType' => 'module',
    ];
  }

  /**
   * Ensure that plugin definition catches missing provider information.
   */
  public function testMissingProvider(): void {
    $this->expectException(InvalidPluginDefinitionException::class);
    new ProviderTypedPluginDefinition([
      'id' => 'test_definition',
      'providerType' => 'theme',
    ]);
  }

  /**
   * Ensure that plugin definition catches missing provider type.
   *
   * Expects an "InvalidPluginDefinition" exception to indicate that the
   * definition is incomplete and lacking a provider type property.
   */
  public function testMissingProviderType(): void {
    $this->expectException(InvalidPluginDefinitionException::class);
    new ProviderTypedPluginDefinition([
      'id' => 'test_definition',
      'provider' => 'themespace_test',
    ]);
  }

  /**
   * Test that base constructor applies properties correctly.
   *
   * Checks that defaults are kept, that properties not defined in the
   * definition are not written to, and provider info it properly applied.
   */
  public function testBasicConstructor(): void {
    require_once dirname(__DIR__) . '/TestPluginDefinition.php';

    $values = $this->getSampleDefinition() + [
      'assignable' => 'assigned',
      'nonExisting' => 'not assignable',
      'staticVal' => 'modified',
    ];

    $definition = new TestPluginDefinition($values);
    $this->assertEquals('test', $definition->id());
    $this->assertEquals('themespace_test', $definition->getProvider());
    $this->assertEquals('module', $definition->getProviderType());
    $this->assertEquals('\Drupal\Component\Plugin\PluginBase', $definition->getClass());
    $this->assertEquals('hello', $definition->label);
    $this->assertEquals('default', $definition->defaulted);
    $this->assertEquals('assigned', $definition->assignable);
    $this->assertFalse(isset($definition->nonExisting));

    // Ensure that static class properties are not getting assigned implicitly
    // from the $definition parameter.
    $this->assertEquals('original', TestPluginDefinition::$staticVal);

    // Create another definition, just to check that the default value
    // was property overridden from the $definition parameter.
    $values['defaulted'] = 'overridden';
    $definition = new TestPluginDefinition($values);
    $this->assertEquals('overridden', $definition->defaulted);
  }

  /**
   * Testing the assignment of the plugin definition deriver.
   */
  public function testDerivableConstructor(): void {
    require_once dirname(__DIR__) . '/TestPluginDefinition.php';
    require_once dirname(__DIR__) . '/DerivableTestPluginDefinition.php';

    $values = $this->getSampleDefinition() + [
      'deriver' => 'Drupal\\themespace_test\\Plugin\\Derivatives\\TestDeriver',
    ];

    // Test that value is assigned and is not blocked if definition does not
    // implement DerivablePluginDefinitionInterrface and is just treated as a
    // just another value. Generally definitions that use derivers should use
    // the DerivablePluginDefinitionInterface, but themespace should not do
    // anything to block definitions which may have other use cases.
    $pluginDef = new TestPluginDefinition($values);
    $this->assertEquals($values['deriver'], $pluginDef->deriver);

    // Ensure that deriver got assigned, and make sure to use the
    // \Drupal\Component\Plugin\Definition\DerivablePluginDefinitionInterface::setDeriver()
    // instead of the direct value assignment by key.
    $deriverDef = new DerivableTestPluginDefinition($values);
    $this->assertEquals($values['deriver'], $deriverDef->getDeriver());
    $this->assertEmpty($deriverDef->deriver);
  }

}
