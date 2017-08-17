<?php

namespace Drupal\renderkit8\EntityDisplay;

use Donquixote\Cf\Schema\GroupVal\CfSchema_GroupVal_Callback;
use Donquixote\Cf\Schema\Iface\CfSchema_IfaceWithContext;
use Drupal\Core\Entity\EntityInterface;
use Drupal\renderkit8\LabeledEntityBuildProcessor\LabeledEntityBuildProcessorInterface;
use Drupal\renderkit8\Schema\CfSchema_ViewIdWithDisplayId;
use Drupal\views\Views;

/**
 * Show a view (from "views" module) for the entity.
 */
class EntityDisplay_ViewsDisplay extends EntityDisplayBase {

  /**
   * @var string
   */
  private $viewName;

  /**
   * @var string
   */
  private $displayId;

  /**
   * @var \Drupal\renderkit8\LabeledEntityBuildProcessor\LabeledEntityBuildProcessorInterface
   */
  private $labeledDisplay;

  /**
   * @CfrPlugin(
   *   id = "viewsDisplay",
   *   label = @t("Views display")
   * )
   *
   * @param string|null $entityType
   *
   * @return \Donquixote\Cf\Schema\CfSchemaInterface
   */
  public static function createSchema($entityType = NULL) {

    return CfSchema_GroupVal_Callback::fromStaticMethod(
      __CLASS__,
      'create',
      [
        CfSchema_ViewIdWithDisplayId::createWithEntityIdArg($entityType),
        CfSchema_IfaceWithContext::createOptional(LabeledEntityBuildProcessorInterface::class),
      ],
      [
        t('Views display'),
        t('Label format'),
      ]);
  }

  /**
   * @param string $id
   * @param \Drupal\renderkit8\LabeledEntityBuildProcessor\LabeledEntityBuildProcessorInterface|null $labeledEntityBuildProcessor
   *
   * @return \Drupal\renderkit8\EntityDisplay\EntityDisplay_ViewsDisplay|null
   */
  public static function create($id, LabeledEntityBuildProcessorInterface $labeledEntityBuildProcessor = NULL) {

    list($view_name, $display_id) = explode(':', $id) + [NULL, NULL];

    if (NULL === $display_id) {
      return NULL;
    }

    // No further checking at this point.
    return new self(
      $view_name,
      $display_id,
      $labeledEntityBuildProcessor);
  }

  /**
   * @param string $viewName
   * @param string $displayId
   * @param \Drupal\renderkit8\LabeledEntityBuildProcessor\LabeledEntityBuildProcessorInterface $labeledDisplay
   */
  public function __construct($viewName, $displayId, LabeledEntityBuildProcessorInterface $labeledDisplay = NULL) {
    $this->viewName = $viewName;
    $this->displayId = $displayId;
    $this->labeledDisplay = $labeledDisplay;
  }

  /**
   * Same as ->buildEntities(), just for a single entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Single entity object for which to build a render arary.
   *
   * @return array
   */
  public function buildEntity(EntityInterface $entity) {

    if (NULL === $etid = $entity->id()) {
      return [
        '#markup' => __LINE__,
      ];
    }

    if (NULL === $view = Views::getView($this->viewName)) {
      return [
        '#markup' => __LINE__,
      ];
    }

    if (FALSE === $view->setDisplay($this->displayId)) {
      return [
        '#markup' => __LINE__,
      ];
    }

    $view->initHandlers();

    $arguments = $view->argument;

    if ([] === $arguments) {
      return [
        '#markup' => __LINE__,
      ];
    }

    $argPlugin = array_shift($arguments);

    if ([] !== $arguments) {
      return [
        '#markup' => __LINE__,
      ];
    }

    if (!isset($argPlugin->options['validate']['type'])) {
      return [
        '#markup' => __LINE__,
      ];
    }

    if ('entity:' . $entity->getEntityTypeId() !== $argPlugin->options['validate']['type']) {
      return [
        '#markup' => __LINE__,
      ];
    }

    $args = [$etid];

    // @todo Some of this might not be required?
    $view->setArguments($args);
    $view->preExecute();
    $view->execute();

    $content = $view->buildRenderable($this->displayId, $args);

    if (NULL === $this->labeledDisplay) {
      return $content;
    }

    $label = $view->getTitle();

    if (empty($label)) {
      return $content;
    }

    return $this->labeledDisplay->buildAddLabelWithEntity(
      $content,
      $entity,
      $label);
  }
}
