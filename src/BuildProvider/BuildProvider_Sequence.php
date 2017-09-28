<?php

namespace Drupal\renderkit\BuildProvider;

use Drupal\cfrapi\Configurator\Sequence\Configurator_Sequence;
use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\ListFormat\ListFormatInterface;

class BuildProvider_Sequence implements BuildProviderInterface {

  /**
   * @var \Drupal\renderkit\BuildProvider\BuildProviderInterface[]
   */
  private $providers;

  /**
   * @var \Drupal\renderkit\ListFormat\ListFormatInterface
   */
  private $listFormat;

  /**
   * @CfrPlugin(
   *   id = "sequence",
   *   label = "Sequence of build providers"
   * )
   *
   * @param \Drupal\cfrapi\Context\CfrContextInterface $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator(CfrContextInterface $context = NULL) {
    return Configurator_CallbackConfigurable::createFromClassName(
      __CLASS__,
      [
        new Configurator_Sequence(
          cfrplugin()->interfaceGetOptionalConfigurator(
            BuildProviderInterface::class,
            $context)
        ),
        cfrplugin()->interfaceGetOptionalConfigurator(
          ListFormatInterface::class,
          $context),
      ],
      [
        t('Build providers'),
        t('List format'),
      ]
    );
  }

  /**
   * @param \Drupal\renderkit\BuildProvider\BuildProviderInterface[] $providers
   * @param \Drupal\renderkit\ListFormat\ListFormatInterface $listFormat
   */
  public function __construct(array $providers, ListFormatInterface $listFormat = NULL) {
    $this->providers = $providers;
    $this->listFormat = $listFormat;
  }

  /**
   * @return array
   *   A render array.
   */
  public function build() {
    $builds = [];
    foreach ($this->providers as $k => $provider) {
      $build = $provider->build();
      if (is_array($build) && [] !== $build) {
        $builds[$k] = $provider->build();
      }
    }
    if ([] === $builds) {
      return [];
    }
    if (NULL !== $this->listFormat) {
      $builds = $this->listFormat->buildList($builds);
    }
    return $builds;
  }
}
