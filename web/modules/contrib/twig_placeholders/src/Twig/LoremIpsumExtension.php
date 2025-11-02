<?php

namespace Drupal\twig_placeholders\Twig;

use Drupal\twig_placeholders\Service\LoremIpsumGenerator;
use Twig\Extension\AbstractExtension;
use Twig\TwigFunction;

/**
 * Provides a Twig function for generating Lorem Ipsum text.
 */
class LoremIpsumExtension extends AbstractExtension {
  /**
   * The Lorem Ipsum generator service.
   *
   * @var \Drupal\twig_placeholders\Service\LoremIpsumGenerator
   */
  protected LoremIpsumGenerator $generator;

  /**
   * Constructs a TwigPlaceholdersExtension object.
   *
   * @param \Drupal\twig_placeholders\Service\LoremIpsumGenerator $generator
   *   The Lorem Ipsum generator service.
   */
  public function __construct(LoremIpsumGenerator $generator) {
    $this->generator = $generator;
  }

  /**
   * {@inheritdoc}
   */
  public function getFunctions(): array {
    return [
      new TwigFunction('tp_lorem_ipsum', $this->generator->generate(...)),
    ];
  }

}
