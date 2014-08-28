<?php

/**
 * @file
 * Contains \Drupal\link\Plugin\field\formatter\LinkFormatter.
 */

namespace Drupal\jw_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Plugin implementation of the 'foo_formatter' formatter
 *
 * @FieldFormatter(
 *   id = "jwplayer_formatter",
 *   label = @Translation("Jw player"),
 *   field_types = {
 *     "file"
 *   },

 * )
 */
class JwplayerFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'jwplayer_preset' => 'none',
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $settings = $this->settings;

    $format_types = jw_player_preset_load();
    foreach ($format_types as $type => $type_info) {
      $options[$type] = $type_info->label();
    }
     $element['jwplayer_preset'] = array(
      '#title' => t('Select preset'),
      '#type' => 'select',
      '#default_value' => ($settings['jwplayer_preset']) ?  $settings['jwplayer_preset'] : FALSE,
      '#options' => $options,
    );
    $element['links'] = array(
      '#theme' => 'links',
      '#links' => array(
        array(
          'title' => t('Create new preset'),
          'href' => 'admin/config/media/jw_player/add',
        ),
        array(
          'title' => t('Manage presets'),
          'href' => 'admin/config/media/jw_player',
        ),
      ),
    );

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $settings = $this->settings;

    $summary = array();
    $presets = jw_player_preset_load();

    if (isset($presets[$settings['jwplayer_preset']])) {
      $summary[] = t('Preset: @name', array('@name' => $this->getSetting('jwplayer_preset')));
      $summary[] = t('Description: @description', array('@description' => $presets[$settings['jwplayer_preset']]->description));

      $settings = $presets[$settings['jwplayer_preset']]->settings;
      foreach ($settings as $key => $val) {
        // Filter out complex settings in the form of arrays (such as plugins).
        // @todo Tackle the display of enabled plugins separately.
        if (!is_array($val)) {
          $summary[] = t('@key: @val', array('@key' => $key, '@val' => !empty($val) ? $val : t('default')));
        }
      }
    }
    else {
      $summary[] = t('No preset selected');
    }

    return $summary;
  }
  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items) {
     $element = array();
     if ($this->getPluginId() == 'jwplayer_formatter' /*&& !empty($item->summary)*/) {
       // Process files for the theme function.
       foreach ($items as $delta => $item) {
         if ($item->entity) {
             $file_uri = $item->entity->getFileUri();
             $file_mime = $item->entity->getMimeType();
             $uri = file_create_url($file_uri);
           $element[$delta] = array(
             '#theme' => 'jw_player',
             '#file_url' =>  $uri,
             '#preset' => $this->settings['jwplayer_preset'],
             '#file_mime' => $file_mime,

           );
         }
       }
     }
     return $element;
    }


}
