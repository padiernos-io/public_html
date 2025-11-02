<?php

namespace Drupal\pathauto_update;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Utility\Token;
use Drupal\pathauto\AliasCleanerInterface;
use Drupal\pathauto\PathautoGeneratorInterface;
use Drupal\pathauto\PathautoPatternInterface;
use Drupal\token\TokenEntityMapperInterface;

/**
 * Resolves dependencies for path aliases.
 */
class PathAliasDependencyResolver implements PathAliasDependencyResolverInterface {

  /**
   * The token service.
   *
   * @var \Drupal\Core\Utility\Token
   */
  protected Token $token;

  /**
   * The token entity mapper.
   *
   * @var \Drupal\token\TokenEntityMapperInterface
   */
  protected TokenEntityMapperInterface $tokenEntityMapper;

  /**
   * The alias cleaner.
   *
   * @var \Drupal\pathauto\AliasCleanerInterface
   */
  protected AliasCleanerInterface $aliasCleaner;

  /**
   * The alias generator.
   *
   * @var \Drupal\pathauto\PathautoGeneratorInterface
   */
  protected PathautoGeneratorInterface $aliasGenerator;

  /**
   * The pattern token dependency provider manager.
   *
   * @var \Drupal\pathauto_update\PatternTokenDependencyProviderManager
   */
  protected PatternTokenDependencyProviderManager $patternTokenDependenciesManager;

  public function __construct(
    Token $token,
    TokenEntityMapperInterface $tokenEntityMapper,
    AliasCleanerInterface $aliasCleaner,
    PathautoGeneratorInterface $aliasGenerator,
    PatternTokenDependencyProviderManager $patternTokenDependenciesManager
  ) {
    $this->token = $token;
    $this->tokenEntityMapper = $tokenEntityMapper;
    $this->aliasCleaner = $aliasCleaner;
    $this->aliasGenerator = $aliasGenerator;
    $this->patternTokenDependenciesManager = $patternTokenDependenciesManager;
  }

  /**
   * {@inheritdoc}
   */
  public function getDependencies(EntityInterface $entity): PathAliasDependencyCollectionInterface {
    $pattern = $this->aliasGenerator->getPatternByEntity($entity);
    $dependencies = new PathAliasDependencyCollection();

    if ($pattern) {
      $this->addDependenciesFromTokens($dependencies, $pattern, $entity);
    }

    return $dependencies;
  }

  /**
   * Add dependencies from tokens.
   */
  protected function addDependenciesFromTokens(PathAliasDependencyCollectionInterface $dependencies, PathautoPatternInterface $pattern, EntityInterface $entity): void {
    $tokensByType = $this->token->scan($pattern->getPattern());
    $entityTokenType = $this->tokenEntityMapper->getTokenTypeForEntityType($entity->getEntityTypeId());
    $data = [$entityTokenType => $entity];

    $langcode = $entity->language()->getId();
    // Core does not handle aliases with language Not Applicable.
    if ($langcode === LanguageInterface::LANGCODE_NOT_APPLICABLE) {
      $langcode = LanguageInterface::LANGCODE_NOT_SPECIFIED;
    }

    $options = [
      'clear' => TRUE,
      'callback' => [$this->aliasCleaner, 'cleanTokenValues'],
      'langcode' => $langcode,
      'pathauto' => TRUE,
    ];

    foreach ($tokensByType as $type => $tokens) {
      if (!$this->patternTokenDependenciesManager->hasDefinition($type)) {
        continue;
      }

      $this->patternTokenDependenciesManager->createInstance($type)
        ->addDependencies($tokens, $data, $options, $dependencies);
    }
  }

}
