<?php

namespace Drupal\renderkit\BuildProvider;

use Drupal\renderkit\BuildProcessor\BuildProcessorInterface;

/**
 * @CfrPlugin("withBuildProcessor", "With build processor")
 */
class BuildProvider_WithBuildProcessor implements BuildProviderInterface {

  /**
   * @var \Drupal\renderkit\BuildProvider\BuildProviderInterface
   */
  private $decorated;

  /**
   * @var \Drupal\renderkit\BuildProcessor\BuildProcessorInterface
   */
  private $processor;

  /**
   * @param \Drupal\renderkit\BuildProvider\BuildProviderInterface $decorated
   * @param \Drupal\renderkit\BuildProcessor\BuildProcessorInterface $processor
   */
  public function __construct(BuildProviderInterface $decorated, BuildProcessorInterface $processor) {
    $this->decorated = $decorated;
    $this->processor = $processor;
  }

  /**
   * @return array
   *   A render array.
   */
  public function build() {
    $build = $this->decorated->build();
    $build = $this->processor->process($build);
    return $build;
  }
}
