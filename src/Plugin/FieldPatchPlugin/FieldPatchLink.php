<?php

namespace Drupal\patch_revision\Plugin\FieldPatchPlugin;

use Drupal\patch_revision\Annotation\FieldPatchPlugin;
use Drupal\Core\Annotation\Translation;

/**
 * Plugin implementation of the 'promote' actions.
 *
 * @FieldPatchPlugin(
 *   id = "link",
 *   label = @Translation("FieldPatchPlugin for all field types of link"),
 *   field_types = {
 *     "link",
 *   },
 *   properties = {
 *     "uri" = "",
 *     "title" = "",
 *   },
 *   permission = "administer nodes",
 * )
 */
class FieldPatchLink extends FieldPatchUndiffable {

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return 'link';
  }

  /**
   * {@inheritdoc}
   */
  public function validateDataIntegrity($value) {
    $properties = ['uri' => "", 'title' => ""];
    return count(array_intersect_key($properties, $value)) == count($properties);
  }

}