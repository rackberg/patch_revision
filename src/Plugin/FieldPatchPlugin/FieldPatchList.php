<?php

namespace Drupal\patch_revision\Plugin\FieldPatchPlugin;

use Drupal\patch_revision\Annotation\FieldPatchPlugin;
use Drupal\Core\Annotation\Translation;
use Drupal\patch_revision\Plugin\FieldPatchPluginBase;

/**
 * Plugin implementation of the 'promote' actions.
 *
 * @FieldPatchPlugin(
 *   id = "list",
 *   label = @Translation("FieldPatchPlugin for all field types of numbers"),
 *   field_types = {
 *     "list_string",
 *     "list_float",
 *     "list_integer",
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
class FieldPatchList extends FieldPatchPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return 'list';
  }

  /**
   * {@inheritdoc}
   */
  protected function setFeedbackClasses(&$field, $feedback) {
    $properties = array_keys($this->getFieldProperties());
    foreach ($feedback as $key => $col) {
      foreach ($properties as $property) {
        if(isset($col[$property]['applied'])) {
          if ($col[$property]['applied'] === FALSE) {
            $field['#attributes']['class'][] = "pr-apply-{$property}-failed";
          }
        }
      }
    }
  }

}