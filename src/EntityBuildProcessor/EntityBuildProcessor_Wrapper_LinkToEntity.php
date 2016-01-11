<?php

namespace Drupal\renderkit\EntityBuildProcessor;


use Drupal\renderkit\Html\HtmlAttributesInterface;
use Drupal\renderkit\Html\HtmlAttributesTrait;

/**
 * Wraps the content from a decorated display handler into a link, linking to
 * the entity.
 *
 * Just like the base class, this does provide methods like addClass() to modify
 * the attributes of the link element.
 *
 * A typical use case would be to wrap in image into a link element.
 *
 * @CfrPlugin(
 *   id = "entityLinkWrapper",
 *   label = @t("Entity link wrapper")
 * )
 */
class EntityBuildProcessor_Wrapper_LinkToEntity extends EntityBuildProcessorBase implements HtmlAttributesInterface {

  use HtmlAttributesTrait;

  /**
   * @param array $build
   * @param string $entity_type
   * @param object $entity
   *
   * @return array
   *   Render array for one entity.
   */
  function processEntityBuild(array $build, $entity_type, $entity) {
    $link_uri = entity_uri($entity_type, $entity);
    $link_uri['options']['attributes'] = $this->attributes;
    return array(
      $build,
      /* @see themekit_element_info() */
      /* @see theme_themekit_link_wrapper() */
      '#type' => 'themekit_link_wrapper',
      '#path' => $link_uri['path'],
      '#options' => $link_uri['options'],
    );
  }

}