<?php

namespace Drupal\renderkit\Configurator;

use Drupal\cfrapi\CfrCodegenHelper\CfrCodegenHelperInterface;
use Drupal\cfrapi\Configurator\ConfiguratorInterface;
use Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface;
use Drupal\renderkit\ListFormat\ListFormat_ElementDefaults;

/**
 * @CfrPlugin("expert", "Expert")
 *
 * This is inspired by Display suite.
 */
class Configurator_ListFormat_Expert implements ConfiguratorInterface {

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param string|null $label
   *   Label for the form element, specifying the purpose where it is used.
   *
   * @return array
   *
   * @see ds_extras_field_template_settings_form()
   */
  public function confGetForm($conf, $label) {

    $form = [
      '#type' => 'container',
      '#attributes' => ['class' => ['listformat-ui-expert-groups']],
      '#process' => [RENDERKIT_POP_PARENT],
    ];

    $wrappers = [
      'items' => t('List items'),
      'item' => t('List item'),
    ];

    /** @var string $k */

    foreach ($wrappers as $wrapper_key => $wrapper_label) {

      $form[$wrapper_key] = [
        '#type' => 'container',
        '#attributes' => ['class' => ['listformat-ui-expert-group']],
        '#process' => [RENDERKIT_POP_PARENT],
      ];

      foreach (['enabled', 'tag_name', 'classes'] as $colname) {
        $form[$wrapper_key][$colname] = [
          '#type' => 'container',
          '#process' => [RENDERKIT_POP_PARENT],
        ];
      }

      $form[$wrapper_key][$colname = 'enabled'][$k = $wrapper_key] = [
        '#type' => 'checkbox',
        '#title' => $wrapper_label,
        '#default_value' => !empty($conf[$k]),
      ];

      $form[$wrapper_key][$colname = 'tag_name'][$k = $wrapper_key . '-' . $colname] = [
        '#type' => 'textfield',
        '#size' => '10',
        '#title' => t('Element'),
        # '#description' => t('Leave empty for no tag around each item.'),
        '#default_value' => isset($conf[$k]) ? $conf[$k] : '',
        '#states' => [
          'visible' => [
            ':input[name$="[' . $wrapper_key . ']"]' => ['checked' => TRUE],
          ],
        ],
      ];

      $form[$wrapper_key][$colname = 'classes'][$k = $wrapper_key . '-' . $colname] = [
        '#type' => 'textfield',
        '#title' => t('Classes'),
        # '#description' => t('Classes separated by spaces. Has no effect if wrapper tag name is empty.'),
        '#default_value' => isset($conf[$k]) ? $conf[$k] : '',
        '#states' => [
          'visible' => [
            ':input[name$="[' . $wrapper_key . ']"]' => ['checked' => TRUE],
          ],
        ],
      ];
    }

    $form['item']['classes'][$k = 'zebra'] = [
      '#type' => 'checkbox',
      '#title' => t("Zebra striping ('even' / 'odd' classes)"),
      '#default_value' => !empty($conf[$k]),
      '#states' => [
        'visible' => [
          ':input[name$="[item]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form['item']['classes'][$k = 'firstlast'] = [
      '#type' => 'checkbox',
      '#title' => t("Classes for 'first' / 'last'"),
      '#default_value' => !empty($conf[$k]),
      '#states' => [
        'visible' => [
          ':input[name$="[item]"]' => ['checked' => TRUE],
        ],
      ],
    ];

    $form = [
      '#type' => 'fieldset',
      'groups' => $form,
    ];

    $form['#attached']['css'][] = drupal_get_path('module', 'renderkit') . '/css/renderkit.admin.css';

    return $form;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   * @param \Drupal\cfrapi\SummaryBuilder\SummaryBuilderInterface $summaryBuilder
   *   An object that controls the format of the summary.
   *
   * @return mixed|string|null
   *   A string summary is always allowed. But other values may be returned if
   *   $summaryBuilder generates them.
   */
  public function confGetSummary($conf, SummaryBuilderInterface $summaryBuilder) {
    // No summary details.
    return NULL;
  }

  /**
   * @param mixed $conf
   *   Configuration from a form, config file or storage.
   *
   * @return \Drupal\renderkit\ListFormat\ListFormatInterface
   */
  public function confGetValue($conf) {
    $defaults = $this->confGetElementDefaults($conf);
    return new ListFormat_ElementDefaults($defaults);
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
    $defaults = $this->confGetElementDefaults($conf);
    return 'new ' . ListFormat_ElementDefaults::class . '('
      . "\n" . $helper->export($defaults) . ')';
  }

  /**
   * @param mixed $conf
   *
   * @return array
   *   Render array defaults.
   */
  private function confGetElementDefaults($conf) {

    if (!\is_array($conf)) {
      $conf = [];
    }

    $defaults = [];

    if (!empty($conf['items'])) {

      /* @see themekit_element_info() */
      /* @see theme_themekit_container() */
      $defaults['#type'] = 'themekit_container';

      $defaults['#tag_name'] = !empty($conf['items-tag_name'])
        ? (trim($conf['items-tag_name']) ?: 'div')
        : 'div';

      if (!empty($conf['items-classes'])) {
        if ([] !== $classes = array_unique(array_filter(explode(' ', $conf['items-classes'])))) {
          $defaults['#attributes']['class'] = $classes;
        }
      }
    }

    if (!empty($conf['item'])) {

      /* @see theme_themekit_item_containers() */
      $defaults['#theme'] = 'themekit_item_containers';

      $defaults['#item_tag_name'] = !empty($conf['item-tag_name'])
        ? (trim($conf['item-tag_name']) ?: 'div')
        : 'div';

      if (!empty($conf['item-classes'])) {
        if ([] !== $item_classes = array_unique(array_filter(explode(' ', $conf['item-classes'])))) {
          $defaults['#item_attributes']['class'] = $item_classes;
        }
      }

      if (!empty($conf['zebra'])) {
        $defaults['#zebra'] = TRUE;
      }

      if (!empty($conf['firstlast'])) {
        $defaults['#first'] = TRUE;
        $defaults['#last'] = TRUE;
      }
    }

    return $defaults;
  }
}
