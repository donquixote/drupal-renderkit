<?php

namespace Drupal\renderkit\ListFormat;

interface ListFormatInterface {

  /**
   * @param array[] $builds
   *   Array of render arrays for list items.
   *   Must not contain any property keys like "#..".
   *
   * @return array
   *   Render array for the list.
   */
  function buildList(array $builds);

}
