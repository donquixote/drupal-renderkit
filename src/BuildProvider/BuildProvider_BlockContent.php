<?php

namespace Drupal\renderkit\BuildProvider;

use Drupal\cfrreflection\Configurator\Configurator_CallbackMono;
use Drupal\renderkit\Configurator\Id\Configurator_BlockId;

class BuildProvider_BlockContent implements BuildProviderInterface {

  /**
   * @var string
   */
  private $module;

  /**
   * @var string
   */
  private $delta;

  /**
   * @CfrPlugin("blockContent", "Block content")
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function configurator() {
    return Configurator_CallbackMono::createFromClassStaticMethod(
      self::class,
      'create',
      new Configurator_BlockId());
  }

  /**
   * @param string $moduleDotDelta
   *
   * @return self|null
   */
  public static function create($moduleDotDelta) {

    list($module, $delta) = explode('.', $moduleDotDelta, 2) + ['', ''];

    if ('' === $delta || '' === $module) {
      return NULL;
    }

    if (!module_exists($module)) {
      return NULL;
    }

    // @todo Throw exception if misconfigured?

    return new self($module, $delta);
  }

  /**
   * @param string $module
   * @param string $delta
   */
  public function __construct($module, $delta) {
    $this->module = $module;
    $this->delta = $delta;
  }

  /**
   * @return array
   *   A render array.
   */
  public function build() {

    if (!module_hook($this->module, 'block_view')) {
      return [];
    }

    $function = $this->module . '_block_view';

    $block = $function($this->delta);

    if (!\is_array($block)) {
      return [];
    }

    if (empty($block['content'])) {
      return [];
    }

    $content = $block['content'];

    if (\is_string($content)) {
      return ['#markup' => $content];
    }

    if (\is_array($content)) {
      return $content;
    }

    return [];
  }
}
