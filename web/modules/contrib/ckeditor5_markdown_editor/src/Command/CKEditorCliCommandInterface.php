<?php

namespace Drupal\ckeditor5_markdown_editor\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\StyleInterface;

/**
 * Defines an interface for our CKEditor cli.
 */
interface CKEditorCliCommandInterface {

  /**
   * Retrieve the command input service.
   *
   * @return \Symfony\Component\Console\Input\InputInterface
   *   The input service.
   */
  public function getInput(): InputInterface;

  /**
   * Retrieve the i/o style.
   *
   * @return \Symfony\Component\Console\Style\StyleInterface
   *   The i/o style.
   */
  public function getIo(): StyleInterface;

  /**
   * Retrieve message text.
   *
   * @param string $message_key
   *   The key of the requested message.
   *
   * @return string
   *   The requested message.
   */
  public function getMessage(string $message_key): string;

  /**
   * Present confirmation question to user.
   *
   * @param string $question
   *   The confirmation question.
   * @param bool $default
   *   The default value to return if user doesn’t enter any valid input.
   *
   * @return mixed
   *   The user answer
   */
  public function confirmation(string $question, bool $default = FALSE): mixed;

  /**
   * Output message in comment style.
   *
   * @param string $text
   *   The comment message.
   */
  public function comment(string $text);

}
