<?php

namespace Drupal\patch_revision\Plugin\FieldPatchPlugin;

use Drupal\Core\Datetime\DrupalDateTime;
use Drupal\patch_revision\Annotation\FieldPatchPlugin;
use Drupal\Core\Annotation\Translation;
use Drupal\patch_revision\Plugin\FieldPatchPluginBase;

/**
 * Plugin implementation of the 'promote' actions.
 *
 * @FieldPatchPlugin(
 *   id = "datetime",
 *   label = @Translation("FieldPatchPlugin for all field types of numbers"),
 *   field_types = {
 *     "datetime",
 *     "timestamp",
 *   },
 *   properties = {
 *     "value" = {
 *       "label" = @Translation("Value"),
 *       "default_value" = "",
 *       "patch_type" = "full",
 *     },
 *   },
 *   permission = "administer nodes",
 * )
 */
class FieldPatchDateTime extends FieldPatchPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return 'datetime';
  }


  public function getFormattedValue($value) {
    // $value = "1519732604";
    if (!preg_match('/^[0-9]*$/', $value)) {
      $timezone = new \DateTimeZone('UTC');
      $date_time = new DrupalDateTime($value, $timezone);
      $value = $date_time->format('U');
    }
    $object = $this->dateFormatter->format($value,'medium');
    return $object;
  }

  /**
   * {@inheritdoc}
   */
  public function prepareDataDb($data) {
    switch ($this->getFieldType()) {
      case 'timestamp':
        $format = 'U';
        break;
      default:
        $format = 'Y-m-d\TH:i:s';
    }

    foreach ($data as $key => $value) {
      foreach ($this->getFieldProperties() as $name => $default) {
        if ($value[$name] instanceof DrupalDateTime) {
          $data[$key][$name] = $value[$name]->format($format);
        } else {
          $data[$key][$name] = (string) $value[$name];
        }
      }
    }
    return $data;
  }

}