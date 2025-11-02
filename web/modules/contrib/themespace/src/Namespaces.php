<?php

namespace Drupal\themespace;

/**
 * Traverse namespaces for modules and themes either together or separately.
 *
 * Collects the module and theme namespaces, and can return them as iterators
 * to traverse both namespaces together or separately.
 */
class Namespaces implements NamespacesInterface {

  /**
   * Creates a new instance of the Themespace Namespaces class.
   *
   * @param \Traversable|array $namespaces
   *   Module namespaces as an iterable object. The key is the namespace, and
   *   value is the path to the source files.
   * @param \Traversable|array $themespaces
   *   Theme namespaces as an iterable object. The key is the namespace, and
   *   value is the path to the source files.
   */
  public function __construct(
    public readonly \Traversable|array $namespaces,
    public readonly \Traversable|array $themespaces,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getIterator(?string $type = NULL): \Traversable {
    switch ($type) {
      case 'module':
      case 'modules':
        return yield from $this->namespaces;

      case 'theme':
      case 'themes':
        return yield from $this->themespaces;
    }

    return yield from [
      ...$this->namespaces,
      ...$this->themespaces,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getModuleIterator(): \Traversable {
    return yield from $this->namespaces;
  }

  /**
   * {@inheritdoc}
   */
  public function getThemeIterator(): \Traversable {
    return yield from $this->themespaces;
  }

}
