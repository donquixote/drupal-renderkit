<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface;
use Drupal\cfrapi\Configurator\ConfiguratorInterface;
use Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface;
use Drupal\renderkit\BuildProcessor\BuildProcessor_Container;
use Drupal\renderkit\EntityBuildProcessor\EntityBuildProcessor_Wrapper_LinkToEntity;
use Drupal\renderkit\EntityDisplay\Decorator\EntityDisplay_WithBuildProcessor;
use Drupal\renderkit\EntityDisplay\Decorator\EntityDisplay_WithEntityBuildProcessor;
use Drupal\renderkit\EntityDisplay\EntityDisplay_Title;

class Configurator_EntityDisplay_Title implements ConfiguratorInterface {

  /**
   * @var string[]
   */
  private $allowedTagNames;

  /**
   * @return \Drupal\renderkit\Configurator\Configurator_EntityDisplay_Title
   */
  public static function create() {
    return new self(['h1', 'h2', 'h3', 'h4', 'strong']);
  }

  /**
   * TitleEntityDisplayPlugin constructor.
   *
   * @param string[] $allowedTagNames
   */
  public function __construct(array $allowedTagNames) {
    $this->allowedTagNames = array_combine($allowedTagNames, $allowedTagNames);
  }

  /**
   * @param array $conf
   *
   * @param $label
   *
   * @return array
   */
  public function confGetForm($conf, $label) {
    list($tagName, $link) = $this->confGetNormalized($conf);

    $form = [];
    $form['tag_name'] = [
      '#type' => 'select',
      '#options' => $this->allowedTagNames,
      '#title' => t('Wrapper'),
      '#empty_option' => t('- No wrapper -'),
      '#default_value' => $tagName,
    ];
    $form['link'] = [
      '#type' => 'checkbox',
      '#title' => t('Link to entity'),
      '#default_value' => $link,
    ];
    return $form;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param \Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface $summaryBuilder
   *
   * @return null|string
   */
  public function confGetSummary($conf, SummaryBuilderInterface $summaryBuilder) {
    list($wrapperTagName, $link) = $this->confGetNormalized($conf);

    if ($link && NULL !== $wrapperTagName) {
      return $wrapperTagName . ', ' . t('linked to entity') . '.';
    }

    if ($link) {
      return t('Linked to entity');
    }

    if (NULL !== $wrapperTagName) {
      return $wrapperTagName;
    }

    return t('Raw title');
  }

  /**
   * Gets a handler object that does the business logic, or null, or dummy
   * object.
   *
   * @param array $conf
   *   Configuration for the handler object creation, if this plugin is
   *   configurable.
   *
   * @return \Drupal\renderkit\EntityDisplay\EntityDisplayInterface
   */
  public function confGetValue($conf) {
    list($wrapperTagName, $link) = $this->confGetNormalized($conf);

    $display = new EntityDisplay_Title();

    if ($link) {
      $wrapper = new EntityBuildProcessor_Wrapper_LinkToEntity();
      $display = new EntityDisplay_WithEntityBuildProcessor($display, $wrapper);
    }

    if (NULL !== $wrapperTagName) {
      $container = new BuildProcessor_Container();
      $container->setTagName($wrapperTagName);
      $display = new EntityDisplay_WithBuildProcessor($display, $container);
    }

    return $display;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param \Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface $helper
   *
   * @return string
   *   PHP statement to generate the value.
   */
  public function confGetPhp($conf, CfrCodegenHelperInterface $helper) {
    list($wrapperTagName, $link) = $this->confGetNormalized($conf);

    $display_php = 'new ' . EntityDisplay_Title::class . '()';

    if ($link) {
      $wrapper = 'new ' . EntityBuildProcessor_Wrapper_LinkToEntity::class . '()';
      $display_php = 'new ' . EntityDisplay_WithEntityBuildProcessor::class . "(\n  $display_php,\n  $wrapper)";
    }

    if (NULL !== $wrapperTagName) {
      $container = '(new ' . BuildProcessor_Container::class . '())';
      $container .= '->setTagName(' . var_export($wrapperTagName, TRUE)  . ')';
      $display_php = 'new ' . EntityDisplay_WithBuildProcessor::class . "(\n  $display_php,\n  $container)";
    }

    return $display_php;
  }

  /**
   * @param mixed $conf
   *
   * @return array
   *   Format: [$tagName, $link]
   */
  private function confGetNormalized($conf) {

    if (!\is_array($conf)) {
      $conf = [];
    }

    if (0
      || !isset($conf['tag_name'])
      || !array_key_exists($conf['tag_name'], $this->allowedTagNames)
    ) {
      $conf['tag_name'] = NULL;
    }

    $conf['link'] = !empty($conf['link']);

    return [$conf['tag_name'], $conf['link']];
  }
}
