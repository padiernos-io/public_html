<?php

namespace Drupal\improve_line_breaks_filter;

/**
 * Provides text modification methods.
 */
class TextReplacement {

  /**
   * Replace empty paragraphs, e.g. <p></p> or <p>&nbsp;</p> with <br />.
   *
   * @param string $text
   *   The source text.
   * @param bool $remove_empty_paragraphs
   *   Determines whether empty paragraphs will be deleted or replaced.
   *
   * @return string
   *   The output text with correct line breaks.
   */
  public function processImproveLineBreaks($text, $remove_empty_paragraphs = FALSE) {
    // Split at opening and closing PRE, SCRIPT, STYLE, OBJECT, CODE, IFRAME
    // tags and comments. We don't apply any processing to the contents of these
    // tags to avoid messing up code.
    $chunks = preg_split('@(<!--.*?-->|</?(?:pre|script|style|object|code|iframe|!--)[^>]*>)@i', $text, -1, PREG_SPLIT_DELIM_CAPTURE);

    $ignore = FALSE;
    $ignoretag = '';
    $output = '';
    foreach ($chunks as $i => $chunk) {
      if ($i % 2) {
        $comment = (substr($chunk, 0, 4) == '<!--');
        if ($comment) {
          $output .= $chunk;
          continue;
        }

        // Opening or closing tag?
        $open = ($chunk[1] != '/');
        list($tag) = preg_split('/[ >]/', substr($chunk, 2 - $open), 2);
        if (!$ignore) {
          if ($open) {
            $ignore = TRUE;
            $ignoretag = $tag;
          }
        }
        // Only allow a matching tag to close it.
        elseif (!$open && $ignoretag == $tag) {
          $ignore = FALSE;
          $ignoretag = '';
        }
      }
      // Skip ignore tags and replace empty paragraphs with <br /> tag.
      elseif (!$ignore) {
        if ($remove_empty_paragraphs) {
          $chunk = $this->removeEmptyParagraph($chunk);
        }
        else {
          $chunk = $this->replaceEmptyParagraph($chunk);
        }
      }
      $output .= $chunk;
    }
    return $output;
  }

  /**
   * Replace empty paragraphs with line breaks tags.
   *
   * @param string $text
   *   The source text.
   *
   * @return string
   *   The output text.
   */
  protected function replaceEmptyParagraph($text) {
    return preg_replace('/<p>(&nbsp;|\s)*<\/p>/ui', '<br />', $text);
  }

  /**
   * Remove empty paragraphs.
   *
   * @param string $text
   *   The source text.
   *
   * @return string
   *   The output text.
   */
  protected function removeEmptyParagraph($text) {
    return preg_replace('/<p>(&nbsp;|\s)*<\/p>/ui', '', $text);
  }

}
