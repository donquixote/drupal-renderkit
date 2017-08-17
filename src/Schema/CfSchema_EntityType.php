<?php

namespace Drupal\renderkit8\Schema;

use Donquixote\Cf\Schema\Options\CfSchema_Options_FromFlatOptions;
use Donquixote\Cf\Schema\Options\Flat\CfSchema_FlatOptionsInterface;
use Drupal\Core\Entity\EntityTypeRepositoryInterface;

class CfSchema_EntityType implements CfSchema_FlatOptionsInterface {

  /**
   * @var \Drupal\Core\Entity\EntityTypeRepositoryInterface
   */
  private $entityTypeRepository;

  /**
   * @return \Donquixote\Cf\Schema\Options\CfSchema_OptionsInterface
   */
  public static function createOptionsSchema() {
    return new CfSchema_Options_FromFlatOptions(self::create());
  }

  /**
   * @return self
   */
  public static function create() {
    /** @var \Drupal\Core\Entity\EntityTypeRepositoryInterface $entityTypeRepository */
    $entityTypeRepository = \Drupal::service('entity_type.repository');
    return new self($entityTypeRepository);
  }

  /**
   * @param \Drupal\Core\Entity\EntityTypeRepositoryInterface $entityTypeRepository
   */
  public function __construct(EntityTypeRepositoryInterface $entityTypeRepository) {
    $this->entityTypeRepository = $entityTypeRepository;
  }

  /**
   * @return string[]
   */
  public function getOptions() {

    $options = $this->entityTypeRepository->getEntityTypeLabels();

    asort($options);

    return $options;
  }

  /**
   * @param string $id
   *
   * @return string|null
   */
  public function idGetLabel($id) {

    $options = $this->entityTypeRepository->getEntityTypeLabels();

    return isset($options[$id])
      ? $options[$id]
      : NULL;
  }

  /**
   * @param string $id
   *
   * @return bool
   */
  public function idIsKnown($id) {

    $options = $this->entityTypeRepository->getEntityTypeLabels();

    return isset($options[$id]);
  }
}
