<?php

namespace Drupal\patch_revision\Plugin\FieldPatchPlugin;

use Drupal\patch_revision\Annotation\FieldPatchPlugin;
use Drupal\Core\Annotation\Translation;
use Drupal\patch_revision\Plugin\FieldPatchPluginBase;

/**
 * Plugin implementation of the 'promote' actions.
 *
 * @FieldPatchPlugin(
 *   id = "text_with_summary",
 *   label = @Translation("FieldPatchPlugin for field type Texts with summary"),
 *   field_types = {
 *     "text_with_summary",
 *   },
 *   properties = {
 *     "summary" = {
 *       "label" = @Translation("Summary"),
 *       "default_value" = "",
 *       "patch_type" = "diff",
 *     },
 *     "value" = {
 *       "label" = @Translation("Body"),
 *       "default_value" = "",
 *       "patch_type" = "diff",
 *     },
 *   },
 *   permission = "administer nodes",
 * )
 */
class FieldPatchTextSummary extends FieldPatchPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return 'text_with_summary';
  }

  /**
   * {@inheritdoc}
   */
  public function getDiffValue($str_src, $str_target) {
    return $this->diff->getTextDiff($str_src, $str_target);
  }

  /**
   * {@inheritdoc}
   */
  public function applyPatchValue($value, $patch) {
    return $this->diff->applyPatchText($value, $patch, $this->t('body'));
  }

  /**
   * {@inheritdoc}
   */
  public function patchFormatterValue($patch, $value_old) {
    return $this->diff->patchView($patch, $value_old);
  }


  /**
   * {@inheritdoc}
   */
  public function getDiffSummary($str_src, $str_target) {
    return $this->diff->getTextDiff($str_src, $str_target);
  }

  /**
   * {@inheritdoc}
   */
  public function applyPatchSummary($value, $patch) {
    return $this->diff->applyPatchText($value, $patch, $this->t('summary'));
  }

  /**
   * {@inheritdoc}
   */
  public function patchFormatterSummary($patch, $value_old) {
    return $this->diff->patchView($patch, $value_old);
  }

}