<?php

namespace Drupal\opcachectl\Twig\Extension;

use Twig\Extension\AbstractExtension;
use Twig\TwigFilter;
use Twig\TwigTest;

/**
 * Class TypeTest
 *
 * All credits go to https://github.com/victor-in/Craft-TwigTypeTest !
 *
 * @see https://github.com/victor-in/Craft-TwigTypeTest/blob/master/twigtypetest/twigextensions/TwigTypeTestTwigExtension.php
 *
 * @package Drupal\opcachectl\Twig\Extension
 */
class TypeTest extends AbstractExtension {

  public function getName() {
    return 'type_test';
  }

  public function getTests() {
    return [
      new TwigTest('of_type', $this->ofType(...)),
    ];
  }

  public function getFilters() {
    return [
      new TwigFilter('get_type', $this->getType(...)),
    ];
  }

  function ofType($var, $typeTest = NULL, $className = NULL): bool {
    switch ($typeTest) {
      default:
        return FALSE;

      case 'array':
        return is_array($var);

      case 'bool':
        return is_bool($var);

      case 'class':
        return is_object($var) === TRUE && get_class($var) === $className;

      case 'float':
        return is_float($var);

      case 'int':
        return is_int($var);

      case 'numeric':
        return is_numeric($var);

      case 'object':
        return is_object($var);

      case 'scalar':
        return is_scalar($var);

      case 'string':
        return is_string($var);
    }
  }

  public function getType($var): string {
    return gettype($var);
  }

}
