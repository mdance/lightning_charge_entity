<?php

namespace Drupal\lightning_charge_entity\EventSubscriber;

use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\lightning_charge\Events\LightningChargeAjaxResponseEvent;
use Drupal\lightning_charge\Events\LightningChargeEvents;
use Drupal\lightning_charge\Events\LightningChargeMetadataSchemasEvent;
use Drupal\lightning_charge\LightningChargeConstants;
use Drupal\lightning_charge_entity\Events\LightningChargeEntityEvents;
use Drupal\lightning_charge_entity\Events\LightningChargeEntityTypesEvent;
use Drupal\lightning_charge_entity\LightningChargeEntityConstants;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Provides the LightningChargeEntityEventSubscriber class.
 */
class LightningChargeEntityEventSubscriber implements EventSubscriberInterface {

  use StringTranslationTrait;

  /**
   * Provides the entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritDoc}
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager
  ) {
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    $output = [];

    $output[LightningChargeEvents::JS] = 'onJs';
    $output[LightningChargeEvents::METADATA_SCHEMA] = 'onMetadataSchema';
    $output[LightningChargeEntityEvents::TYPES] = 'onTypes';

    return $output;
  }

  /**
   * Provides the ajax response event handler.
   *
   * @param LightningChargeAjaxResponseEvent $event
   *   Provides the event object.
   */
  public function onJs(LightningChargeAjaxResponseEvent $event) {
    $invoice = $event->getInvoice();
    $metadata = $invoice->getMetadata();

    if (isset($metadata[LightningChargeConstants::KEY_TYPE])) {
      $type = $metadata[LightningChargeConstants::KEY_TYPE];
      $type = $type->getValue();

      if ($type == LightningChargeEntityConstants::TYPE) {
        $response = $event->getResponse();

        $entity_type = $metadata['entity_type']->getValue();
        $id = $metadata['entity']->getValue();
        $view_mode = $metadata['view_mode']->getValue();

        $storage = $this->entityTypeManager->getStorage($entity_type);
        $builder = $this->entityTypeManager->getViewBuilder($entity_type);

        $entity = $storage->load($id);

        if ($entity) {
          $result = $builder->view($entity, $view_mode);

          $hash = $metadata['hash']->getValue();
          $selector = "#" . LightningChargeEntityConstants::PREFIX . $hash;

          $command = new ReplaceCommand($selector, $result);

          $response->addCommand($command);
        }
      }
    }
  }

  /**
   * Provides the metadata schema event handler.
   *
   * @param \Drupal\lightning_charge\Events\LightningChargeMetadataSchemasEvent $event
   */
  public function onMetadataSchema(LightningChargeMetadataSchemasEvent $event) {
    $schema = [];

    $schema['entity_type'] = $this->t('Entity Type');
    $schema['bundle'] = $this->t('Bundle');
    $schema['entity'] = $this->t('Entity');
    $schema['view_mode'] = $this->t('View Mode');
    $schema['uid'] = $this->t('User');
    $schema['ip'] = $this->t('IP Address');
    $schema['session'] = $this->t('Session');
    $schema['hash'] = $this->t('Hash');

    $event->addSchema('lightning_charge_entity', $this->t('Lightning Charge Entities'), $schema);
  }

  /**
   * Provides the types event handler.
   *
   * @param \Drupal\lightning_charge_entity\Events\LightningChargeEntityTypesEvent $event
   */
  public function onTypes(LightningChargeEntityTypesEvent $event) {
    $event->add(LightningChargeEntityConstants::TYPE, $this->t('Lightning Charge Entity'));
  }

}
