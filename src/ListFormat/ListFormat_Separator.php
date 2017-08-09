<?php

namespace Drupal\renderkit\ListFormat;

use Donquixote\Cf\Schema\GroupVal\CfSchema_GroupVal_Callback;
use Drupal\renderkit\Schema\CfSchema_ListSeparator;

/**
 * Concatenates the list items with a separator.
 */
class ListFormat_Separator implements ListFormatInterface {

  /**
   * @var string
   */
  private $separator;

  /**
   * @CfrPlugin(
   *   id = "separator",
   *   label = @t("Separator")
   * )
   *
   * @return \Donquixote\Cf\Schema\GroupVal\CfSchema_GroupValInterface
   */
  public static function createSchema() {

    return CfSchema_GroupVal_Callback::fromClass(
      __CLASS__,
      [
        new CfSchema_ListSeparator(),
      ],
      [
        t('Separator'),
      ]);
  }

  /**
   * @param string $separator
   */
  public function __construct($separator = '') {
    $this->separator = $separator;
  }

  /**
   * @param array[] $builds
   *   Array of render arrays for list items.
   *   Must not contain any property keys like "#..".
   *
   * @return array
   *   Render array for the list.
   */
  public function buildList(array $builds) {
    return [
      /* @see renderkit_theme() */
      /* @see theme_themekit_separator_list() */
      '#theme' => 'themekit_separator_list',
      '#separator' => $this->separator,
    ] + $builds;
  }
}
