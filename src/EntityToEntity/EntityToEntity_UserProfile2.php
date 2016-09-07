<?php

namespace Drupal\renderkit\EntityToEntity;

use Drupal\cfrreflection\Configurator\Configurator_CallbackConfigurable;
use Drupal\renderkit\Configurator\Id\Configurator_EntityBundleName;

class EntityToEntity_UserProfile2 extends EntityToEntityBase {

  /**
   * @var null|string
   */
  private $profile2TypeName;

  /**
   * @CfrPlugin(
   *   id = "userProfile2",
   *   label = "Profile2 from user or author"
   * )
   *
   * @param string $entityType
   *   Contextual parameter.
   *
   * @return \Drupal\cfrapi\Configurator\ConfiguratorInterface
   */
  public static function createConfigurator($entityType = NULL) {
    return Configurator_CallbackConfigurable::createFromCallable(
      function($profile2TypeName = NULL) use ($entityType) {
        return self::create($entityType, $profile2TypeName);
      },
      [new Configurator_EntityBundleName('profile2', FALSE)],
      [t('Profile type')]
    );
  }

  /**
   * @param string|null $entityType
   * @param string|null $profile2TypeName
   *
   * @return \Drupal\renderkit\EntityToEntity\EntityToEntity_ChainOfTwo|\Drupal\renderkit\EntityToEntity\EntityToEntity_UserProfile2
   */
  public static function create($entityType = NULL, $profile2TypeName = NULL) {
    $userToProfile2 = new self($profile2TypeName);
    if ('user' === $entityType) {
      return $userToProfile2;
    }
    if (NULL === $entityType) {
      $entityToUser = EntityToEntity_EntityAuthor::userEntityOrAuthor();
    }
    else {
      $entityToUser = new EntityToEntity_EntityAuthor();
    }
    return new EntityToEntity_ChainOfTwo($entityToUser, $userToProfile2);
  }

  /**
   * @param string|null $profile2TypeName
   *   The profile2 bundle name.
   */
  public function __construct($profile2TypeName = NULL) {
    $this->profile2TypeName = $profile2TypeName;
  }

  /**
   * Gets the entity type of the referenced entities.
   *
   * @return string
   */
  public function getTargetType() {
    return 'profile2';
  }

  /**
   * @param string $entityType
   * @param object $entity
   *
   * @return object|null
   */
  public function entityGetRelated($entityType, $entity) {
    if ('user' !== $entityType) {
      return NULL;
    }
    $profile_or_profiles = \profile2_load_by_user($entity, $this->profile2TypeName);
    if (!is_array($profile_or_profiles)) {
      return $profile_or_profiles ?: NULL;
    }
    return reset($profile_or_profiles) ?: NULL;
  }
}
