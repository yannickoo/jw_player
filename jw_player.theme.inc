<?php
// $Id$

/**
 * @file:
 */

/**
 * Returns HTML for an image field widget.
 *
 * @param $variables
 *   An associative array containing:
 *   - element: A render element representing the image field widget.
 *
 * @ingroup themeable
 */
function theme_jw_player_widget($variables) {
  $element = $variables['element'];
  $output = '';
  $output .= '<div class="jw-player-widget form-managed-file clearfix">';

  if (isset($element['preview'])) {
    $output .= '<div class="jw-player-preview">';
    $output .= drupal_render($element['preview']);
    $output .= '</div>';
  }

  $output .= '<div class="jw-player-widget-data">';
  if ($element['fid']['#value'] != 0) {
    $element['filename']['#markup'] .= ' <span class="file-size">(' . format_size($element['#file']->filesize) . ')</span> ';
  }
  $output .= drupal_render_children($element);
  $output .= '</div>';
  $output .= '</div>';

  return $output;
}

/**
 * Returns HTML for a JW Player field formatter.
 *
 * @param $variables
 *   An associative array containing:
 *   - item: An array of image data.
 *   - image_style: An optional image style.
 *
 * @ingroup themeable
 */
function theme_jw_player_formatter($variables) {
  $file = (object) $variables['item'];
  $preview_image = $file->preview_image;


  if ($variables['image_style']) {
    $image_path = image_style_path($variables['image_style'], $preview_image->uri);
    if (!file_exists($image_path)) {
      image_style_create_derivative(image_style_load($variables['image_style']), $preview_image->uri, $image_path);
    }
    $preview_image->uri = $image_path;
  }

  // Get the image dimensions. These will overwrite the dimensions of the player if they aren't set
  $info = image_get_info($preview_image->uri);
  $path = drupal_get_path('module', 'jw_player');

  $config = array(
    'id' => 'jw_player_' . $file->fid,
    'flashplayer' => file_create_url($path . '/mediaplayer/player.swf'),
    'width' => (isset($variables['width']) ? $variables['width'] : $info['width']),
    'height' => (isset($variables['height']) ? $variables['height'] : $info['height']),
    'file' => file_create_url($file->uri),
    'image' => file_create_url($preview_image->uri),
  );

  if ($variables['skin']) {
    $config['skin'] = file_create_url($variables['skin']);
  }

  // Allow other modules to alter the configuration of the player before it's rendered.
  drupal_alter('jw_player_config', $config, $file);

  return theme('jw_player', array('config' => $config));
}

/**
 * Theme a single JW Player.
 */
function theme_jw_player($variables) {
  $id_count = &drupal_static(__FUNCTION__);
  $config = $variables['config'];

  if (!isset($config['id'])) {
    $id_count = (isset($id_count) ? $id_count + 1 : 0);
    $config['id'] = 'jw_player_' . $id_count;
  }

  $output = '<div id="' . $config['id'] . '"></div>';

  $path = drupal_get_path('module', 'jw_player');

  drupal_add_js($path . '/mediaplayer/jwplayer.js');
  drupal_add_js($path . '/jw_player.js');
  drupal_add_js(array('jw_player' => array($config['id'] => $config)), 'setting');

  return $output;
}
