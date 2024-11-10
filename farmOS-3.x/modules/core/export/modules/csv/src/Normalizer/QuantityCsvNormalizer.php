<?php

namespace Drupal\farm_export_csv\Normalizer;

use Drupal\farm_csv\Normalizer\ContentEntityNormalizer;
use Drupal\quantity\Entity\QuantityInterface;

/**
 * Normalizes quantity entities for CSV exports.
 */
class QuantityCsvNormalizer extends ContentEntityNormalizer {

  /**
   * {@inheritdoc}
   */
  public function normalize($entity, $format = NULL, array $context = []): array|string|int|float|bool|\ArrayObject|NULL {
    $data = parent::normalize($entity, $format, $context);

    // Query the log associated with the quantity.
    /** @var \Drupal\log\Entity\LogInterface[] $logs */
    $logs = $this->entityTypeManager->getStorage('log')->loadByProperties([
      'quantity' => $entity->id(),
    ]);

    // Prepend log data to the quantity data for normalization.
    if ($log = reset($logs)) {
      $data = [
        'log_id' => $log->id(),
        'log_status' => $log->get('status')->value,
        'log_timestamp' => $this->serializer->normalize($log->get('timestamp')->first(), $format, $context),
        'log_type' => $log->bundle(),
        'log_name' => $log->getName(),
      ] + $data;
    }
    return $data;
  }

  /**
   * {@inheritdoc}
   */
  public function supportsNormalization($data, ?string $format = NULL, array $context = []): bool {
    return $data instanceof QuantityInterface && $format == static::FORMAT;
  }

}
