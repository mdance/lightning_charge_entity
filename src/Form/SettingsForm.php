<?php

namespace Drupal\lightning_charge_entity\Form;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Form\ConfigFormBase;
use Drupal\lightning_charge_entity\LightningChargeEntityConstants;
use Drupal\lightning_charge_entity\LightningChargeEntityServiceInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Drupal\Core\Form\FormStateInterface;

/**
 * Provides the SettingsForm class.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * Provides the module service.
   *
   * @var \Drupal\lightning_charge_entity\LightningChargeEntityServiceInterface
   */
  protected $service;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    LightningChargeEntityServiceInterface $service
  ) {
    parent::__construct($config_factory);

    $this->service = $service;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('lightning_charge_entity')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'lightning_charge_entity_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return [
      LightningChargeEntityConstants::KEY_SETTINGS
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, Request $request = NULL) {
    $form['#tree'] = TRUE;

    $rebuild = $form_state->isRebuilding();
    $user_input = $form_state->getUserInput();

    $form = parent::buildForm($form, $form_state);

    $wrapper_id = 'wrapper-settings-form';

    $form['#attributes']['id'] = $wrapper_id;

    $key = 'price_mode';

    $options = $this->service->getPriceModes(FALSE);

    $default_value = $this->service->getPriceMode();

    $selector = $key;

    $form[$key] = [
      '#type' => 'radios',
      '#title' => $this->t('Price Mode'),
      '#options' => $options,
      '#default_value' => $default_value,
      '#attributes' => [
        'class' => [
          $selector,
        ],
      ],
    ];

    $key = 'price';

    $default_value = $this->service->getPrice([], FALSE);

    $form[$key] = [
      '#type' => 'commerce_price',
      '#title' => t('Price'),
      '#default_value' => $default_value,
      '#states' => [
        'visible' => [
          ".$selector" => [
            'value' => LightningChargeEntityConstants::PRICE_MODE_CUSTOM,
          ],
        ],
      ],
    ];

    $key = 'entity_types';

    $entity_types = $this->service->getEntityTypes();

    $default_value = $this->service->getEnabledEntityTypeOptions();

    if ($rebuild) {
      $parents = [
        $key,
      ];

      $default_value = NestedArray::getValue($user_input, $parents);
    } else {
      $default_value = array_keys($default_value);
    }

    if (is_array($default_value)) {
      $default_value = array_filter($default_value);
    }

    $options = $this->service->getEntityTypeOptions();

    $form[$key] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Entity Types'),
      '#options' => $options,
      '#default_value' => $default_value,
      '#ajax' => [
        'wrapper' => $wrapper_id,
        'callback' => '::jsCallback',
      ],
      '#slice_length' => -3,
    ];

    $enabled_entity_types = array_intersect_key($entity_types, array_combine($default_value, $default_value));

    foreach ($enabled_entity_types as $entity_type_id => $entity_type) {
      /** @var \Drupal\Core\Entity\EntityTypeInterface $entity_type */
      $args = [];

      $args['@label'] = $entity_type->getLabel();

      $title = $this->t('@label Settings', $args);

      $form[$entity_type_id] = [
        '#type' => 'details',
        '#title' => $title,
        '#open' => TRUE,
      ];

      $base = [
        $entity_type_id,
      ];

      $subform = &$form[$entity_type_id];

      $key = 'price_mode';

      $options = $this->service->getPriceModes();

      $params = [
        'entity_type' => $entity_type_id,
      ];

      $default_value = $this->service->getPriceMode($params);

      $selector = $entity_type_id . '-' . $key;

      $subform[$key] = [
        '#type' => 'radios',
        '#title' => $this->t('Price Mode'),
        '#options' => $options,
        '#default_value' => $default_value,
        '#attributes' => [
          'class' => [
            $selector,
          ],
        ],
      ];

      $key = 'price';

      $params = [];

      $params['entity_type'] = $entity_type_id;

      $default_value = $this->service->getPrice($params, FALSE);

      $subform[$key] = [
        '#type' => 'commerce_price',
        '#title' => t('Price'),
        '#default_value' => $default_value,
        '#states' => [
          'visible' => [
            ".$selector" => [
              'value' => LightningChargeEntityConstants::PRICE_MODE_CUSTOM,
            ],
          ],
        ],
      ];

      $key = 'overrides';

      $params = [];

      $params['entity_type'] = $entity_type_id;

      $default_value = $this->service->getOverrides($params);
      $selector = $key . '-' . $entity_type_id;

      $subform[$key] = [
        '#type' => 'checkbox',
        '#title' => $this->t('Enable Bundle Settings'),
        '#default_value' => $default_value,
        '#attributes' => [
          'class' => [
            $selector,
          ],
        ],
      ];

      $key = 'bundles';

      $subform[$key] = [
        '#type' => 'container',
        '#states' => [
          'visible' => [
            ".$selector" => [
              'checked' => TRUE,
            ],
          ],
        ],
      ];

      $bundles_form = &$subform[$key];

      $base[] = $key;

      $key = 'enabled';

      $options = $this->service->getEntityTypeBundleOptions($entity_type_id);

      $default_value = $this->service->getEnabledEntityTypeBundles($entity_type_id);
      $default_value = array_keys($default_value);

      if ($rebuild) {
        $parents = $base;
        $parents[] = $key;

        $default_value = NestedArray::getValue($user_input, $parents) ?? $default_value;
      }

      if (is_array($default_value)) {
        $default_value = array_filter($default_value);
        $default_value = array_combine($default_value, $default_value);
      }

      $bundles_form[$key] = [
        '#type' => 'checkboxes',
        '#title' => $this->t('Bundles'),
        '#options' => $options,
        '#default_value' => $default_value,
        '#ajax' => [
          'wrapper' => $wrapper_id,
          'callback' => '::jsCallback',
        ],
        '#slice_length' => -4,
      ];

      $enabled_entity_type_bundles = $this->service->getEntityTypeBundles($entity_type_id);
      $enabled_entity_type_bundles = array_intersect_key($enabled_entity_type_bundles, $default_value);

      $view_modes = $this->service->getEntityTypeViewModes($entity_type_id);

      foreach ($enabled_entity_type_bundles as $bundle => $v) {
        $args = [];

        $args['@bundle'] = $v['label'];

        $title = $this->t('@bundle Settings', $args);

        $bundles_form[$bundle] = [
          '#type' => 'details',
          '#title' => $title,
          '#open' => TRUE,
        ];

        $bundle_form = &$bundles_form[$bundle];

        $key = 'price_mode';

        $options = $this->service->getPriceModes();

        $params = [];

        $params['entity_type'] = $entity_type_id;
        $params['bundle'] = $bundle;

        $default_value = $this->service->getPriceMode($params);

        $selector = $entity_type_id . '-' . $bundle . '-' . $key;

        $bundle_form[$key] = [
          '#type' => 'radios',
          '#title' => $this->t('Price Mode'),
          '#options' => $options,
          '#default_value' => $default_value,
          '#attributes' => [
            'class' => [
              $selector,
            ],
          ],
        ];

        $key = 'price';

        $params = [];

        $params['entity_type'] = $entity_type_id;
        $params['bundle'] = $bundle;

        $default_value = $this->service->getPrice($params, FALSE);

        $bundle_form[$key] = [
          '#type' => 'commerce_price',
          '#title' => t('Price'),
          '#default_value' => $default_value,
          '#states' => [
            'visible' => [
              ".$selector" => [
                'value' => LightningChargeEntityConstants::PRICE_MODE_CUSTOM,
              ],
            ],
          ],
        ];

        $key = 'instances';

        $params = [];

        $params['entity_type'] = $entity_type_id;
        $params['bundle'] = $bundle;

        $default_value = $this->service->getInstancesOverrides($params);

        $selector = $entity_type_id . '-' . $bundle . '-' . $key;

        $bundle_form[$key] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable Instance Settings'),
          '#default_value' => $default_value,
          '#attributes' => [
            'class' => [
              $selector,
            ],
          ],
        ];

        $key = 'overrides';

        $params = [];

        $params['entity_type'] = $entity_type_id;
        $params['bundle'] = $bundle;

        $default_value = $this->service->getOverrides($params);
        $selector = $entity_type_id . '-' . $bundle . '-' . $key;

        $bundle_form[$key] = [
          '#type' => 'checkbox',
          '#title' => $this->t('Enable View Mode Settings'),
          '#default_value' => $default_value,
          '#attributes' => [
            'class' => [
              $selector,
            ],
          ],
        ];

        $key = 'view_modes';

        $bundle_form[$key] = [
          '#type' => 'container',
          '#states' => [
            'visible' => [
              ".$selector" => [
                'checked' => TRUE,
              ],
            ],
          ],
        ];

        $view_modes_form = &$bundle_form[$key];

        $key = 'enabled';

        $enabled_view_modes = $this->service->getEnabledViewModes($entity_type_id, $bundle);

        if ($rebuild) {
          // node[bundles][article][view_modes][enabled][full]
          $parents = $base;
          $parents[] = $bundle;
          $parents[] = 'view_modes';
          $parents[] = $key;

          $enabled_view_modes = NestedArray::getValue($user_input, $parents) ?? $enabled_view_modes;
        }

        if (is_array($enabled_view_modes)) {
          $enabled_view_modes = array_filter($enabled_view_modes);
        }

        $default_value = array_keys($enabled_view_modes);

        $view_modes_form[$key] = [
          '#type' => 'checkboxes',
          '#title' => $this->t('View Modes'),
          '#options' => $view_modes,
          '#default_value' => $default_value,
          '#ajax' => [
            'wrapper' => $wrapper_id,
            'callback' => '::jsCallback',
          ],
          '#slice_length' => -4,
        ];

        foreach ($enabled_view_modes as $view_mode => $view_mode_info) {
          $args = [];

          $args['@view_mode'] = $view_modes[$view_mode];

          $title = $this->t('@view_mode Settings', $args);

          $view_modes_form[$view_mode] = [
            '#type' => 'details',
            '#title' => $title,
            '#open' => TRUE,
          ];

          $view_mode_form = &$view_modes_form[$view_mode];

          $key = 'price_mode';

          $options = $this->service->getPriceModes();

          $params = [];

          $params['entity_type'] = $entity_type_id;
          $params['bundle'] = $bundle;
          $params['view_mode'] = $view_mode;

          $default_value = $this->service->getPriceMode($params);

          $selector = $entity_type_id . '-' . $bundle . '-' . $view_mode . '-' . $key;

          $view_mode_form[$key] = [
            '#type' => 'radios',
            '#title' => $this->t('Price Mode'),
            '#options' => $options,
            '#default_value' => $default_value,
            '#attributes' => [
              'class' => [
                $selector,
              ],
            ],
          ];

          $key = 'price';

          $params = [];

          $params['entity_type'] = $entity_type_id;
          $params['bundle'] = $bundle;
          $params['view_mode'] = $view_mode;

          $default_value = $this->service->getPrice($params, FALSE);

          $view_mode_form[$key] = [
            '#type' => 'commerce_price',
            '#title' => t('Price'),
            '#default_value' => $default_value,
            '#states' => [
              'visible' => [
                ".$selector" => [
                  'value' => LightningChargeEntityConstants::PRICE_MODE_CUSTOM,
                ],
              ],
            ],
          ];
        }
      }
    }


    return $form;
  }

  /**
   * Provides the ajax callback.
   */
  public function jsCallback($form, FormStateInterface $form_state) {
    $triggering_element = $form_state->getTriggeringElement();

    $parents = $triggering_element['#parents'];
    $length = $triggering_element['#slice_length'];

    $parents = array_splice($parents, 0, $length);

    $element = NestedArray::getValue($form, $parents);

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->cleanValues()->getValues();
    unset($values['actions']);

    $config = $this->config(LightningChargeEntityConstants::KEY_SETTINGS);

    foreach ($values as $key => $value) {
      $config->set($key, $value);
    }

    $config->save();


    parent::submitForm($form, $form_state);
  }

}
