<?php

namespace Drupal\lightning_charge_entity;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lightning_charge\LightningChargeConstants;
use Drupal\lightning_charge\LightningChargeServiceInterface;
use Drupal\lightning_charge_entity\Events\LightningChargeEntityEvents;
use Drupal\lightning_charge_entity\Events\LightningChargeEntityTypesEvent;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

/**
 * Provides the LightningChargeEntityService class.
 */
class LightningChargeEntityService implements LightningChargeEntityServiceInterface {

  use StringTranslationTrait;

  /**
   * Provides the request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * Provides the request.
   *
   * @var \Symfony\Component\HttpFoundation\Request|null
   */
  protected $request;

  /**
   * Provides the session.
   *
   * @var \Symfony\Component\HttpFoundation\Session\SessionInterface
   */
  protected $session;

  /**
   * Provides the database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Provides the current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Provides the entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Provides the entity type bundle info service.
   *
   * @var \Drupal\Core\Entity\EntityTypeBundleInfoInterface
   */
  protected $entityTypeBundleInfo;

  /**
   * Provides the entity display repository.
   *
   * @var \Drupal\Core\Entity\EntityDisplayRepositoryInterface
   */
  protected $entityDisplayRepository;

  /**
   * Provides the config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * Provides the configuration.
   *
   * @var \Drupal\Core\Config\Config
   */
  protected $config;

  /**
   * Provides the event dispatcher.
   *
   * @var \Symfony\Component\EventDispatcher\EventDispatcherInterface
   */
  protected $eventDispatcher;

  /**
   * Provides the lightning charge service.
   *
   * @var \Drupal\lightning_charge\LightningChargeServiceInterface
   */
  protected $lightningCharge;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    RequestStack $request_stack,
    SessionInterface $session,
    Connection $connection,
    AccountProxyInterface $current_user,
    EntityTypeManagerInterface $entity_type_manager,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    EntityDisplayRepositoryInterface $entity_display_repository,
    ConfigFactoryInterface $config_factory,
    EventDispatcherInterface $event_dispatcher,
    LightningChargeServiceInterface $lightning_charge
  ) {
    $this->requestStack = $request_stack;
    $this->request = $request_stack->getCurrentRequest();

    $this->session = $session;

    $this->connection = $connection;

    $this->currentUser = $current_user;

    $this->entityTypeManager = $entity_type_manager;
    $this->entityTypeBundleInfo = $entity_type_bundle_info;
    $this->entityDisplayRepository = $entity_display_repository;

    $this->configFactory = $config_factory;
    $this->config = $config_factory->getEditable(LightningChargeEntityConstants::KEY_SETTINGS);

    $this->eventDispatcher = $event_dispatcher;

    $this->lightningCharge = $lightning_charge;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypes() {
    $output = $this->entityTypeManager->getDefinitions();

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeOptions() {
    $output = [];

    $results = $this->getEntityTypes();

    foreach ($results as $key => $value) {
      $output[$key] = $value->getLabel();
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEnabledEntityTypes() {
    $output = [];

    $enabled = $this->config->get('entity_types') ?? [];
    $total = count($enabled);

    if (!$total) {
      return $output;
    }

    $enabled = array_filter($enabled);
    $enabled = array_combine($enabled, $enabled);

    $results = $this->getEntityTypes();

    $output = array_intersect_key($results, $enabled);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEnabledEntityTypeOptions() {
    $output = [];

    $results = $this->getEnabledEntityTypes();

    foreach ($results as $k => $v) {
      $output[$k] = (string)$v->getLabel();
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeBundles($entity_type) {
    if ($entity_type instanceof EntityTypeInterface) {
      $entity_type = $entity_type->id();
    }

    return $this->entityTypeBundleInfo->getBundleInfo($entity_type);
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeBundleOptions($input) {
    $output = [];

    $results = $this->getEntityTypeBundles($input);

    foreach ($results as $k => $v) {
      $output[$k] = $v['label'];
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEnabledEntityTypeBundles($entity_type) {
    $output = [];

    $enabled = $this->config->get("$entity_type.bundles.enabled") ?? [];
    $enabled = array_filter($enabled);

    $total = count($enabled);

    if (!$total) {
      return $output;
    }

    $enabled = array_combine($enabled, $enabled);

    $results = $this->getEntityTypeBundles($entity_type);

    $output = array_intersect_key($results, $enabled);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEnabledEntityTypeBundleOptions($entity_type) {
    $output = [];

    $results = $this->getEnabledEntityTypeBundles($entity_type);

    foreach ($results as $k => $v) {
      $output[$k] = $v['label'];
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityTypeViewModes($entity_type) {
    $output = [];

    $results = $this->entityDisplayRepository->getViewModes($entity_type);

    foreach ($results as $k => $v) {
      $output[$k] = $v['label'];
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEnabledViewModes($entity_type, $bundle) {
    $output = [];

    $keys = [];

    $keys[] = $entity_type;
    $keys[] = 'bundles';
    $keys[] = $bundle;
    $keys[] = 'view_modes';
    $keys[] = 'enabled';

    $key = implode('.', $keys);

    $enabled = $this->config->get($key) ?? [];
    $total = count($enabled);

    if (!$total) {
      return $output;
    }

    $enabled = array_filter($enabled);
    $enabled = array_combine($enabled, $enabled);

    $results = $this->getEntityTypeViewModes($entity_type);

    $output = array_intersect_key($results, $enabled);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function isEnabled(EntityInterface $entity, $view_mode = NULL) {
    $entity_type = $entity->getEntityTypeId();

    $results = $this->getEnabledEntityTypeOptions();

    if (!isset($results[$entity_type])) {
      return FALSE;
    }

    $params = [];

    $params['entity_type'] = $entity_type;

    $result = $this->getOverrides($params);

    $bundle = $entity->bundle();

    if ($result) {
      $results = $this->getEnabledEntityTypeBundles($entity_type);

      if (!isset($results[$bundle])) {
        return FALSE;
      }
    }

    $params['bundle'] = $bundle;

    if ($view_mode) {
      $result = $this->getOverrides($params);

      if ($result) {
        $results = $this->getEnabledViewModes($entity_type, $bundle);

        if (!isset($results[$view_mode])) {
          return FALSE;
        }
      }
    }

    if (is_null($view_mode)) {
      $result = $this->getInstancesOverrides($params);

      if (!$result) {
        return FALSE;
      }
    }

    return TRUE;
  }

  /**
   * {@inheritDoc}
   */
  public function getPriceModes($inherit = TRUE) {
    $output = [];

    if ($inherit) {
      $output[LightningChargeEntityConstants::PRICE_MODE_INHERIT] = $this->t('Inherit');
    }

    $output[LightningChargeEntityConstants::PRICE_MODE_DONATION] = $this->t('Donation');
    $output[LightningChargeEntityConstants::PRICE_MODE_CUSTOM] = $this->t('Custom');

    return $output;
  }

  /**
   * Gets the config key.
   *
   * @param string $type
   *   A string containing the key type.
   * @param array $options
   *   An array of lookup options.
   *
   * @return array|mixed|null
   *   The configuration value.
   */
  private function getKey($type, array $options = []) {
    $defaults = [
      'entity_type' => NULL,
      'bundle' => NULL,
      'view_mode' => NULL,
      'entity' => NULL,
    ];

    $options = array_merge($defaults, $options);

    $entity_type = $options['entity_type'];
    $bundle = $options['bundle'];
    $view_mode = $options['view_mode'];
    $entity = $options['entity'];

    if ($entity instanceof EntityInterface) {
      $entity_type = $entity->getEntityTypeId();
      $bundle = $entity->bundle();
    }

    $key = [];

    if ($entity_type) {
      $key[] = $entity_type;
    }

    if ($bundle) {
      $key[] = 'bundles';
      $key[] = $bundle;
    }

    if ($view_mode) {
      $key[] = 'view_modes';
      $key[] = $view_mode;
    }

    $key[] = $type;

    $key = implode('.', $key);

    return $this->config->get($key);
  }

  /**
   * {@inheritDoc}
   */
  public function getPriceMode(array $options = []) {
    $output = LightningChargeEntityConstants::PRICE_MODE_CUSTOM;

    $o = $options;

    $key = 'view_mode';

    if (isset($o[$key])) {
      unset($o[$key]);
    }

    $result = $this->getInstancesOverrides($o);

    if ($result) {
      $key = 'entity';

      if (isset($options[$key])) {
        $entity = $options[$key];

        $results = $this->getEntity($entity);

        $view_mode = NULL;

        $key = 'view_mode';

        if (isset($options[$key])) {
          $view_mode = $options[$key];
        }

        foreach ($results as $result) {
          if (is_null($view_mode) && $result[$key] == '') {
            return $result['price_mode'];
          } else if ($view_mode == $result[$key]) {
            return $result['price_mode'];
          }
        }
      }
    }

    $result = $this->getKey('price_mode', $options);

    if ($result) {
      $output = $result;
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getPrice(array $options = [], bool $price_mode = TRUE) {
    $output = [
      'number' => '0.00',
      'currency_code' => '',
    ];

    $result = NULL;

    $o = $options;

    $key = 'view_mode';

    if (isset($o[$key])) {
      unset($o[$key]);
    }

    $overrides = $this->getInstancesOverrides($o);

    if ($overrides) {
      $key = 'entity';

      if (isset($options[$key])) {
        $entity = $options[$key];

        $results = $this->getEntity($entity);

        $view_mode = NULL;

        $key = 'view_mode';

        if (isset($options[$key])) {
          $view_mode = $options[$key];
        }

        foreach ($results as $v) {
          $found = FALSE;

          if (is_null($view_mode) && $v[$key] == '') {
            $found = TRUE;
          } else if ($view_mode == $v[$key]) {
            $found = TRUE;
          }

          if ($found) {
            $result = $v['price_mode'];

            switch ($result) {
              case LightningChargeEntityConstants::PRICE_MODE_INHERIT:
                break 2;
              case LightningChargeEntityConstants::PRICE_MODE_DONATION:
                return $output;
              case LightningChargeEntityConstants::PRICE_MODE_CUSTOM:
                return $v['price'];
            }
          }
        }
      }
    }

    if ($price_mode) {
      if (is_null($result)) {
        $result = LightningChargeEntityConstants::PRICE_MODE_INHERIT;
      }

      $keys = [
        'entity',
        'view_mode',
        'bundle',
        'entity_type',
      ];

      while ($result == LightningChargeEntityConstants::PRICE_MODE_INHERIT) {
        $result = $this->getKey('price_mode', $options);

        if ($result == LightningChargeEntityConstants::PRICE_MODE_INHERIT) {
          $key = array_shift($keys);

          if (isset($options[$key])) {
            unset($options[$key]);
          }
        }
      }
    }

    if (is_null($result) || $result == LightningChargeEntityConstants::PRICE_MODE_CUSTOM) {
      $result = $this->getKey('price', $options);

      if ($result) {
        $output = $result;
      }
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getOverrides(array $options = []) {
    $output = FALSE;

    $result = $this->getKey('overrides', $options);

    if ($result) {
      $output = $result;
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getInstancesOverrides(array $options = []) {
    $output = FALSE;

    $result = $this->getKey('instances', $options);

    if ($result) {
      $output = $result;
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getInvoices(EntityInterface $entity, $view_mode, &$hash, $account = NULL) {
    $output = [];

    if (is_null($account)) {
      $account = $this->currentUser;
    }

    $entity_type = $entity->getEntityTypeId();
    $bundle = $entity->bundle();
    $id = $entity->id();
    $label = $entity->label();

    $params = [
      'entity_type' => $entity_type,
      'bundle' => $bundle,
      'entity' => $entity,
      'view_mode' => $view_mode,
    ];

    $price = $this->getPrice($params);

    $args = [];

    $args['@label'] = $label;

    $description = t('@label', $args);

    $props = [
      'description' => $description,
      'amount' => $price['number'],
      'currency' => $price['currency_code'],
      'metadata' => [
        LightningChargeConstants::KEY_TYPE => LightningChargeEntityConstants::TYPE,
        'entity_type' => $entity_type,
        'bundle' => $bundle,
        'entity' => $id,
        'view_mode' => $view_mode,
      ],
    ];

    $metadata = &$props['metadata'];

    if ($account->isAnonymous()) {
      $metadata['ip'] = $this->request->getClientIp();
      $metadata['session'] = $this->session->getId();
    } else {
      $metadata['uid'] = $account->id();
    }

    $data = json_encode($metadata);
    $hash = hash('sha256', $data);

    $metadata['hash'] = $hash;

    $a = 'i';

    $query = $this->connection->select(LightningChargeEntityConstants::TABLE_INVOICES, $a);

    $fields = [
      'id',
      'invoice_id',
    ];

    $query->fields($a, $fields);

    $query->condition('hash', $hash);

    $results = $query->execute();

    $create = TRUE;

    if ($results) {
      foreach ($results as $result) {
        $id = $result->invoice_id;

        if ($id) {
          $invoice = $this->lightningCharge->invoice($id);

          if ($invoice) {
            $amount = $invoice->getAmount();
            $currency_code = $invoice->getCurrency();

            if ($amount == $price['number'] && $currency_code == $price['currency_code']) {
              $status = $invoice->getStatus();

              $output[$id] = $invoice;

              switch ($status) {
                case LightningChargeConstants::STATUS_UNPAID:
                case LightningChargeConstants::STATUS_PAID:
                  $create = FALSE;

                  break;
              }
            }
          }
        }
      }
    }

    if ($create) {
      $key = 'amount';

      if (empty($props[$key])) {
        unset($props[$key]);

        $key = 'currency';

        if (isset($props[$key])) {
          unset($props[$key]);
        }
      }

      $invoice = $this->createInvoice($props);
      $id = $invoice->getId();

      $query = $this->connection->insert(LightningChargeEntityConstants::TABLE_INVOICES);

      $fields = [];

      $fields['invoice_id'] = $id;
      $fields['hash'] = $hash;

      $query->fields($fields);

      $query->execute();

      $output[$id] = $invoice;
    }

    $this->session->set('lightning_charge_entity_invoices', TRUE);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntityInvoices(EntityInterface $entity = NULL) {
    $output = [];

    $entity_type = NULL;
    $bundle = NULL;
    $eid = NULL;

    if ($entity) {
      $entity_type = $entity->getEntityTypeId();
      $bundle = $entity->bundle();
      $eid = $entity->id();
    }

    $event = new LightningChargeEntityTypesEvent();

    $this->eventDispatcher->dispatch($event, LightningChargeEntityEvents::TYPES);

    $types = $event->get();

    $invoices = $this->lightningCharge->invoices();

    foreach ($invoices as $invoice) {
      $metadata = $invoice->getMetadata();

      if (isset($metadata[LightningChargeConstants::KEY_TYPE])) {
        $type = $metadata[LightningChargeConstants::KEY_TYPE]->getValue();

        if (!isset($types[$type])) {
          continue;
        }

        if ($entity_type && $bundle && $eid) {
          if ($metadata['entity_type']->getValue() != $entity_type) {
            continue;
          }

          if ($metadata['bundle']->getValue() != $bundle) {
            continue;
          }

          if ($metadata['entity']->getValue() != $eid) {
            continue;
          }
        }

        $id = $invoice->getId();

        $output[$id] = $invoice;
      }
    }

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function createInvoice($props = []) {
    $output = $this->lightningCharge->createInvoice($props);

    return $output;
  }

  /**
   * {@inheritDoc}
   */
  public function saveEntity($entity, $values) {
    if (isset($values['price_mode'])) {
      $values = [
        '' => $values,
      ];
    }

    foreach ($values as $k => $v) {
      $query = $this->connection->upsert(LightningChargeEntityConstants::TABLE);

      $data = [];

      $data['entity_type'] = $entity->getEntityTypeId();
      $data['entity_id'] = $entity->id();
      $data['view_mode'] = $k;
      $data['price_mode'] = $v['price_mode'];
      $data['price'] = $v['price']['number'];
      $data['currency_code'] = $v['price']['currency_code'];

      $fields = array_keys($data);

      $query->key('entity_id');
      $query->fields($fields);
      $query->values($data);

      $query->execute();
    }

    return $this;
  }

  /**
   * {@inheritDoc}
   */
  public function getEntity($entity) {
    $output = [];

    $a = 'b';

    $query = $this->connection->select(LightningChargeEntityConstants::TABLE, $a);

    $fields = [
      'view_mode',
      'price_mode',
      'price',
      'currency_code',
    ];

    $query->fields($a, $fields);

    $query->condition('entity_type', $entity->getEntityTypeId());
    $query->condition('entity_id', $entity->id());

    $results = $query->execute();

    foreach ($results as $result) {
      $output[] = [
        'view_mode' => $result->view_mode,
        'price_mode' => $result->price_mode,
        'price' => [
          'number' => $result->price,
          'currency_code' => $result->currency_code,
        ],
      ];
    }

    return $output;
  }

}
