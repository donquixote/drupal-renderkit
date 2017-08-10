<?php

namespace Drupal\renderkit8\EntityToEntity;

use Donquixote\Cf\Schema\GroupVal\CfSchema_GroupVal_Callback;
use Drupal\Core\Entity\EntityInterface;
use Drupal\renderkit8\Schema\CfSchema_EntityBundleName;

class EntityToEntity_UserProfile2 extends EntityToEntityBase {

  /**
   * @var null|string
   */
  private $profile2TypeName;

  /**
   * @var \Drupal\renderkit8\EntityToEntity\EntityToEntityInterface|null
   */
  private $entityToUser;

  /**
   * @ CfrPlugin("userProfile2", @t("Profile2 from user or author"))
   *
   * @return \Donquixote\Cf\Schema\CfSchemaInterface|null
   */
  public static function createSchema() {

    if (!module_exists('profile2')) {
      return NULL;
    }

    return CfSchema_GroupVal_Callback::fromStaticMethod(
      self::class,
      'create',
      [
        CfSchema_EntityBundleName::create('profile2'),
      ],
      [
        t('Profile type'),
      ]
    );
  }

  /**
   * @param string|null $profile2TypeName
   *
   * @return \Drupal\renderkit8\EntityToEntity\EntityToEntity_ChainOfTwo|\Drupal\renderkit8\EntityToEntity\EntityToEntity_UserProfile2
   */
  public static function create($profile2TypeName = NULL) {
    return new self($profile2TypeName, new EntityToEntity_EntityAuthor());
  }

  /**
   * @param string|null $profile2TypeName
   *   The profile2 bundle name.
   * @param \Drupal\renderkit8\EntityToEntity\EntityToEntityInterface|null $entityToUser
   */
  public function __construct(
    $profile2TypeName = NULL,
    EntityToEntityInterface $entityToUser = NULL
  ) {

    $this->profile2TypeName = $profile2TypeName;

    if (NULL !== $entityToUser && 'user' === $entityToUser->getTargetType()) {
      $this->entityToUser = $entityToUser;
    }
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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return null|\Drupal\Core\Entity\EntityInterface
   */
  public function entityGetRelated(EntityInterface $entity) {

    if (NULL === $user = $this->entityGetUser($entityType, $entity)) {
      return NULL;
    }

    $profile_or_profiles = \profile2_load_by_user($user, $this->profile2TypeName);

    if (!is_array($profile_or_profiles)) {
      return $profile_or_profiles ?: NULL;
    }

    return reset($profile_or_profiles) ?: NULL;
  }

  /**
   * @param string $entityType
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return null|object
   */
  private function entityGetUser($entityType, $entity) {

    if ('user' === $entityType) {
      return $entity;
    }

    if (NULL !== $this->entityToUser) {
      return $this->entityToUser->entityGetRelated($entity);
    }

    return NULL;
  }
}
