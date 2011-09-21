<?php
/**
 * @file:
 */

/**
 * Returns HTML for a JW Player field formatter.
 *
 * @param $variables
 *   An associative array containing:
 *   - item: An array of image data.
 *   - image_style: An optional image style.
 *   - preset : An array of JW Player formatter information.
 *
 * @ingroup themeable
 */
function theme_jw_player_formatter($variables) {
  $files = $variables['files'];
  if (empty($files)) {
    return '';
  }

  // get the first file to play
  $start_file = reset($files);

  // player setup
  $player_path = libraries_get_path('jwplayer');
  $config = array();

  // player config overrides
  if (!empty($variables['config'])) {
    $config = $variables['config'];
  }

  // player preset settings
  if (!empty($variables['preset'])) {
    $preset = jw_player_preset_load($variables['preset']);
    $settings = $preset['settings'];

    // resolve skin url
    $skin = !empty($settings['skin']) ? jw_player_skins($settings['skin']) : '';
    $config += array(
      'skin' => !empty($skin) ? file_create_url($skin->uri) : FALSE,
    );

    // merge in all other preset settings
    $config += $settings;
  }

  // merge default settings
  $config += jw_player_default_settings();

  // process each file
  $playlist = array();
  $preview_image_style = !empty($variables['image_style']) ? $variables['image_style'] : '';
  $start_file_isset = FALSE;
  $base_item_title = t('!playlist_base_title', array(
    '!playlist_base_title' => !empty($variables['playlist_base_title']) ? $variables['playlist_base_title'] : '',
  ));

  foreach ($files as $delta => $file) {
    $player_item = array(
      'file' => file_create_url($file->uri),
      'title' => !empty($file->description) ? $file->description : (!empty($base_item_title) ? $base_item_title . ' ' . ($delta + 1) : basename($file->filename)),
    );

    // image preview
    if (!empty($file->preview_image) && !empty($file->preview_image->uri)) {
      if ($preview_image_style) {
        $preview_image_path = image_style_path($preview_image_style, $file->preview_image->uri);
        if (!file_exists($preview_image_path)) {
          image_style_create_derivative(image_style_load($preview_image_style), $file->preview_image->uri, $preview_image_path);
        }
        $player_item['image'] = image_style_url($preview_image_style, $preview_image_path);
      }
      else {
        $player_item['image'] = file_create_url($file->preview_image->uri);
      }
    }

    // update main config
    if (!$start_file_isset) {
      $config['file'] = $player_item['file'];
      if (!empty($player_item['image'])) {
        $config['image'] = $player_item['image'];
      }
      $start_file_isset = TRUE;
    }

    // update playlist
    $playlist[] = $player_item;
  }


  // playlist handling
  if (count($playlist) > 1) {
    $config['playlist'] = $playlist;
  }
  else {
    unset($config['playlist'], $config['playlist.position'], $config['playlist.size']);
  }


  // Allow other modules to alter the configuration of the player before it's rendered.
  drupal_alter('jw_player_config', $config, $file);

  // render player
  return theme('jw_player', array('config' => $config));
}

/**
 * Theme a single JW Player.
 */
function theme_jw_player($variables) {
  $config = $variables['config'];
  $html_id = drupal_html_id('jw_player');
  $player_path = libraries_get_path('jwplayer');

  // add dependent resources
  drupal_add_js($player_path . '/jwplayer.js');
  drupal_add_js(drupal_get_path('module', 'jw_player') . '/jw_player.js');
  drupal_add_js(array('jw_player' => array($html_id => $config)), 'setting');

  return '<div id="' . $html_id . '"></div>';
}
