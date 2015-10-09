<?php

/**
 * @file
 * Contains \Drupal\link\Plugin\field\formatter\LinkFormatter.
 */

namespace Drupal\jw_player\Plugin\Field\FieldFormatter;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\jw_player\Entity\Jw_player;

/**
 * Plugin implementation of the 'foo_formatter' formatter
 *
 * @FieldFormatter(
 *   id = "jwplayer_formatter",
 *   label = @Translation("Jw player"),
 *   field_types = {
 *     "file", "link"
 *   },
 * )
 */
class JwplayerFormatter extends FormatterBase {

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return array(
      'jwplayer_preset' => NULL,
    ) + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $presets = Jw_player::loadMultiple();
    $options = array();
    foreach ($presets as $type => $type_info) {
      $options[$type] = $type_info->label();
    }
    $element['jwplayer_preset'] = array(
      '#title' => t('Select preset'),
      '#type' => 'select',
      '#default_value' => $this->getSetting('jwplayer_preset'),
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
    $presets = Jw_player::loadMultiple();

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
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = array();
    // Process files for the theme function.
    foreach ($items as $delta => $item) {
      if ($item->entity) {
        $file_uri = $item->entity->getFileUri();
        $file_mime = $item->entity->getMimeType();
        $uri = file_create_url($file_uri);

        // Add cache tags for the referenced file and the preset if it can be
        // loaded, to prevent fatal errors.
        $tags = $item->entity->getCacheTags();
        if ($preset = Jw_player::load($this->getSetting('jwplayer_preset'))) {
          $tags = Cache::mergeTags($tags, $preset->getCacheTags());
        }

        $element[$delta] = array(
          'player' => array(
            '#theme' => 'jw_player',
            '#file_url' => $uri,
            '#file' => $item->entity,
            '#item' => $item,
            '#preset' => $this->getSetting('jwplayer_preset'),
            '#file_mime' => $file_mime,
            // Give each instance of the player a unique id. A random hash is
            // used in place of drupal_html_id() due to potentially conflicting
            // ids in cases where the entire output of the theme function is
            // cached. Prefix with jwplayer, as ID's that start with a number
            // are not valid.
            '#html_id' => 'jwplayer-' . md5(rand()),
          ),
          '#attached' => array(
            'library' => array('jw_player/jwplayer'),
          ),
          '#cache' => array(
            'tags' => $tags,
          ),
        );
      }
      elseif ($item->getFieldDefinition()->getType() == 'link') {
        $uri = $item->getUrl()->getUri();

        $element[$delta] = array(
          'player' => array(
            '#theme' => 'jw_player',
            '#file_url' => $uri,
            '#item' => $item,
            '#preset' => $this->getSetting('jwplayer_preset'),
            '#html_id' => 'jwplayer-' . md5(rand()),
          ),
          '#attached' => array(
            'library' => array('jw_player/jwplayer'),
          ),
        );
      }
    }

    return $element;
  }

}
