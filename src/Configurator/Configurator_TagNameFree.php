<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface;
use Drupal\cfrapi\Configurator\Optional\OptionalConfiguratorBase;
use Drupal\cfrapi\Exception\ConfToValueException;
use Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface;

class Configurator_TagNameFree extends OptionalConfiguratorBase {

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param string|null $label
   *   Label for the form element, specifying the purpose where it is used.
   *
   * @return array
   */
  public function confGetForm($conf, $label) {

    if ($this->confIsEmpty($conf) || !$this->confIsValid($conf)) {
      $conf = NULL;
    }

    return [
      /* @see theme_textfield() */
      '#type' => 'textfield',
      '#title' => $label,
      '#default_value' => $conf,
      '#required' => $this->isRequired(),
      /* @see elementValidate() */
      '#element_validate' => [[$this, 'elementValidate']]
    ];
  }

  /**
   * Form #element_validate callback.
   *
   * @param array $element
   * @param array $form_state
   * @param array $form
   */
  public function elementValidate(
    array $element,
    /** @noinspection PhpUnusedParameterInspection */ array $form_state,
    /** @noinspection PhpUnusedParameterInspection */ array $form
  ) {
    if (!$this->confIsValid($element['#value'])) {
      form_error($element, t('Value does not seem to be a valid HTML tag name.'));
    }
  }

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface $summaryBuilder
   *
   * @return mixed
   */
  protected function nonEmptyConfGetSummary($conf, SummaryBuilderInterface $summaryBuilder) {

    if (!$this->confIsValid($conf)) {
      return '- ' . t('Invalid') . ' -';
    }

    return "'$conf'";
  }

  /**
   * @param mixed $conf
   *
   * @return mixed
   *
   * @throws \Drupal\cfrapi\Exception\ConfToValueException
   */
  protected function nonEmptyConfGetValue($conf) {

    if (!$this->confIsValid($conf)) {
      throw new ConfToValueException("Not a valid tag name.");
    }

    return $conf;
  }

  /**
   * @param mixed $conf
   * @param \Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface $helper
   *
   * @return mixed
   */
  protected function nonEmptyConfGetPhp($conf, CfrCodegenHelperInterface $helper) {

    if (!$this->confIsValid($conf)) {
      return $helper->incompatibleConfiguration($conf, "Not a valid tag name.");
    }

    return var_export($conf, TRUE);
  }

  /**
   * @param mixed $conf
   *
   * @return bool
   */
  public function confIsEmpty($conf) {
    return NULL === $conf || '' === $conf;
  }

  /**
   * @param mixed $conf
   *
   * @return bool
   */
  private function confIsValid($conf) {
    return is_string($conf) && preg_match('/^[\w]+$/', $conf) && strlen($conf) < 14;
  }
}
