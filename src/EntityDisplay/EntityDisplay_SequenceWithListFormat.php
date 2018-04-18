<?php

namespace Drupal\renderkit\EntityDisplay;

use Drupal\cfrapi\Context\CfrContextInterface;
use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\cfrreflection\Configurator\Configurator_CallbackMono;
use Drupal\renderkit\ListFormat\ListFormat;
use Drupal\renderkit\ListFormat\ListFormatInterface;

/**
 * A sequence of entity display handlers, whose results are assembled into a
 * single render array.
 *
 * This can be used for something like a layout region with a number of fields
 * or elements.
 */
class EntityDisplay_SequenceWithListFormat extends EntityDisplay_Sequence {

  /**
   * @var \Drupal\renderkit\ListFormat\ListFormatInterface
   */
  private $listFormat;

  /**
   * @CfrPlugin("sequence", @t("Sequence of entity displays, with list format"))
   *
   * @param \Drupal\cfrapi\Context\CfrContextInterface $context
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function plugin(CfrContextInterface $context = NULL) {
    return Configurator_CallbackConfigurable::createFromClassStaticMethod(
      self::class,
      'create',
      [
        EntityDisplay::sequenceConfigurator(),
        ListFormat::optionalConfigurator(),
      ],
      [
        t('Entity displays'),
        t('List format'),
      ]);
  }

  /**
   * @param array $displayHandlers
   * @param \Drupal\renderkit\ListFormat\ListFormatInterface|NULL $listFormat
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  public static function create(array $displayHandlers, ListFormatInterface $listFormat = NULL) {
    return NULL === $listFormat
      ? new EntityDisplay_Sequence($displayHandlers)
      : new self($displayHandlers, $listFormat);
  }

  /**
   * @param \Drupal\renderkit\EntityDisplay\EntityDisplayInterface[] $displayHandlers
   * @param \Drupal\renderkit\ListFormat\ListFormatInterface $listFormat
   */
  public function __construct(array $displayHandlers, ListFormatInterface $listFormat) {
    parent::__construct($displayHandlers);
    $this->listFormat = $listFormat;
  }

  /**
   * @param string $entityType
   * @param object[] $entities
   *
   * @return array[]
   *   An array of render arrays, keyed by the original array keys of $entities.
   */
  public function buildEntities($entityType, array $entities) {

    $builds = parent::buildEntities($entityType, $entities);

    return $this->listFormat->buildList($builds);
  }
}
